@extends('layouts.app')

@section('title', 'Complete Payment')

@section('content')
<section class="section">
    <div class="container">
        <div class="card" style="max-width:500px;margin:3rem auto">
            <div class="card-body">
                <h1 class="section-title" style="text-align:center">Complete Payment</h1>
                <p class="section-subtitle" style="text-align:center">
                    {{ $template->name }} via M-Pesa STK Push
                </p>

                <div class="price-display" style="text-align:center;margin:1rem 0">
                    <span class="price-amount" id="display-amount"
                          style="font-size:2rem;font-weight:700;color:var(--primary)">
                        KSh {{ number_format($template->price, 0) }}
                    </span>
                </div>

                <form id="payment-form" novalidate
                      data-token="{{ $doc->session_token }}"
                      data-amount="{{ $template->price }}">
                    @csrf

                    <!-- Phone Number -->
                    <div class="form-group">
                        <label class="form-label" for="pay-phone">M-Pesa Phone Number</label>
                        <input type="tel" id="pay-phone" name="phone" class="form-control"
                               placeholder="07XXXXXXXX" maxlength="10" inputmode="numeric"
                               autocomplete="tel" required
                               @auth value="{{ auth()->user()->phone }}" @endauth>
                        <span class="form-hint">Enter the number to receive the M-Pesa prompt.</span>
                    </div>

                    <!-- Promo Code -->
                    <div class="form-group">
                        <label class="form-label" for="promo-code">Promo Code (optional)</label>
                        <div style="display:flex;gap:.5rem">
                            <input type="text" id="promo-code" name="promo_code" class="form-control"
                                   placeholder="Enter code" maxlength="50" style="text-transform:uppercase">
                            <button type="button" id="btn-apply-promo" class="btn btn-outline">Apply</button>
                        </div>
                        <span id="promo-msg" style="font-size:.85rem"></span>
                    </div>

                    <!-- Discount / Total line -->
                    <div id="discount-row" class="hidden" style="border-top:1px solid var(--border);padding-top:.75rem;margin-bottom:1rem">
                        <div style="display:flex;justify-content:space-between">
                            <span>Original:</span>
                            <span>KSh <span id="original-amount">{{ number_format($template->price, 0) }}</span></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;color:var(--success)">
                            <span>Discount:</span>
                            <span>- KSh <span id="discount-amount">0</span></span>
                        </div>
                        <div style="display:flex;justify-content:space-between;font-weight:700;font-size:1.1rem;margin-top:.5rem">
                            <span>Total:</span>
                            <span>KSh <span id="final-amount">{{ number_format($template->price, 0) }}</span></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-accent btn-block btn-lg" id="btn-pay">
                        <span id="btn-pay-label">Pay via M-Pesa</span>
                        <span id="btn-pay-spinner" class="hidden">Sending prompt…</span>
                    </button>
                </form>

                <!-- Waiting state (hidden initially) -->
                <div id="waiting-state" class="hidden" style="text-align:center;margin-top:2rem">
                    <div class="spinner" style="margin:0 auto 1rem" aria-hidden="true"></div>
                    <p id="waiting-msg"><strong>Waiting for M-Pesa confirmation…</strong></p>
                    <p style="font-size:.9rem;color:var(--text-muted)">
                        Check your phone for the M-Pesa prompt.<br>
                        Time remaining: <strong id="countdown">120</strong>s
                    </p>
                    <button type="button" id="btn-cancel" class="btn btn-link" style="margin-top:1rem">Cancel</button>
                </div>

                <!-- Receipt info -->
                <div id="success-state" class="hidden" style="text-align:center;margin-top:1.5rem">
                    <p style="color:var(--success);font-size:1.1rem">✅ Payment confirmed!</p>
                    <p>Redirecting to your download…</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/payment.js"></script>
@endpush
