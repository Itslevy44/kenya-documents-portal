@extends('layouts.app')

@section('title', 'Payment')

@section('content')
{{-- FEAT-005: M-Pesa STK push payment --}}
<section class="section">
    <div class="container">
        <div class="card" style="max-width:480px;margin:0 auto">
            <div class="card-body">
                <h1 class="section-title">Complete Payment</h1>
                <p class="section-subtitle">Pay via M-Pesa to download your document.</p>

                <div id="order-summary" class="alert alert-info mb-3">
                    {{-- Order summary populated by payment.js --}}
                </div>

                <form id="payment-form" novalidate>
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="phone">M-Pesa Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               placeholder="07XXXXXXXX" maxlength="10" required>
                    </div>
                    <button type="submit" class="btn btn-accent btn-block" id="btn-pay">
                        Pay KSh 50
                    </button>
                </form>

                <div id="payment-status" class="mt-3 hidden">
                    {{-- Status / spinner shown after STK push --}}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/payment.js"></script>
@endpush
