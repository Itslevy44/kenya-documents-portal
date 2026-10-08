<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MissingDocumentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Session-based auth with CSRF for all routes except webhooks.
| Webhook is excluded from CSRF via VerifyCsrfToken middleware.
|
*/

// ===== AUTH =====
Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// ===== DOCUMENT GENERATION =====
Route::post('/documents/{slug}/preview', [DocumentController::class, 'preview'])
    ->name('document.preview');
Route::post('/documents/{token}/generate', [DocumentController::class, 'generate'])
    ->name('document.generate');

// ===== PAYMENTS =====
Route::post('/payments/initiate', [PaymentController::class, 'initiate'])
    ->name('payment.initiate');
Route::get('/payments/{paymentId}/status', [PaymentController::class, 'status'])
    ->name('payment.status');

// Promo code validation
Route::post('/promo/validate', function (\Illuminate\Http\Request $request) {
    $request->validate([
        'code'   => ['required', 'string'],
        'amount' => ['required', 'numeric'],
    ]);

    $promo = \App\Models\PromoCode::where('code', strtoupper($request->code))
        ->where('is_active', true)
        ->first();

    if (!$promo) {
        return response()->json(['success' => false, 'message' => 'Invalid promo code.']);
    }

    if ($promo->expires_at && $promo->expires_at->isPast()) {
        return response()->json(['success' => false, 'message' => 'Promo code has expired.']);
    }

    if ($promo->max_uses && $promo->uses_count >= $promo->max_uses) {
        return response()->json(['success' => false, 'message' => 'Promo code limit reached.']);
    }

    $amount = (float) $request->amount;
    $discount = $promo->type === 'percent'
        ? round($amount * ($promo->value / 100), 2)
        : min($promo->value, $amount - 1);

    return response()->json([
        'success'  => true,
        'discount' => $discount,
        'message'  => 'Promo applied! Save KSh ' . number_format($discount, 2),
    ]);
});

// ===== MISSING DOCUMENTS =====
Route::post('/missing-documents', [MissingDocumentController::class, 'store'])
    ->name('missing-documents.store');

// ===== WEBHOOKS =====
// API routes don't use CSRF (web middleware group), so no exclusion needed
Route::post('/webhooks/payhero', [PaymentController::class, 'webhook'])
    ->name('payment.webhook');

// ===== ADMIN API (admin middleware) =====
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats', [AdminController::class, 'stats']);

    // Templates
    Route::get('/templates', [AdminController::class, 'templatesList']);
    Route::post('/templates', [AdminController::class, 'templateCreate']);
    Route::put('/templates/{id}', [AdminController::class, 'templateUpdate']);
    Route::post('/templates/{id}/toggle', [AdminController::class, 'templateToggle']);

    // Library
    Route::get('/library', [AdminController::class, 'libraryList']);
    Route::post('/library/upload', [AdminController::class, 'libraryUpload']);
    Route::put('/library/{id}', [AdminController::class, 'libraryUpdate']);
    Route::delete('/library/{id}', [AdminController::class, 'libraryDelete']);

    // Payments
    Route::get('/payments', [AdminController::class, 'paymentsList']);
    Route::post('/payments/{id}/unlock', [AdminController::class, 'paymentUnlock']);

    // Users
    Route::get('/users', [AdminController::class, 'usersList']);
    Route::delete('/users/{id}', [AdminController::class, 'userDelete']);

    // Promo codes
    Route::get('/promo-codes', [AdminController::class, 'promoList']);
    Route::post('/promo-codes', [AdminController::class, 'promoCreate']);
    Route::post('/promo-codes/{id}/toggle', [AdminController::class, 'promoToggle']);
    Route::delete('/promo-codes/{id}', [AdminController::class, 'promoDelete']);

    // Missing documents
    Route::get('/missing-documents', [AdminController::class, 'missingDocumentsList']);
});
