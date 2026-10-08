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
     * Send OTP to user's phone
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

        // Generate 6-digit OTP
        $otpCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);

        // Hash and store OTP
        OtpCode::create([
            'phone' => $phone,
            'code' => Hash::make($otpCode),
            'expires_at' => now()->addMinutes(10),
        ]);

        // Send SMS
        $message = "Your Kenya Docs verification code is: {$otpCode}. Valid for 10 minutes. Do not share this code.";
        $sent = $this->atService->sendSms($phone, $message);

        if (!$sent) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ], 500);
        }

        // Increment rate limiter
        RateLimiter::hit($key, 600); // 10 minutes

        return response()->json([
            'success' => true,
            'message' => 'OTP sent successfully to ' . $phone,
        ]);
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
