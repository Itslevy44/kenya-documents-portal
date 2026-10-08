@extends('layouts.app')

@section('title', 'Sign In')

@section('content')
<section class="section">
    <div class="container">
        <div class="card" style="max-width:440px;margin:3rem auto">
            <div class="card-body">
                <div style="text-align:center;margin-bottom:1.5rem">
                    <span style="font-size:2.5rem">🇰🇪</span>
                    <h1 class="section-title" style="margin-top:.5rem">Sign In</h1>
                    <p class="section-subtitle">Enter your Kenyan phone number to receive a one-time code.</p>
                </div>

                {{-- Step 1: Phone number --}}
                <div id="step-phone">
                    <form id="otp-request-form" novalidate>
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" class="form-control"
                                   placeholder="07XXXXXXXX" maxlength="10" inputmode="numeric"
                                   autocomplete="tel" required>
                            <span class="form-hint">Format: 07XXXXXXXX (Safaricom, Airtel, Telkom)</span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="btn-send-otp">
                            <span id="btn-send-otp-label">Send OTP</span>
                            <span id="btn-send-otp-spinner" class="hidden">Sending…</span>
                        </button>
                    </form>
                </div>

                {{-- Step 2: OTP verification (hidden until OTP sent) --}}
                <div id="step-otp" class="hidden">
                    <p id="otp-sent-notice" style="color:var(--success);margin-bottom:1rem;font-size:.9rem"></p>
                    <form id="otp-verify-form" novalidate>
                        @csrf
                        <div class="form-group">
                            <label class="form-label" for="otp">OTP Code</label>
                            <input type="text" id="otp" name="otp" class="form-control"
                                   placeholder="6-digit code" maxlength="6" inputmode="numeric"
                                   autocomplete="one-time-code" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block" id="btn-verify-otp">
                            <span id="btn-verify-otp-label">Verify &amp; Sign In</span>
                            <span id="btn-verify-otp-spinner" class="hidden">Verifying…</span>
                        </button>
                        <button type="button" id="btn-resend" class="btn btn-link" style="margin-top:.75rem;width:100%">
                            Resend OTP <span id="resend-countdown"></span>
                        </button>
                        <button type="button" id="btn-change-phone" class="btn btn-link" style="width:100%">
                            Change phone number
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    const phoneInput      = document.getElementById('phone');
    const otpInput        = document.getElementById('otp');
    const stepPhone       = document.getElementById('step-phone');
    const stepOtp         = document.getElementById('step-otp');
    const btnSendOtp      = document.getElementById('btn-send-otp');
    const btnVerifyOtp    = document.getElementById('btn-verify-otp');
    const btnResend       = document.getElementById('btn-resend');
    const btnChangePhone  = document.getElementById('btn-change-phone');
    const otpSentNotice   = document.getElementById('otp-sent-notice');
    const resendCountdown = document.getElementById('resend-countdown');

    let currentPhone  = '';
    let resendTimer   = null;

    // --- helpers ---
    function setLoading(btn, labelId, spinnerId, loading) {
        btn.disabled = loading;
        document.getElementById(labelId).classList.toggle('hidden', loading);
        document.getElementById(spinnerId).classList.toggle('hidden', !loading);
    }

    function startResendCooldown(seconds) {
        btnResend.disabled = true;
        let remaining = seconds;
        resendCountdown.textContent = '(' + remaining + 's)';
        resendTimer = setInterval(function () {
            remaining--;
            if (remaining <= 0) {
                clearInterval(resendTimer);
                btnResend.disabled = false;
                resendCountdown.textContent = '';
            } else {
                resendCountdown.textContent = '(' + remaining + 's)';
            }
        }, 1000);
    }

    async function sendOtp(phone) {
        setLoading(btnSendOtp, 'btn-send-otp-label', 'btn-send-otp-spinner', true);
        try {
            const res = await api.post('/api/auth/send-otp', { phone });
            if (res.success) {
                currentPhone = phone;
                otpSentNotice.textContent = 'OTP sent to ' + phone + '. Check your messages.';
                stepPhone.classList.add('hidden');
                stepOtp.classList.remove('hidden');
                otpInput.focus();
                startResendCooldown(60);
            } else {
                showToast(res.message || 'Failed to send OTP.', 'error');
            }
        } catch (e) {
            showToast(e.message || 'Failed to send OTP.', 'error');
        } finally {
            setLoading(btnSendOtp, 'btn-send-otp-label', 'btn-send-otp-spinner', false);
        }
    }

    // --- request OTP ---
    document.getElementById('otp-request-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const phone = phoneInput.value.trim();
        if (!/^07\d{8}$/.test(phone)) {
            showToast('Phone must be in format 07XXXXXXXX.', 'error');
            phoneInput.focus();
            return;
        }
        await sendOtp(phone);
    });

    // --- verify OTP ---
    document.getElementById('otp-verify-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const otp = otpInput.value.trim();
        if (!/^\d{6}$/.test(otp)) {
            showToast('Please enter the 6-digit OTP.', 'error');
            otpInput.focus();
            return;
        }
        setLoading(btnVerifyOtp, 'btn-verify-otp-label', 'btn-verify-otp-spinner', true);
        try {
            const res = await api.post('/api/auth/verify-otp', { phone: currentPhone, otp });
            if (res.success) {
                showToast('Signed in successfully!', 'success');
                setTimeout(function () {
                    window.location.href = res.redirect || '/';
                }, 600);
            } else {
                showToast(res.message || 'Invalid OTP.', 'error');
            }
        } catch (e) {
            showToast(e.message || 'Verification failed.', 'error');
        } finally {
            setLoading(btnVerifyOtp, 'btn-verify-otp-label', 'btn-verify-otp-spinner', false);
        }
    });

    // --- resend ---
    btnResend.addEventListener('click', async function () {
        if (resendTimer) clearInterval(resendTimer);
        otpInput.value = '';
        await sendOtp(currentPhone);
    });

    // --- change phone ---
    btnChangePhone.addEventListener('click', function () {
        stepOtp.classList.add('hidden');
        stepPhone.classList.remove('hidden');
        phoneInput.focus();
        if (resendTimer) clearInterval(resendTimer);
    });
}());
</script>
@endpush
