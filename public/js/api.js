/**
 * api.js — Shared fetch() wrapper for Kenya Docs
 *
 * Reads CSRF token from <meta name="csrf-token">.
 * All requests send Content-Type: application/json and X-CSRF-TOKEN.
 * On 4xx/5xx, shows a toast and rejects the promise.
 *
 * Usage:
 *   const data = await api.post('/api/auth/send-otp', { phone: '0712345678' });
 *   const list = await api.get('/api/documents');
 */

const api = (() => {
    'use strict';

    /** Show a toast notification (success | error | warning | info) */
    window.showToast = function (message, type = 'info', duration = 4000) {
        const toast = document.getElementById('toast');
        if (!toast) return;

        // Remove previous type classes
        toast.classList.remove('show', 'toast-success', 'toast-error', 'toast-warning', 'toast-info');

        toast.textContent = message;
        if (type !== 'info') toast.classList.add('toast-' + type);

        // Force reflow so transition fires
        void toast.offsetHeight;
        toast.classList.add('show');

        clearTimeout(toast._hideTimer);
        toast._hideTimer = setTimeout(() => toast.classList.remove('show'), duration);
    };

    /** Get the CSRF token from the meta tag */
    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    /**
     * Core fetch wrapper
     * @param {string} url
     * @param {RequestInit} options
     * @returns {Promise<any>} Parsed JSON body
     */
    async function request(url, options = {}) {
        const defaults = {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': getCsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        };

        const merged = {
            ...defaults,
            ...options,
            headers: {
                ...defaults.headers,
                ...(options.headers || {}),
            },
        };

        let response;
        try {
            response = await fetch(url, merged);
        } catch (networkError) {
            window.showToast('Network error — please check your connection.', 'error');
            throw networkError;
        }

        // Parse response body (may be JSON or plain text)
        let body;
        const contentType = response.headers.get('Content-Type') || '';
        if (contentType.includes('application/json')) {
            body = await response.json().catch(() => ({}));
        } else {
            body = await response.text().catch(() => '');
        }

        if (!response.ok) {
            // Extract a human-readable error message
            const message =
                (body && typeof body === 'object' && (body.message || body.error)) ||
                (typeof body === 'string' && body) ||
                `Request failed (${response.status})`;

            if (response.status === 401) {
                window.showToast('Session expired — please sign in again.', 'error');
                setTimeout(() => { window.location.href = '/signin'; }, 1500);
            } else if (response.status === 403) {
                window.showToast('Access denied.', 'error');
            } else if (response.status === 422 && body.errors) {
                // Validation errors — join all messages
                const msgs = Object.values(body.errors).flat().join(' ');
                window.showToast(msgs, 'warning');
            } else {
                window.showToast(message, 'error');
            }

            const err = new Error(message);
            err.status = response.status;
            err.body   = body;
            throw err;
        }

        return body;
    }

    return {
        get:    (url, options = {})       => request(url, { method: 'GET', ...options }),
        post:   (url, data, options = {}) => request(url, { method: 'POST',   body: JSON.stringify(data), ...options }),
        put:    (url, data, options = {}) => request(url, { method: 'PUT',    body: JSON.stringify(data), ...options }),
        patch:  (url, data, options = {}) => request(url, { method: 'PATCH',  body: JSON.stringify(data), ...options }),
        delete: (url, options = {})       => request(url, { method: 'DELETE', ...options }),
    };
})();
