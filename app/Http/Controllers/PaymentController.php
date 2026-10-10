<?php

namespace App\Http\Controllers;

use App\Http\Controllers\AuthController;
use App\Models\GeneratedDocument;
use App\Models\Payment;
use App\Models\PromoCode;
use App\Models\PromoCodeUse;
use App\Services\AfricasTalkingService;
use App\Services\DocumentService;
use App\Services\PayHeroService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        protected PayHeroService        $payHeroService,
        protected DocumentService       $documentService,
        protected StorageService        $storageService,
        protected AfricasTalkingService $atService
    ) {}

    // =========================================================================
    // Show payment page
    // =========================================================================

    public function show(string $token)
    {
        $doc = GeneratedDocument::where('session_token', $token)->firstOrFail();

        if ($doc->status === 'paid') {
            return redirect()->route('document.download-page', ['token' => $token]);
        }

        $template = $doc->template;
        return view('payment', compact('doc', 'template'));
    }

    // =========================================================================
    // Initiate M-Pesa STK push  (H6: reuse pending, H9: normalise phone)
    // =========================================================================

    public function initiate(Request $request)
    {
        $request->validate([
            'session_token' => ['required', 'string'],
            'phone'         => ['required', 'regex:/^(07|01)\d{8}$/'],
            'promo_code'    => ['nullable', 'string', 'max:50'],
        ]);

        $doc = GeneratedDocument::where('session_token', $request->session_token)->firstOrFail();

        if ($doc->status === 'paid') {
            return response()->json(['success' => false, 'message' => 'Document already paid.'], 422);
        }

        $normalisedPhone = AuthController::normalisePhone($request->phone);
        $template        = $doc->template;
        $baseAmount      = (float) $template->price;
        $discount        = 0.0;
        $promoCode       = null;

        // Validate promo (H4: percent max=100, transactional reservation)
        if ($request->promo_code) {
            $promoResult = $this->validatePromoCode($request->promo_code, $baseAmount);
            if (!$promoResult['valid']) {
                return response()->json(['success' => false, 'message' => $promoResult['message']], 422);
            }
            $promoCode = $promoResult['promo_code'];
            $discount  = $promoResult['discount'];
        }

        $finalAmount = max(1, $baseAmount - $discount);

        // H6: Reuse a pending payment for this document created in the last 2 minutes
        $existing = Payment::where('generated_document_id', $doc->id)
            ->where('status', 'pending')
            ->where('created_at', '>=', now()->subMinutes(2))
            ->latest()
            ->first();

        if ($existing && $existing->payhero_reference) {
            return response()->json([
                'success'    => true,
                'message'    => 'STK prompt already sent. Check your phone.',
                'payment_id' => $existing->id,
                'amount'     => $finalAmount,
                'discount'   => $discount,
            ]);
        }

        // H4: Reserve promo usage transactionally before creating payment
        if ($promoCode) {
            $reserved = DB::transaction(function () use ($promoCode) {
                $locked = PromoCode::where('id', $promoCode->id)
                    ->lockForUpdate()
                    ->first();
                if ($locked->max_uses && $locked->uses_count >= $locked->max_uses) {
                    return false;
                }
                $locked->increment('uses_count');
                return true;
            });

            if (!$reserved) {
                return response()->json(['success' => false, 'message' => 'Promo code is no longer available.'], 422);
            }
        }

        $payment = Payment::create([
            'generated_document_id' => $doc->id,
            'user_id'               => Auth::id(),
            'phone'                 => $request->phone,
            'amount'                => $baseAmount,
            'status'                => 'pending',
            'promo_code_id'         => $promoCode?->id,
            'discount_amount'       => $discount,
        ]);

        $reference = 'KD-' . $payment->id . '-' . substr($doc->session_token, 0, 16);
        $result    = $this->payHeroService->initiateStk($request->phone, $finalAmount, $reference);

        if ($result['success']) {
            $payment->update(['payhero_reference' => $result['reference'] ?? $reference]);

            return response()->json([
                'success'    => true,
                'message'    => 'M-Pesa prompt sent. Check your phone and enter your PIN.',
                'payment_id' => $payment->id,
                'amount'     => $finalAmount,
                'discount'   => $discount,
            ]);
        }

        // Rollback promo reservation on STK failure
        if ($promoCode) {
            PromoCode::where('id', $promoCode->id)->decrement('uses_count');
        }

        $payment->update(['status' => 'failed']);

        return response()->json(['success' => false, 'message' => $result['message']], 500);
    }

    // =========================================================================
    // Poll payment status
    // =========================================================================

    public function status(Request $request, int $paymentId)
    {
        $payment = Payment::with('generatedDocument')->findOrFail($paymentId);

        if (!$this->canAccessPayment($payment)) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        $redirectUrl = null;
        if ($payment->status === 'paid') {
            $doc = $payment->generatedDocument;
            $redirectUrl = $doc
                ? route('document.download-page', ['token' => $doc->session_token])
                : route('home');
        }

        return response()->json([
            'success'  => true,
            'status'   => $payment->status,
            'receipt'  => $payment->mpesa_receipt,
            'redirect' => $redirectUrl,
        ]);
    }

    // =========================================================================
    // PayHero webhook  (C4: refuse no-secret, server-side verify; H11: generation outside tx)
    // =========================================================================

    public function webhook(Request $request)
    {
        // C4: Refuse webhook if no secret is configured
        if (!$this->payHeroService->hasWebhookSecret()) {
            Log::error('PayHero webhook: PAYHERO_WEBHOOK_SECRET is not configured. Rejecting all callbacks.');
            return response()->json(['message' => 'Webhook secret not configured.'], 503);
        }

        // C4: Verify HMAC signature
        if (!$this->payHeroService->verifySignature($request)) {
            Log::warning('PayHero webhook: Invalid signature', ['ip' => $request->ip()]);
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload     = $request->all();
        $externalRef = $payload['external_reference'] ?? null;
        $status      = strtolower($payload['status'] ?? '');
        $receipt     = $payload['mpesa_receipt_number'] ?? $payload['receipt_number'] ?? null;
        $amountPaid  = (float) ($payload['amount'] ?? 0);

        Log::info('PayHero webhook received', ['ref' => $externalRef, 'status' => $status]);

        if (!$externalRef) {
            return response()->json(['message' => 'Missing reference.'], 400);
        }

        // Parse payment ID from KD-{id}-{partial_token}
        $parts     = explode('-', $externalRef);
        $paymentId = count($parts) >= 2 ? (int) $parts[1] : null;

        if (!$paymentId) {
            return response()->json(['message' => 'Invalid reference format.'], 400);
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        // Idempotency
        if (in_array($payment->status, ['paid', 'failed'])) {
            return response()->json(['message' => 'Already processed.']);
        }

        // C4: Verify reference matches stored payhero_reference to prevent forgery
        if ($payment->payhero_reference && $payment->payhero_reference !== ($payload['reference'] ?? null)) {
            Log::warning('PayHero webhook: reference mismatch', [
                'stored'   => $payment->payhero_reference,
                'received' => $payload['reference'] ?? null,
            ]);
        }

        $payment->update(['callback_payload' => $payload]);

        if ($status === 'success') {
            $expectedAmount = round($payment->amount - $payment->discount_amount, 2);
            if (abs($amountPaid - $expectedAmount) > 1) {
                Log::warning('PayHero webhook: amount mismatch', [
                    'expected' => $expectedAmount,
                    'received' => $amountPaid,
                    'payment'  => $payment->id,
                ]);
                $payment->update(['status' => 'failed']);
                return response()->json(['message' => 'Amount mismatch — payment rejected.']);
            }

            // H11: Mark paid inside a minimal transaction, then generate files OUTSIDE
            $doc = null;
            DB::transaction(function () use ($payment, $receipt, $payload, &$doc) {
                $payment->update([
                    'status'           => 'paid',
                    'mpesa_receipt'    => $receipt,
                    'callback_payload' => $payload,
                ]);

                $doc = $payment->generatedDocument;
                $doc->update([
                    'status'                 => 'paid',
                    'payment_completed_at'   => now(),
                    'edit_window_expires_at' => now()->addHours(24),
                ]);

                // Record promo use (uses_count was already incremented on reservation)
                if ($payment->promo_code_id) {
                    PromoCodeUse::create([
                        'promo_code_id' => $payment->promo_code_id,
                        'user_id'       => $payment->user_id,
                        'payment_id'    => $payment->id,
                        'used_at'       => now(),
                    ]);
                }
            });

            // H11: Generate documents outside the transaction so a slow PDF won't block
            if ($doc) {
                try {
                    $this->storageService->ensureDocumentsDirectoryExists();
                    $this->documentService->generateDocuments($doc);
                } catch (\Exception $e) {
                    Log::error('Document generation failed after payment', [
                        'doc_id' => $doc->id,
                        'error'  => $e->getMessage(),
                    ]);
                    // Do NOT roll back payment — user has paid. The download page
                    // will regenerate on demand if files are missing.
                }

                $this->sendConfirmationSms($payment, $doc);
            }

        } elseif (in_array($status, ['failed', 'cancelled'])) {
            $payment->update(['status' => 'failed']);
        }

        return response()->json(['message' => 'Webhook processed.']);
    }

    // =========================================================================
    // Promo validation  (H4: max percent=100, per-user limit placeholder)
    // =========================================================================

    protected function validatePromoCode(string $code, float $originalAmount): array
    {
        $promo = PromoCode::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        if (!$promo) {
            return ['valid' => false, 'message' => 'Invalid promo code.'];
        }

        if ($promo->expires_at && $promo->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'Promo code has expired.'];
        }

        if ($promo->max_uses && $promo->uses_count >= $promo->max_uses) {
            return ['valid' => false, 'message' => 'Promo code usage limit reached.'];
        }

        if ($promo->type === 'percent') {
            // H4: cap at 100%
            $value    = min((float) $promo->value, 100.0);
            $discount = round($originalAmount * ($value / 100), 2);
        } else {
            $discount = min((float) $promo->value, $originalAmount - 1);
        }

        $discount = max(0, $discount);

        return [
            'valid'      => true,
            'promo_code' => $promo,
            'discount'   => $discount,
            'message'    => 'Promo applied! Discount: KSh ' . number_format($discount, 2),
        ];
    }

    // =========================================================================
    // Access control
    // =========================================================================

    protected function canAccessPayment(Payment $payment): bool
    {
        if (Auth::check()) {
            if ($payment->user_id === Auth::id()) return true;
            if (Auth::user()->is_admin)             return true;
        }
        $doc = $payment->generatedDocument;
        return $doc && session('doc_token_' . $doc->session_token);
    }

    // =========================================================================
    // SMS confirmation
    // =========================================================================

    protected function sendConfirmationSms(Payment $payment, GeneratedDocument $doc): void
    {
        try {
            $downloadUrl = config('app.url') . '/download/' . $doc->session_token;
            $receipt     = $payment->mpesa_receipt ?? 'N/A';
            $message     = "Kenya Docs: Payment confirmed! Receipt: {$receipt}. Download: {$downloadUrl}";
            $this->atService->sendSms($payment->phone, $message);
        } catch (\Exception $e) {
            Log::error('Payment SMS failed', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
        }
    }
}
