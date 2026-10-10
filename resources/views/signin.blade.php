@extends('layouts.app')

@section('title', 'Sign In to Kenya Docs')
@section('meta-description', 'Sign in securely with your Kenyan phone number — passwordless SMS verification.')

@section('content')

<section style="min-height:calc(100vh - 68px);background:linear-gradient(160deg,#F0FDF4 0%,#F5F7F5 60%);display:flex;align-items:center;padding:3rem 0;">
    <div class="container">
        <div class="signin-card">

            {{-- Card --}}
            <div class="card" style="border-radius:20px;box-shadow:0 8px 40px rgba(0,0,0,.1);border:1px solid var(--border);">
                <div class="card-body" style="padding:clamp(2rem,5vw,2.75rem);">

                    {{-- Brand header --}}
                    <div style="text-align:center;margin-bottom:2rem;">
                        <img src="/assets/images/logo.jpg" alt="Kenya Docs"
                             width="72" height="72"
                             style="border-radius:16px;margin:0 auto 1rem;box-shadow:0 4px 16px rgba(0,0,0,.12);">
                        <h1 style="font-size:1.65rem;font-weight:800;color:var(--text);letter-spacing:-.02em;margin-bottom:.4rem;">
                            Welcome to Kenya<strong style="color:var(--primary);">Docs</strong>
                        </h1>
                        <p style="font-size:.9rem;color:var(--text-muted);margin:0;">
                            Sign in with your Kenyan phone number — no password needed.
                        </p>
                    </div>

                    {{-- STEP 1: Phone --}}
                    <div id="step-phone">
                        <form id="otp-request-form" novalidate>
                            @csrf

                            {{-- Phone field --}}
                            <div class="form-group" style="margin-bottom:1.5rem;">
                                <label class="form-label" for="phone">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    Kenyan Mobile Number
                                </label>
                                <div style="position:relative;">
                                    <span style="position:absolute;left:.85rem;top:50%;transform:translateY(-50%);font-size:.95rem;color:var(--text-muted);font-weight:600;pointer-events:none;">🇰🇪</span>
                                    <input type="tel" id="phone" name="phone"
                                           class="form-control"
                                           placeholder="07XXXXXXXX"
                                           maxlength="10"
                                           inputmode="numeric"
                                           autocomplete="tel"
                                           required
                                           style="padding-left:2.6rem;font-size:1.05rem;letter-spacing:.5px;">
                                </div>
                                <span class="form-hint">Safaricom, Airtel or Telkom — format: 07XXXXXXXX or 01XXXXXXXX</span>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-send-otp" style="font-weight:700;">
                                <span id="btn-send-otp-label" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                                    Send Verification Code
                                </span>
                                <span id="btn-send-otp-spinner" class="hidden" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                                    <span class="spinner" style="width:18px;height:18px;border-width:2px;"></span>
                                    Sending SMS…
                                </span>
                            </button>
                        </form>

                        {{-- Divider --}}
                        <div style="display:flex;align-items:center;gap:1rem;margin:1.5rem 0;">
                            <div style="flex:1;height:1px;background:var(--border);"></div>
                            <span style="font-size:.78rem;color:var(--text-muted);font-weight:600;white-space:nowrap;">HOW IT WORKS</span>
                            <div style="flex:1;height:1px;background:var(--border);"></div>
                        </div>

                        <div style="display:flex;flex-direction:column;gap:.75rem;">
                            @foreach(['Enter your mobile number and receive a 6-digit code by SMS.','Type the code to verify and sign in instantly.','Your documents, CVs and downloads are saved to your account.'] as $i => $step)
                            <div style="display:flex;align-items:flex-start;gap:.75rem;">
                                <span style="width:22px;height:22px;border-radius:50%;background:var(--primary);color:#fff;font-size:.72rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:.05rem;">{{ $i+1 }}</span>
                                <span style="font-size:.85rem;color:var(--text-muted);line-height:1.5;">{{ $step }}</span>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- STEP 2: OTP --}}
                    <div id="step-otp" class="hidden">

                        <div id="otp-sent-notice"
                             style="background:#F0FDF4;border:1.5px solid #86EFAC;color:#166534;padding:.875rem 1rem;border-radius:10px;margin-bottom:1.5rem;font-size:.9rem;font-weight:500;display:flex;align-items:center;gap:.6rem;">
                            <span style="font-size:1.1rem;">✅</span>
                            <span id="otp-notice-text"></span>
                        </div>

                        <form id="otp-verify-form" novalidate>
                            @csrf
                            <div class="form-group" style="margin-bottom:1.5rem;">
                                <label class="form-label" style="text-align:center;display:block;margin-bottom:1rem;">
                                    Enter the 6-digit code
                                </label>
                                {{-- Single input (styled as large box) --}}
                                <input type="text"
                                       id="otp"
                                       name="otp"
                                       class="form-control"
                                       placeholder="— — — — — —"
                                       maxlength="6"
                                       inputmode="numeric"
                                       autocomplete="one-time-code"
                                       required
                                       style="font-size:2rem;letter-spacing:.5rem;font-weight:800;text-align:center;padding:1rem;border-width:2px;border-radius:12px;">
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-verify-otp" style="font-weight:700;">
                                <span id="btn-verify-otp-label" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                    Verify &amp; Sign In
                                </span>
                                <span id="btn-verify-otp-spinner" class="hidden" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                                    <span class="spinner" style="width:18px;height:18px;border-width:2px;"></span>
                                    Verifying…
                                </span>
                            </button>

                            <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1.1rem;">
                                <button type="button" id="btn-resend" class="btn btn-link" style="font-size:.85rem;">
                                    Resend code <span id="resend-countdown" style="color:var(--text-muted);"></span>
                                </button>
                                <button type="button" id="btn-change-phone" class="btn btn-link" style="font-size:.85rem;">
                                    ← Change number
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

            {{-- Security note --}}
            <p style="text-align:center;font-size:.78rem;color:var(--text-muted);margin-top:1.25rem;line-height:1.5;">
                🔒 &nbsp;Encrypted session. Your data is never sold or shared.
            </p>

        </div>
    </div>
</section>

@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    const phoneInput      = document.getElementById('phone');
    const otpInput        = document.getElementById('otp');
    const stepPhone       = document.getElementById('step-phone');
    const stepOtp         = document.getElementById('step-otp');
    const btnSendOtp      = document.getElementById('btn-send-otp');
    const btnVerifyOtp    = document.getElementById('btn-verify-otp');
    const btnResend       = document.getElementById('btn-resend');
    const btnChangePhone  = document.getElementById('btn-change-phone');
    const otpNoticeText   = document.getElementById('otp-notice-text');
    const resendCountdown = document.getElementById('resend-countdown');

    let currentPhone = '';
    let resendTimer  = null;

    // ── Loading helpers ───────────────────────────────────────
    function setLoading(btn, labelId, spinnerId, loading) {
        btn.disabled = loading;
        const lbl = document.getElementById(labelId);
        const spn = document.getElementById(spinnerId);
        if (lbl) lbl.classList.toggle('hidden', loading);
        if (spn) spn.classList.toggle('hidden', !loading);
    }

    // ── Resend cooldown ───────────────────────────────────────
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

    // ── Send OTP ──────────────────────────────────────────────
    async function sendOtp(phone) {
        setLoading(btnSendOtp, 'btn-send-otp-label', 'btn-send-otp-spinner', true);
        try {
            const res = await api.post('/api/auth/send-otp', { phone });
            if (res.success) {
                currentPhone = phone;
                otpNoticeText.textContent = res.message || ('Code sent to ' + phone + '. Check your SMS.');
                stepPhone.classList.add('hidden');
                stepOtp.classList.remove('hidden');
                otpInput.value = '';
                otpInput.focus();
                startResendCooldown(60);
            } else {
                showToast(res.message || 'Failed to send code. Please try again.', 'error');
            }
        } catch (e) {
            showToast(e.message || 'Network error. Please try again.', 'error');
        } finally {
            setLoading(btnSendOtp, 'btn-send-otp-label', 'btn-send-otp-spinner', false);
        }
    }

    // ── OTP request form ──────────────────────────────────────
    document.getElementById('otp-request-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const phone = phoneInput.value.trim();
        if (!/^(07|01)\d{8}$/.test(phone)) {
            showToast('Enter a valid phone number: 07XXXXXXXX or 01XXXXXXXX', 'error');
            phoneInput.focus();
            return;
        }
        await sendOtp(phone);
    });

    // ── Auto-submit when all 6 digits entered ─────────────────
    otpInput.addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        if (this.value.length === 6) {
            document.getElementById('otp-verify-form').dispatchEvent(new Event('submit'));
        }
    });

    // ── Verify OTP ────────────────────────────────────────────
    document.getElementById('otp-verify-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        const otp = otpInput.value.trim();
        if (!/^\d{6}$/.test(otp)) {
            showToast('Please enter the 6-digit code from your SMS.', 'error');
            otpInput.focus();
            return;
        }
        setLoading(btnVerifyOtp, 'btn-verify-otp-label', 'btn-verify-otp-spinner', true);
        try {
            const res = await api.post('/api/auth/verify-otp', { phone: currentPhone, otp });
            if (res.success) {
                showToast('Signed in successfully! Redirecting…', 'success');
                setTimeout(function () {
                    window.location.href = res.redirect || '/';
                }, 600);
            } else {
                showToast(res.message || 'Invalid code. Please try again.', 'error');
                otpInput.value = '';
                otpInput.focus();
            }
        } catch (e) {
            showToast(e.message || 'Verification failed. Please try again.', 'error');
        } finally {
            setLoading(btnVerifyOtp, 'btn-verify-otp-label', 'btn-verify-otp-spinner', false);
        }
    });

    // ── Resend ────────────────────────────────────────────────
    btnResend.addEventListener('click', async function () {
        if (resendTimer) clearInterval(resendTimer);
        otpInput.value = '';
        await sendOtp(currentPhone);
    });

    // ── Change phone ──────────────────────────────────────────
    btnChangePhone.addEventListener('click', function () {
        stepOtp.classList.add('hidden');
        stepPhone.classList.remove('hidden');
        phoneInput.focus();
        if (resendTimer) clearInterval(resendTimer);
    });

}());
</script>
@endpush
