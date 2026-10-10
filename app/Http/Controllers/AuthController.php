<?php

namespace App\Http\Controllers;

use App\Models\OtpCode;
use App\Models\User;
use App\Services\AfricasTalkingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected AfricasTalkingService $atService) {}

    // =========================================================================
    // Send OTP
    // =========================================================================

    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^(07|01)\d{8}$/'],
        ]);

        $phone = $this->normalisePhone($request->input('phone'));

        // Per-phone rate limit: 3 sends per 10 minutes
        $phoneKey = 'otp-send:' . $phone;
        if (RateLimiter::tooManyAttempts($phoneKey, 3)) {
            $secs = RateLimiter::availableIn($phoneKey);
            throw ValidationException::withMessages([
                'phone' => ['Too many requests. Try again in ' . ceil($secs / 60) . ' minutes.'],
            ]);
        }

        // Per-IP rate limit: 10 sends per 10 minutes
        $ipKey = 'otp-send-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipKey, 10)) {
            throw ValidationException::withMessages([
                'phone' => ['Too many requests from your network. Try again later.'],
            ]);
        }

        try {
            $otpCode = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            OtpCode::create([
                'phone'      => $phone,
                'code'       => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(10),
            ]);

            RateLimiter::hit($phoneKey, 600);
            RateLimiter::hit($ipKey, 600);

            $message = "Your Kenya Docs verification code is: {$otpCode}. Valid for 10 minutes. Do not share this code.";
            $sent    = $this->atService->sendSms($phone, $message);

            if ($sent) {
                $msg = 'Verification code sent to ' . $request->input('phone') . '. Check your SMS.';
            } else {
                \Illuminate\Support\Facades\Log::warning('OTP SMS delivery failed', ['phone' => $phone]);
                $msg = 'Code sent. If you do not receive an SMS within 30 seconds, please try again.';
            }

            // ⚠ debug_otp is intentionally removed — never expose OTP in API responses.

            return response()->json(['success' => true, 'message' => $msg]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('sendOtp error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again.',
            ], 500);
        }
    }

    // =========================================================================
    // Verify OTP
    // =========================================================================

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^(07|01)\d{8}$/'],
            'otp'   => ['required', 'digits:6'],
        ]);

        $phone = $this->normalisePhone($request->input('phone'));
        $otp   = $request->input('otp');

        // Per-phone attempt limit: 5 attempts per OTP window
        $attemptKey = 'otp-verify:' . $phone;
        if (RateLimiter::tooManyAttempts($attemptKey, 5)) {
            // Invalidate all OTPs for this phone to prevent further brute-force
            OtpCode::where('phone', $phone)->whereNull('used_at')->update(['used_at' => now()]);
            throw ValidationException::withMessages([
                'otp' => ['Too many failed attempts. Request a new code.'],
            ]);
        }

        // Per-IP attempt limit: 20 per 10 minutes
        $ipAttemptKey = 'otp-verify-ip:' . $request->ip();
        if (RateLimiter::tooManyAttempts($ipAttemptKey, 20)) {
            throw ValidationException::withMessages([
                'otp' => ['Too many requests from your network. Try again later.'],
            ]);
        }

        $otpRecord = OtpCode::where('phone', $phone)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$otpRecord) {
            RateLimiter::hit($attemptKey, 600);
            RateLimiter::hit($ipAttemptKey, 600);
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired code. Request a new one.'],
            ]);
        }

        if (!Hash::check($otp, $otpRecord->code)) {
            RateLimiter::hit($attemptKey, 600);
            RateLimiter::hit($ipAttemptKey, 600);
            throw ValidationException::withMessages([
                'otp' => ['Incorrect code. ' . (5 - RateLimiter::attempts($attemptKey)) . ' attempts remaining.'],
            ]);
        }

        // Success — mark used and clear rate limiters
        $otpRecord->update(['used_at' => now()]);
        RateLimiter::clear($attemptKey);
        RateLimiter::clear('otp-send:' . $phone);

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'New User']
        );

        // Auto-promote to admin
        $adminPhone = config('services.admin_phone');
        if ($adminPhone && $phone === $this->normalisePhone($adminPhone) && !$user->is_admin) {
            $user->update(['is_admin' => true]);
        }

        // M1: Regenerate session to prevent session fixation
        $request->session()->regenerate(true);

        Auth::login($user, true);

        return response()->json([
            'success'  => true,
            'message'  => 'Signed in successfully.',
            'user'     => [
                'id'       => $user->id,
                'phone'    => $user->phone,
                'name'     => $user->name,
                'is_admin' => $user->is_admin,
            ],
            'redirect' => session()->pull('url.intended', route('home')),
        ]);
    }

    // =========================================================================
    // Logout
    // =========================================================================

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Signed out.']);
        }

        return redirect()->route('home');
    }

    // =========================================================================
    // Helpers
    // =========================================================================

    /**
     * Normalise any Kenyan phone format to 254XXXXXXXXX (E.164 without +).
     * Accepts: 07XXXXXXXX, 01XXXXXXXX, +254XXXXXXXXX, 254XXXXXXXXX
     */
    public static function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/\s+/', '', $phone);

        if (str_starts_with($phone, '+254')) {
            return '254' . substr($phone, 4);
        }
        if (str_starts_with($phone, '254') && strlen($phone) === 12) {
            return $phone;
        }
        if (str_starts_with($phone, '0') && strlen($phone) === 10) {
            return '254' . substr($phone, 1);
        }
        return $phone;
    }
}
