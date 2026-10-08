<?php

namespace App\Http\Controllers;

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
        protected PayHeroService      $payHeroService,
        protected DocumentService     $documentService,
        protected StorageService      $storageService,
        protected AfricasTalkingService $atService
    ) {}

    /**
     * Show payment page
     */
    public function show(string $token)
    {
        $doc = GeneratedDocument::where('session_token', $token)->firstOrFail();

        if ($doc->status === 'paid') {
            return redirect()->route('document.download-page', ['token' => $token]);
        }

        $template = $doc->template;

        return view('payment', compact('doc', 'template'));
    }

    /**
     * Initiate M-Pesa STK push payment
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'session_token' => ['required', 'string'],
            'phone'         => ['required', 'regex:/^07\d{8}$/'],
            'promo_code'    => ['nullable', 'string', 'max:50'],
        ]);

        $doc = GeneratedDocument::where('session_token', $request->session_token)->firstOrFail();

        if ($doc->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Document already paid.',
            ], 422);
        }

        $template = $doc->template;
        $amount   = (float) $template->price;
        $discount = 0.0;
        $promoCode = null;

        // Validate promo code if provided
        if ($request->promo_code) {
            $promoResult = $this->validatePromoCode($request->promo_code, $amount);
            if (!$promoResult['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $promoResult['message'],
                ], 422);
            }
            $promoCode = $promoResult['promo_code'];
            $discount  = $promoResult['discount'];
            $amount    = max(1, $amount - $discount); // Minimum KSh 1
        }

        // Create Payment record
        $payment = Payment::create([
            'generated_document_id' => $doc->id,
            'user_id'               => Auth::id(),
            'phone'                 => $request->phone,
            'amount'                => $template->price,
            'status'                => 'pending',
            'promo_code_id'         => $promoCode?->id,
            'discount_amount'       => $discount,
        ]);

        // Initiate STK push
        $reference = 'KD-' . $payment->id . '-' . $doc->session_token;
        $result = $this->payHeroService->initiateStk($request->phone, $amount, $reference);

        if ($result['success']) {
            $payment->update([
                'payhero_reference' => $result['reference'],
            ]);

            return response()->json([
                'success'    => true,
                'message'    => $result['message'],
                'payment_id' => $payment->id,
                'amount'     => $amount,
                'discount'   => $discount,
            ]);
        }

        $payment->update(['status' => 'failed']);

        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 500);
    }

    /**
     * Poll payment status (called every 3s by frontend)
     */
    public function status(Request $request, int $paymentId)
    {
        $payment = Payment::findOrFail($paymentId);

        // Check ownership
        if (!$this->canAccessPayment($payment)) {
            return response()->json(['success' => false, 'message' => 'Access denied.'], 403);
        }

        return response()->json([
            'success' => true,
            'status'  => $payment->status,
            'receipt' => $payment->mpesa_receipt,
            'redirect' => $payment->status === 'paid'
                ? route('document.download-page', ['token' => $payment->generatedDocument->session_token])
                : null,
        ]);
    }

    /**
     * PayHero webhook handler
     */
    public function webhook(Request $request)
    {
        // Verify signature
        if (!$this->payHeroService->verifySignature($request)) {
            Log::warning('PayHero webhook: Invalid signature', [
                'ip' => $request->ip(),
            ]);
            return response()->json(['message' => 'Invalid signature.'], 401);
        }

        $payload = $request->all();

        Log::info('PayHero webhook received', ['payload' => $payload]);

        // Extract key fields
        $externalRef = $payload['external_reference'] ?? null;
        $status      = strtolower($payload['status'] ?? '');
        $receipt     = $payload['mpesa_receipt_number'] ?? $payload['receipt_number'] ?? null;
        $amountPaid  = (float) ($payload['amount'] ?? 0);

        if (!$externalRef) {
            return response()->json(['message' => 'Missing reference.'], 400);
        }

        // Parse payment ID from reference (format: KD-{id}-{token})
        $parts = explode('-', $externalRef);
        $paymentId = count($parts) >= 2 ? (int)$parts[1] : null;

        if (!$paymentId) {
            return response()->json(['message' => 'Invalid reference format.'], 400);
        }

        $payment = Payment::find($paymentId);
        if (!$payment) {
            return response()->json(['message' => 'Payment not found.'], 404);
        }

        // Idempotency: ignore if already processed
        if ($payment->status === 'paid' || $payment->status === 'failed') {
            return response()->json(['message' => 'Already processed.']);
        }

        // Store raw callback
        $payment->update(['callback_payload' => $payload]);

        DB::transaction(function () use ($payment, $status, $receipt, $amountPaid, $payload) {
            if ($status === 'success') {
                // Verify amount (allow minor rounding diff of KSh 1)
                $expectedAmount = $payment->amount - $payment->discount_amount;
                if (abs($amountPaid - $expectedAmount) > 1) {
                    Log::warning('PayHero webhook: Amount mismatch', [
                        'payment_id' => $payment->id,
                        'expected' => $expectedAmount,
                        'received' => $amountPaid,
                    ]);
                    $payment->update(['status' => 'failed']);
                    return;
                }

                // Update payment
                $payment->update([
                    'status'          => 'paid',
                    'mpesa_receipt'   => $receipt,
                    'callback_payload' => $payload,
                ]);

                // Update document
                $doc = $payment->generatedDocument;
                $doc->update([
                    'status'                  => 'paid',
                    'payment_completed_at'    => now(),
                    'edit_window_expires_at'  => now()->addHours(24),
                ]);

                // Record promo code use
                if ($payment->promo_code_id) {
                    PromoCodeUse::create([
                        'promo_code_id' => $payment->promo_code_id,
                        'user_id'       => $payment->user_id,
                        'payment_id'    => $payment->id,
                        'used_at'       => now(),
                    ]);

                    // Increment promo uses count
                    PromoCode::where('id', $payment->promo_code_id)->increment('uses_count');
                }

                // Generate documents
                try {
                    $this->storageService->ensureDocumentsDirectoryExists();
                    $this->documentService->generateDocuments($doc);
                } catch (\Exception $e) {
                    Log::error('Document generation failed after payment', [
                        'doc_id' => $doc->id,
                        'error' => $e->getMessage(),
                    ]);
                }

                // Send SMS confirmation
                $this->sendConfirmationSms($payment, $doc);

            } elseif (in_array($status, ['failed', 'cancelled'])) {
                $payment->update(['status' => 'failed']);
            }
        });

        return response()->json(['message' => 'Webhook processed.']);
    }

    /**
     * Validate a promo code
     */
    protected function validatePromoCode(string $code, float $originalAmount): array
    {
        $promo = PromoCode::where('code', strtoupper($code))
            ->where('is_active', true)
            ->first();

        if (!$promo) {
            return ['valid' => false, 'message' => 'Invalid promo code.'];
        }

        // Check expiry
        if ($promo->expires_at && $promo->expires_at->isPast()) {
            return ['valid' => false, 'message' => 'Promo code has expired.'];
        }

        // Check max uses
        if ($promo->max_uses && $promo->uses_count >= $promo->max_uses) {
            return ['valid' => false, 'message' => 'Promo code has reached its usage limit.'];
        }

        // Compute discount
        $discount = 0.0;
        if ($promo->type === 'percent') {
            $discount = round($originalAmount * ($promo->value / 100), 2);
        } else { // fixed
            $discount = min($promo->value, $originalAmount - 1); // keep at least KSh 1
        }

        return [
            'valid'      => true,
            'promo_code' => $promo,
            'discount'   => $discount,
            'message'    => 'Promo code applied! Discount: KSh ' . number_format($discount, 2),
        ];
    }

    /**
     * Check if the current user can access a payment
     */
    protected function canAccessPayment(Payment $payment): bool
    {
        if (Auth::check()) {
            if ($payment->user_id === Auth::id()) return true;
            if (Auth::user()->is_admin) return true;
        }

        // Check session token ownership
        $doc = $payment->generatedDocument;
        return $doc && session('doc_token_' . $doc->session_token);
    }

    /**
     * Send SMS confirmation after successful payment
     */
    protected function sendConfirmationSms(Payment $payment, GeneratedDocument $doc): void
    {
        try {
            $downloadUrl = config('app.url') . '/download/' . $doc->session_token;
            $receipt = $payment->mpesa_receipt ?? 'N/A';
            $message = "Kenya Docs: Payment confirmed! Receipt: {$receipt}. Download: {$downloadUrl}";

            $this->atService->sendSms($payment->phone, $message);
        } catch (\Exception $e) {
            Log::error('Failed to send payment confirmation SMS', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
