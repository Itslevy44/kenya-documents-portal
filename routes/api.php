<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| These routes are loaded by the RouteServiceProvider within the "api"
| middleware group. All routes below use session-based auth with CSRF
| (no JWT). Webhook endpoints are excluded from CSRF middleware.
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Placeholder API routes — controllers added in subsequent FEATs
// Route::post('/auth/send-otp', [AuthController::class, 'sendOtp']);
// Route::post('/auth/verify-otp', [AuthController::class, 'verifyOtp']);
// Route::post('/documents/build', [DocumentController::class, 'build']);
// Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
// Route::post('/webhooks/payhero', [PaymentController::class, 'webhook']);
