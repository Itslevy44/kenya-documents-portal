@extends('layouts.app')

@section('title', 'Complete Payment — {{ $template->name }}')

@section('content')

<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);padding:2.5rem 0;">
    <div class="container" style="text-align:center;">
        <h1 style="font-size:1.8rem;font-weight:800;color:#fff;margin-bottom:.35rem;">Complete Payment</h1>
        <p style="color:rgba(255,255,255,.75);font-size:.95rem;">{{ $template->name }} · Pay via M-Pesa</p>
    </div>
</div>

<section class="section" style="background:var(--bg);">
<div class="container">
<div class="payment-card">

    <div class="card" style="border-radius:20px;box-shadow:var(--shadow-lg);">
        <div class="card-body" style="padding:clamp(1.75rem,5vw,2.5rem);">

            {{-- M-Pesa branding --}}
            <div class="mpesa-logo">
                <span style="font-size:1.5rem;">📱</span>
                <span style="font-weight:800;color:#00A651;font-size:1.1rem;">M-Pesa</span>
                <span style="font-size:.8rem;color:var(--text-muted);font-weight:500;">Secure STK Push</span>
            </div>

            {{-- Amount display --}}
            <div class="amount-display" id="amount-display-wrap">
                <div class="price-big" id="display-amount">KSh {{ number_format($template->price, 0) }}</div>
                <div class="price-label">{{ $template->name }}</div>
            </div>

            {{-- Payment form --}}
            <form id="payment-form" novalidate
                  data-token="{{ $doc->session_token }}"
                  data-amount="{{ $template->price }}">
                @csrf

                <div class="form-group">
                    <label class="form-label" for="pay-phone">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:.3rem;"><path d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        M-Pesa Phone Number
                    </label>
                    <input type="tel" id="pay-phone" name="phone"
                           class="form-control"
                           placeholder="07XXXXXXXX"
                           maxlength="10"
                           inputmode="numeric"
                           autocomplete="tel"
                           required
                           style="font-size:1.05rem;"
                           @auth value="{{ auth()->user()->phone }}" @endauth>
                    <span class="form-hint">Enter the number that will receive the M-Pesa prompt.</span>
                </div>

                {{-- Promo code --}}
                <div class="form-group">
                    <label class="form-label" for="promo-code">Promo Code <span style="font-weight:400;color:var(--text-muted);">(optional)</span></label>
                    <div style="display:flex;gap:.5rem;">
                        <input type="text" id="promo-code" name="promo_code"
                               class="form-control"
                               placeholder="Enter code"
                               maxlength="50"
                               style="text-transform:uppercase;flex:1;">
                        <button type="button" id="btn-apply-promo" class="btn btn-ghost" style="white-space:nowrap;">Apply</button>
                    </div>
                    <span id="promo-msg" style="font-size:.82rem;display:block;margin-top:.3rem;"></span>
                </div>

                {{-- Discount row --}}
                <div id="discount-row" class="hidden" style="background:var(--bg);border-radius:10px;padding:1rem;margin-bottom:1.25rem;font-size:.9rem;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;">
                        <span style="color:var(--text-muted);">Original price</span>
                        <span>KSh <span id="original-amount">{{ number_format($template->price, 0) }}</span></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;margin-bottom:.3rem;color:var(--success);">
                        <span>Discount</span>
                        <span>− KSh <span id="discount-amount">0</span></span>
                    </div>
                    <div style="display:flex;justify-content:space-between;font-weight:800;font-size:1.05rem;border-top:1px solid var(--border);padding-top:.5rem;margin-top:.3rem;">
                        <span>Total</span>
                        <span style="color:var(--primary);">KSh <span id="final-amount">{{ number_format($template->price, 0) }}</span></span>
                    </div>
                </div>

                <button type="submit" class="btn btn-accent btn-block btn-xl" id="btn-pay" style="font-weight:800;letter-spacing:.01em;">
                    <span id="btn-pay-label" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                        <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        Pay via M-Pesa
                    </span>
                    <span id="btn-pay-spinner" class="hidden" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                        <span class="spinner" style="width:18px;height:18px;border-width:2px;"></span>
                        Sending prompt…
                    </span>
                </button>

                <p style="text-align:center;font-size:.78rem;color:var(--text-muted);margin-top:.75rem;">
                    🔒 Your payment is processed securely by Safaricom M-Pesa
                </p>
            </form>

            {{-- Waiting state --}}
            <div id="waiting-state" class="hidden" style="text-align:center;padding:1rem 0;">
                <div style="width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#E8F5E9,#A7F3D0);display:flex;align-items:center;justify-content:center;margin:0 auto 1.25rem;box-shadow:0 4px 16px rgba(34,197,94,.2);">
                    <span class="spinner spinner-dark" style="width:28px;height:28px;border-width:3px;"></span>
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.4rem;">Waiting for M-Pesa…</h3>
                <p style="color:var(--text-muted);font-size:.9rem;line-height:1.55;margin-bottom:.5rem;">
                    Check your phone for the M-Pesa PIN prompt and enter your PIN to complete payment.
                </p>
                <p style="font-size:.85rem;color:var(--text-muted);">
                    Time remaining: <strong id="countdown">120</strong>s
                </p>
                <button type="button" id="btn-cancel" class="btn btn-ghost btn-sm" style="margin-top:1rem;">
                    Cancel
                </button>
            </div>

            {{-- Success state --}}
            <div id="success-state" class="hidden" style="text-align:center;padding:1rem 0;">
                <div class="download-success-icon" style="width:64px;height:64px;">
                    <svg width="28" height="28" fill="none" stroke="#fff" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 style="font-size:1.1rem;font-weight:700;margin:1rem 0 .4rem;">Payment Confirmed!</h3>
                <p style="color:var(--text-muted);font-size:.9rem;">Redirecting to your downloads…</p>
            </div>

        </div>
    </div>

    {{-- Security note --}}
    <div style="display:flex;align-items:flex-start;gap:.75rem;margin-top:1.5rem;background:#fff;border:1px solid var(--border);border-radius:12px;padding:1rem 1.25rem;">
        <span style="font-size:1.2rem;flex-shrink:0;">ℹ️</span>
        <div style="font-size:.82rem;color:var(--text-muted);line-height:1.5;">
            You will receive a PDF and Word (.docx) download immediately after payment. Your documents are saved to your account for 24/7 access.
        </div>
    </div>

</div>
</div>
</section>

@endsection

@push('scripts')
<script src="/js/payment.js"></script>
@endpush
