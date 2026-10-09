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
    protected AfricasTalkingService $atService;

    public function __construct(AfricasTalkingService $atService)
    {
        $this->atService = $atService;
    }

    /**
     * Send OTP to user's phone.
     *
     * The OTP is always persisted to the database first.
     * If the AfricasTalking SMS delivery fails (bad credentials, network error, etc.)
     * we still return HTTP 200 so the UI does not break — the user is shown a soft
     * advisory message.  When APP_DEBUG=true the plain-text OTP is included in the
     * JSON response as `debug_otp` to facilitate testing without a live AT account.
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^07\d{8}$/'],
        ]);

        $phone = $request->input('phone');

        // Rate limiting: 3 requests per 10 minutes per phone
        $key = 'otp-send:' . $phone;
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            throw ValidationException::withMessages([
                'phone' => ["Too many OTP requests. Please try again in " . ceil($seconds / 60) . " minutes."],
            ]);
        }

        try {
            // Generate 6-digit OTP
            $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

            // Always persist the OTP — even if SMS later fails the user can still verify
            OtpCode::create([
                'phone'      => $phone,
                'code'       => Hash::make($otpCode),
                'expires_at' => now()->addMinutes(10),
            ]);

            // Increment rate limiter before attempting SMS so it counts even on failure
            RateLimiter::hit($key, 600); // 10 minutes

            // Attempt SMS delivery
            $message = "Your Kenya Docs verification code is: {$otpCode}. Valid for 10 minutes. Do not share this code.";
            $sent    = $this->atService->sendSms($phone, $message);

            $response = ['success' => true];

            if ($sent) {
                $response['message'] = 'OTP sent successfully to ' . $phone;
            } else {
                // SMS failed — inform user but do not return 500
                \Illuminate\Support\Facades\Log::warning('OTP SMS delivery failed', ['phone' => $phone]);
                $response['message'] = 'OTP sent. If you do not receive an SMS, please check your number and try again.';
            }

            // Include plain OTP in response when APP_DEBUG is enabled (testing / staging only)
            if (config('app.debug')) {
                $response['debug_otp'] = $otpCode;
            }

            return response()->json($response);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('sendOtp unexpected error', [
                'phone' => $phone,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred. Please try again later.',
            ], 500);
        }
    }

    /**
     * Verify OTP and authenticate user
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'regex:/^07\d{8}$/'],
            'otp' => ['required', 'digits:6'],
        ]);

        $phone = $request->input('phone');
        $otp = $request->input('otp');

        // Find the most recent unused, unexpired OTP for this phone
        $otpRecord = OtpCode::where('phone', $phone)
            ->whereNull('used_at')
            ->where('expires_at', '>', now())
            ->latest('created_at')
            ->first();

        if (!$otpRecord) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid or expired OTP code.'],
            ]);
        }

        // Verify the hash
        if (!Hash::check($otp, $otpRecord->code)) {
            throw ValidationException::withMessages([
                'otp' => ['Invalid OTP code.'],
            ]);
        }

        // Mark OTP as used
        $otpRecord->update(['used_at' => now()]);

        // Find or create user
        $user = User::firstOrCreate(
            ['phone' => $phone],
            ['name' => 'User ' . substr($phone, -4)] // Default name using last 4 digits
        );

        // Auto-assign admin if matching configured ADMIN_PHONE
        $adminPhone = config('services.admin_phone');
        if ($adminPhone && $phone === $adminPhone && !$user->is_admin) {
            $user->is_admin = true;
            $user->save();
        }

        // Start session
        Auth::login($user, true);

        // Clear rate limiter for this phone
        RateLimiter::clear('otp-send:' . $phone);

        return response()->json([
            'success' => true,
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'phone' => $user->phone,
                'name' => $user->name,
                'is_admin' => $user->is_admin,
            ],
            'redirect' => session()->pull('url.intended', route('home')),
        ]);
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully',
            ]);
        }

        return redirect()->route('home')->with('success', 'You have been signed out.');
    }
}
