@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
{{-- FEAT-007: OTP phone authentication --}}
<section class="section">
    <div class="container">
        <div class="card" style="max-width:420px;margin:0 auto">
            <div class="card-body">
                <h1 class="section-title text-center">Sign In</h1>
                <p class="section-subtitle text-center">Enter your phone number to receive an OTP.</p>

                {{-- Step 1: Phone number --}}
                <div id="step-phone">
                    <form id="otp-request-form" novalidate>
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   placeholder="07XXXXXXXX" maxlength="10" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="btn-send-otp">
                            Send OTP
                        </button>
                    </form>
                </div>

                {{-- Step 2: OTP verification (hidden until OTP sent) --}}
                <div id="step-otp" class="hidden">
                    <form id="otp-verify-form" novalidate>
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="otp">Enter OTP</label>
                            <input type="text" id="otp" name="otp" class="form-control"
                                   placeholder="6-digit code" maxlength="6" inputmode="numeric" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="btn-verify-otp">
                            Verify &amp; Sign In
                        </button>
                        <button type="button" id="btn-resend" class="btn btn-link mt-2">Resend OTP</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
