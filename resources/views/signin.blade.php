@extends('layouts.app')

@section('title', 'Sign In to Kenya Docs')
@section('meta-description', 'Sign in securely with your Kenyan phone number to access your saved documents, CVs, and templates.')

@section('content')
<section class="section py-4" style="min-height: 80vh; display: flex; align-items: center;">
    <div class="container">
        <div class="card shadow-lg" style="max-width: 460px; margin: 1.5rem auto; border-radius: 16px; border: 1px solid var(--border);">
            <div class="card-body" style="padding: clamp(1.75rem, 4vw, 2.5rem);">
                <!-- Brand Header -->
                <div style="text-align: center; margin-bottom: 2rem;">
                    <img src="/assets/images/logo.jpg" alt="Kenya Docs" width="68" height="68" style="border-radius: 14px; margin: 0 auto .75rem; box-shadow: 0 4px 14px rgba(0,0,0,.12);">
                    <h1 class="section-title" style="margin: 0; font-size: 1.6rem; font-weight: 800; color: var(--primary);">
                        Sign In to Kenya Docs
                    </h1>
                    <p class="section-subtitle" style="font-size: .93rem; color: var(--text-muted); margin-top: .4rem; margin-bottom: 0;">
                        Enter your Kenyan mobile number for passwordless SMS login.
                    </p>
                </div>

                {{-- Step 1: Phone number --}}
                <div id="step-phone">
                    <form id="otp-request-form" novalidate>
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label" for="phone">Kenyan Phone Number</label>
                            <div style="position: relative;">
                                <input type="tel" id="phone" name="phone" class="form-control"
                                       placeholder="07XXXXXXXX" maxlength="10" inputmode="numeric"
                                       autocomplete="tel" required
                                       style="padding-left: 1rem; font-size: 1.05rem; letter-spacing: 0.5px;">
                            </div>
                            <span class="form-hint" style="color: var(--text-muted); font-size: .83rem; margin-top: .35rem; display: block;">
                                Accepts Safaricom, Airtel, and Telkom (Format: 07XXXXXXXX or 01XXXXXXXX)
                            </span>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-send-otp" style="font-weight: 700;">
                            <span id="btn-send-otp-label">Send Verification Code</span>
                            <span id="btn-send-otp-spinner" class="hidden">Sending SMS…</span>
                        </button>
                    </form>
                </div>

                {{-- Step 2: OTP verification (hidden until OTP sent) --}}
                <div id="step-otp" class="hidden">
                    <div id="otp-sent-notice" style="background: #E8F5E9; border: 1px solid #C8E6C9; color: #1B5E20; padding: .75rem 1rem; border-radius: 8px; margin-bottom: 1.25rem; font-size: .9rem;"></div>
                    <form id="otp-verify-form" novalidate>
                        @csrf
                        <div class="form-group mb-3">
                            <label class="form-label" for="otp">Enter 6-Digit Code</label>
                            <input type="text" id="otp" name="otp" class="form-control text-center"
                                   placeholder="123456" maxlength="6" inputmode="numeric"
                                   autocomplete="one-time-code" required
                                   style="font-size: 1.5rem; letter-spacing: 6px; font-weight: 700; padding: .65rem;">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-verify-otp" style="font-weight: 700;">
                            <span id="btn-verify-otp-label">Verify &amp; Continue</span>
                            <span id="btn-verify-otp-spinner" class="hidden">Verifying…</span>
                        </button>
                        <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                            <button type="button" id="btn-resend" class="btn btn-link" style="font-size: .88rem;">
                                Resend Code <span id="resend-countdown"></span>
                            </button>
                            <button type="button" id="btn-change-phone" class="btn btn-link" style="font-size: .88rem;">
                                Change number
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer benefits note -->
                <div style="margin-top: 2rem; padding-top: 1.25rem; border-top: 1px solid var(--border); text-align: center;">
                    <p style="font-size: .82rem; color: var(--text-muted); margin: 0;">
                        🔒 Secure session. Your documents, CVs, and downloads are automatically tied to your account for 24/7 access.
                    </p>
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
                let noticeText = res.message || ('OTP sent to ' + phone + '. Check your messages.');
                if (res.debug_otp) {
                    noticeText += ' (Test code: ' + res.debug_otp + ')';
                    otpInput.value = res.debug_otp;
                }
                otpSentNotice.textContent = noticeText;
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

    // Request OTP
    document.getElementById('otp-request-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const phone = phoneInput.value.trim();
        if (!/^(07|01)\d{8}$/.test(phone)) {
            showToast('Phone must be in format 07XXXXXXXX or 01XXXXXXXX.', 'error');
            phoneInput.focus();
            return;
        }
        await sendOtp(phone);
    });

    // Auto submit on 6 digits
    otpInput.addEventListener('input', function () {
        if (this.value.trim().length === 6) {
            document.getElementById('otp-verify-form').dispatchEvent(new Event('submit'));
        }
    });

    // Verify OTP
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
                }, 500);
            } else {
                showToast(res.message || 'Invalid OTP.', 'error');
            }
        } catch (e) {
            showToast(e.message || 'Verification failed.', 'error');
        } finally {
            setLoading(btnVerifyOtp, 'btn-verify-otp-label', 'btn-verify-otp-spinner', false);
        }
    });

    btnResend.addEventListener('click', async function () {
        if (resendTimer) clearInterval(resendTimer);
        otpInput.value = '';
        await sendOtp(currentPhone);
    });

    btnChangePhone.addEventListener('click', function () {
        stepOtp.classList.add('hidden');
        stepPhone.classList.remove('hidden');
        phoneInput.focus();
        if (resendTimer) clearInterval(resendTimer);
    });
}());
</script>
@endpush
