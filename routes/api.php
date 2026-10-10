<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CvController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\MissingDocumentController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;

// ============================================================
// RATE LIMITERS  (H5)
// ============================================================

RateLimiter::for('otp', function (Request $req) {
    return [
        Limit::perMinutes(10, 3)->by('otp-phone:' . $req->input('phone')),
        Limit::perMinutes(10, 10)->by('otp-ip:' . $req->ip()),
    ];
});

RateLimiter::for('preview', function (Request $req) {
    // Max 10 preview PDFs per IP per hour (CPU/disk protection on shared hosting)
    return Limit::perHour(10)->by($req->ip())->response(function () {
        return response()->json([
            'success' => false,
            'message' => 'Too many preview requests. Please try again later.',
        ], 429);
    });
});

RateLimiter::for('payment', function (Request $req) {
    // Max 5 STK pushes per IP per 10 min (harassment/spam protection)
    return Limit::perMinutes(10, 5)->by($req->ip());
});

RateLimiter::for('cv', function (Request $req) {
    return Limit::perMinute(10)->by($req->ip());
});

// ============================================================
// AUTH
// ============================================================

Route::post('/auth/send-otp',   [AuthController::class, 'sendOtp']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/auth/logout',     [AuthController::class, 'logout']);

// ============================================================
// DOCUMENT GENERATION
// ============================================================

Route::post('/documents/{slug}/preview', [DocumentController::class, 'preview'])
    ->middleware('throttle:preview')
    ->name('document.preview');

Route::post('/documents/{token}/generate', [DocumentController::class, 'generate'])
    ->name('document.generate');

// ============================================================
// PAYMENTS
// ============================================================

Route::post('/payments/initiate', [PaymentController::class, 'initiate'])
    ->middleware('throttle:payment')
    ->name('payment.initiate');

Route::get('/payments/{paymentId}/status', [PaymentController::class, 'status'])
    ->name('payment.status');

// Promo code validation
Route::post('/promo/validate', function (Request $request) {
    $request->validate([
        'code'   => ['required', 'string', 'max:50'],
        'amount' => ['required', 'numeric', 'min:0'],
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

    if ($promo->type === 'percent') {
        $value    = min((float) $promo->value, 100.0); // H4: cap at 100%
        $discount = round($amount * ($value / 100), 2);
    } else {
        $discount = min((float) $promo->value, $amount - 1);
    }

    return response()->json([
        'success'  => true,
        'discount' => max(0, $discount),
        'message'  => 'Promo applied! Save KSh ' . number_format(max(0, $discount), 2),
    ]);
})->middleware('throttle:60,1');

// ============================================================
// MISSING DOCUMENTS
// ============================================================

Route::post('/missing-documents', [MissingDocumentController::class, 'store'])
    ->name('missing-documents.store');

// ============================================================
// CV ASSISTANT  (H5: throttled)
// ============================================================

Route::post('/cv/generate', [CvController::class, 'generateApi'])
    ->middleware('throttle:cv')
    ->name('cv.generate-api');

Route::post('/cv/analyze', [CvController::class, 'analyzeApi'])
    ->middleware('throttle:cv')
    ->name('cv.analyze-api');

// ============================================================
// WEBHOOKS  (no CSRF — excluded in bootstrap/app.php)
// ============================================================

Route::post('/webhooks/payhero', [PaymentController::class, 'webhook'])
    ->name('payment.webhook');

// ============================================================
// ADMIN API
// ============================================================

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/stats',  [AdminController::class, 'stats']);

    // Templates — full CRUD (task #13)
    Route::get('/templates',              [AdminController::class, 'templatesList']);
    Route::post('/templates',             [AdminController::class, 'templateCreate']);
    Route::put('/templates/{id}',         [AdminController::class, 'templateUpdate']);
    Route::delete('/templates/{id}',      [AdminController::class, 'templateDelete']);
    Route::post('/templates/{id}/toggle', [AdminController::class, 'templateToggle']);

    // Categories (for template create form)
    Route::get('/categories',             [AdminController::class, 'categoriesList']);
    Route::post('/categories',            [AdminController::class, 'categoryCreate']);
    Route::put('/categories/{id}',        [AdminController::class, 'categoryUpdate']);
    Route::delete('/categories/{id}',     [AdminController::class, 'categoryDelete']);

    // Library
    Route::get('/library',               [AdminController::class, 'libraryList']);
    Route::post('/library/upload',       [AdminController::class, 'libraryUpload']);
    Route::put('/library/{id}',          [AdminController::class, 'libraryUpdate']);
    Route::delete('/library/{id}',       [AdminController::class, 'libraryDelete']);

    // Payments
    Route::get('/payments',              [AdminController::class, 'paymentsList']);
    Route::post('/payments/{id}/unlock', [AdminController::class, 'paymentUnlock']);

    // Users
    Route::get('/users',                 [AdminController::class, 'usersList']);
    Route::delete('/users/{id}',         [AdminController::class, 'userDelete']);

    // Promo codes
    Route::get('/promo-codes',              [AdminController::class, 'promoList']);
    Route::post('/promo-codes',             [AdminController::class, 'promoCreate']);
    Route::post('/promo-codes/{id}/toggle', [AdminController::class, 'promoToggle']);
    Route::delete('/promo-codes/{id}',      [AdminController::class, 'promoDelete']);

    // Missing documents
    Route::get('/missing-documents', [AdminController::class, 'missingDocumentsList']);
});
