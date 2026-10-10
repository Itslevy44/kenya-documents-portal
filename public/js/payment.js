/**
 * payment.js — M-Pesa STK Push payment flow
 * Polls /api/payments/{id}/status every 3s for up to 2 minutes.
 * Kenya Docs
 */
(function () {
    'use strict';

    const form           = document.getElementById('payment-form');
    if (!form) return;

    const btnPay         = document.getElementById('btn-pay');
    const btnApplyPromo  = document.getElementById('btn-apply-promo');
    const btnCancel      = document.getElementById('btn-cancel');
    const waitingState   = document.getElementById('waiting-state');
    const successState   = document.getElementById('success-state');
    const countdownEl    = document.getElementById('countdown');
    const promoMsg       = document.getElementById('promo-msg');
    const discountRow    = document.getElementById('discount-row');
    const discountAmtEl  = document.getElementById('discount-amount');
    const finalAmtEl     = document.getElementById('final-amount');
    const displayAmount  = document.getElementById('display-amount');

    const sessionToken = form.dataset.token;
    const baseAmount   = parseFloat(form.dataset.amount);

    let paymentId      = null;
    let pollInterval   = null;
    let countdownTimer = null;
    let remaining      = 120;
    let discountAmount = 0;

    // -------------------------------------------------------------------------
    // Promo code
    // -------------------------------------------------------------------------

    if (btnApplyPromo) {
        btnApplyPromo.addEventListener('click', async function () {
            const code = document.getElementById('promo-code').value.trim().toUpperCase();
            if (!code) { showToast('Enter a promo code first.', 'error'); return; }

            btnApplyPromo.disabled = true;
            btnApplyPromo.textContent = 'Checking…';

            try {
                const res = await api.post('/api/promo/validate', {
                    code: code,
                    amount: baseAmount,
                });

                if (res.success) {
                    discountAmount = parseFloat(res.discount) || 0;
                    const finalAmount = Math.max(1, baseAmount - discountAmount);

                    discountAmtEl.textContent = discountAmount.toLocaleString('en-KE', {minimumFractionDigits:0});
                    finalAmtEl.textContent    = finalAmount.toLocaleString('en-KE', {minimumFractionDigits:0});
                    displayAmount.textContent = 'KSh ' + finalAmount.toLocaleString('en-KE', {minimumFractionDigits:0});
                    discountRow.classList.remove('hidden');

                    promoMsg.style.color = 'var(--success)';
                    promoMsg.textContent = res.message || 'Promo code applied!';
                } else {
                    promoMsg.style.color = 'var(--error)';
                    promoMsg.textContent = res.message || 'Invalid promo code.';
                    discountAmount = 0;
                    discountRow.classList.add('hidden');
                }
            } catch (e) {
                promoMsg.style.color = 'var(--error)';
                promoMsg.textContent = e.message || 'Could not validate promo code.';
            } finally {
                btnApplyPromo.disabled = false;
                btnApplyPromo.textContent = 'Apply';
            }
        });
    }

    // -------------------------------------------------------------------------
    // Payment form submit
    // -------------------------------------------------------------------------

    form.addEventListener('submit', async function (e) {
        e.preventDefault();

        const phone = document.getElementById('pay-phone').value.trim();
        // BUG-07: Accept 07XXXXXXXX and 01XXXXXXXX (Safaricom/Airtel modern numbers)
        if (!/^(07|01)\d{8}$/.test(phone)) {
            showToast('Enter a valid Kenyan phone number: 07XXXXXXXX or 01XXXXXXXX', 'error');
            return;
        }

        setFormLoading(true);

        try {
            const payload = {
                session_token: sessionToken,
                phone: phone,
                promo_code: document.getElementById('promo-code').value.trim() || null,
            };

            const res = await api.post('/api/payments/initiate', payload);

            if (res.success) {
                paymentId = res.payment_id;
                showWaiting();
                startPolling();
                startCountdown();
            } else {
                showToast(res.message || 'Payment initiation failed.', 'error');
                setFormLoading(false);
            }
        } catch (err) {
            showToast(err.message || 'Payment initiation failed. Please try again.', 'error');
            setFormLoading(false);
        }
    });

    // -------------------------------------------------------------------------
    // Polling
    // -------------------------------------------------------------------------

    function startPolling() {
        pollInterval = setInterval(async function () {
            try {
                const res = await api.get('/api/payments/' + paymentId + '/status');
                if (res.success) {
                    if (res.status === 'paid') {
                        stopAll();
                        showSuccess(res.redirect);
                    } else if (res.status === 'failed') {
                        stopAll();
                        showToast('Payment failed or was cancelled. Please try again.', 'error');
                        resetForm();
                    }
                }
            } catch (e) {
                // Network error — keep polling
            }
        }, 3000);
    }

    function startCountdown() {
        remaining = 120;
        if (countdownEl) countdownEl.textContent = remaining;
        countdownTimer = setInterval(function () {
            remaining--;
            if (countdownEl) countdownEl.textContent = remaining;
            if (remaining <= 0) {
                stopAll();
                showToast('Payment timed out. Please try again.', 'error');
                resetForm();
            }
        }, 1000);
    }

    function stopAll() {
        clearInterval(pollInterval);
        clearInterval(countdownTimer);
    }

    // -------------------------------------------------------------------------
    // Cancel
    // -------------------------------------------------------------------------

    if (btnCancel) {
        btnCancel.addEventListener('click', function () {
            stopAll();
            resetForm();
        });
    }

    // -------------------------------------------------------------------------
    // UI helpers
    // -------------------------------------------------------------------------

    function setFormLoading(loading) {
        btnPay.disabled = loading;
        const label   = document.getElementById('btn-pay-label');
        const spinner = document.getElementById('btn-pay-spinner');
        if (label)   label.classList.toggle('hidden', loading);
        if (spinner) spinner.classList.toggle('hidden', !loading);
    }

    function showWaiting() {
        form.classList.add('hidden');
        waitingState.classList.remove('hidden');
    }

    function showSuccess(redirectUrl) {
        waitingState.classList.add('hidden');
        successState.classList.remove('hidden');
        if (redirectUrl) {
            setTimeout(function () {
                window.location.href = redirectUrl;
            }, 1500);
        }
    }

    function resetForm() {
        form.classList.remove('hidden');
        waitingState.classList.add('hidden');
        successState.classList.add('hidden');
        setFormLoading(false);
    }

}());
