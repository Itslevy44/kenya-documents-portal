/**
 * admin.js — Admin dashboard AJAX interactions
 * Kenya Docs
 */
(function () {
    'use strict';

    if (!document.getElementById('stats-cards')) return;

    // =========================================================================
    // Tab navigation
    // =========================================================================

    const tabs = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const target = btn.dataset.tab;
            tabs.forEach(function (b) { b.classList.toggle('active', b === btn); });
            panels.forEach(function (p) {
                p.classList.toggle('hidden', p.id !== 'tab-' + target);
            });
            loadTab(target);
        });
    });

    // =========================================================================
    // Load initial stats and first tab
    // =========================================================================

    loadStats();
    loadPayments();

    function loadTab(name) {
        switch (name) {
            case 'payments':  loadPayments(); break;
            case 'templates': loadTemplates(); break;
            case 'library':   loadLibrary(); break;
            case 'users':     loadUsers(); break;
            case 'promos':    loadPromos(); break;
            case 'missing':   loadMissing(); break;
        }
    }

    // =========================================================================
    // Stats
    // =========================================================================

    async function loadStats() {
        try {
            const res = await api.get('/api/admin/stats');
            if (res.success) {
                setText('stat-revenue', formatMoney(res.total_revenue));
                setText('stat-docs', res.paid_doc_count.toLocaleString());
                const top = res.top_templates?.[0];
                setText('stat-top-template', top ? top.name : '—');

                // Today's revenue from daily data
                const today = new Date().toISOString().split('T')[0];
                const todayData = (res.daily_revenue || []).find(function (d) { return d.date === today; });
                setText('stat-today', formatMoney(todayData ? todayData.revenue : 0));
            }
        } catch (e) { /* ignore */ }
    }

    // =========================================================================
    // Payments
    // =========================================================================

    async function loadPayments(filters) {
        const wrap = document.getElementById('payments-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p>Loading…</p>';

        try {
            let qs = '';
            if (filters) {
                const p = new URLSearchParams(filters);
                qs = '?' + p.toString();
            }
            const res = await api.get('/api/admin/payments' + qs);
            if (res.success) {
                wrap.innerHTML = buildPaymentsTable(res.data.data || []);
            }
        } catch (e) {
            wrap.innerHTML = '<p class="text-error">Failed to load payments.</p>';
        }
    }

    function buildPaymentsTable(rows) {
        if (!rows.length) return '<p>No payments found.</p>';
        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>Template</th><th>Phone</th><th>Amount</th><th>Receipt</th><th>Status</th><th>Date</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        rows.forEach(function (p) {
            const statusColor = p.status === 'paid' ? 'var(--success)' : p.status === 'failed' ? 'var(--error)' : 'var(--accent)';
            html += '<tr>';
            html += '<td>' + p.id + '</td>';
            html += '<td>' + escHtml(p.generated_document?.template?.name || '—') + '</td>';
            html += '<td>' + escHtml(p.phone || '—') + '</td>';
            html += '<td>KSh ' + formatMoney(p.amount - p.discount_amount) + '</td>';
            html += '<td>' + escHtml(p.mpesa_receipt || '—') + '</td>';
            html += '<td><span style="color:' + statusColor + '">' + escHtml(p.status) + '</span></td>';
            html += '<td>' + formatDate(p.created_at) + '</td>';
            html += '<td>';
            if (p.status !== 'paid') {
                html += '<button class="btn btn-link btn-sm" onclick="unlockPayment(' + p.id + ')">Unlock</button>';
            }
            html += '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    window.unlockPayment = async function (id) {
        if (!confirm('Mark this payment as paid and unlock the document?')) return;
        try {
            const res = await api.post('/api/admin/payments/' + id + '/unlock', {});
            if (res.success) { showToast('Payment unlocked.', 'success'); loadPayments(); }
        } catch (e) { showToast('Failed to unlock.', 'error'); }
    };

    const btnFilterPayments = document.getElementById('btn-filter-payments');
    if (btnFilterPayments) {
        btnFilterPayments.addEventListener('click', function () {
            loadPayments({
                status:    document.getElementById('filter-status').value,
                date_from: document.getElementById('filter-date-from').value,
                date_to:   document.getElementById('filter-date-to').value,
            });
        });
    }

    // =========================================================================
    // Templates
    // =========================================================================

    async function loadTemplates() {
        const wrap = document.getElementById('templates-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p>Loading…</p>';

        try {
            const res = await api.get('/api/admin/templates');
            if (res.success) {
                wrap.innerHTML = buildTemplatesTable(res.data || []);
            }
        } catch (e) {
            wrap.innerHTML = '<p class="text-error">Failed to load templates.</p>';
        }
    }

    function buildTemplatesTable(rows) {
        if (!rows.length) return '<p>No templates found.</p>';
        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>Category</th><th>Name</th><th>Price</th><th>Status</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        rows.forEach(function (t) {
            html += '<tr>';
            html += '<td>' + t.id + '</td>';
            html += '<td>' + escHtml(t.category?.name || '—') + '</td>';
            html += '<td>' + escHtml(t.name) + '</td>';
            html += '<td>KSh ' + formatMoney(t.price) + '</td>';
            html += '<td>' + (t.is_active ? '✅ Active' : '⛔ Inactive') + '</td>';
            html += '<td><button class="btn btn-link btn-sm" onclick="toggleTemplate(' + t.id + ')">'
                + (t.is_active ? 'Deactivate' : 'Activate') + '</button></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    window.toggleTemplate = async function (id) {
        try {
            const res = await api.post('/api/admin/templates/' + id + '/toggle', {});
            if (res.success) { showToast('Template updated.', 'success'); loadTemplates(); }
        } catch (e) { showToast('Failed to update template.', 'error'); }
    };

    // =========================================================================
    // Library
    // =========================================================================

    async function loadLibrary() {
        const wrap = document.getElementById('library-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p>Loading…</p>';

        try {
            const res = await api.get('/api/admin/library');
            if (res.success) {
                wrap.innerHTML = buildLibraryTable(res.data.data || []);
            }
        } catch (e) {
            wrap.innerHTML = '<p class="text-error">Failed to load library.</p>';
        }
    }

    function buildLibraryTable(rows) {
        if (!rows.length) return '<p>No library items found.</p>';
        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>Title</th><th>Category</th><th>Free</th><th>Active</th><th>Downloads</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        rows.forEach(function (item) {
            html += '<tr>';
            html += '<td>' + item.id + '</td>';
            html += '<td>' + escHtml(item.title) + '</td>';
            html += '<td>' + escHtml(item.category || '—') + '</td>';
            html += '<td>' + (item.is_free ? '✅' : '❌') + '</td>';
            html += '<td>' + (item.is_active ? '✅' : '❌') + '</td>';
            html += '<td>' + (item.download_count || 0) + '</td>';
            html += '<td><button class="btn btn-link btn-sm btn-danger" onclick="deleteLibraryItem(' + item.id + ')">Delete</button></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    window.deleteLibraryItem = async function (id) {
        if (!confirm('Delete this library item?')) return;
        try {
            const res = await api.delete('/api/admin/library/' + id);
            if (res.success) { showToast('Item deleted.', 'success'); loadLibrary(); }
        } catch (e) { showToast('Failed to delete.', 'error'); }
    };

    // Library upload form
    const btnUploadLibrary = document.getElementById('btn-upload-library');
    const libraryUploadForm = document.getElementById('library-upload-form');
    const btnCancelUpload = document.getElementById('btn-cancel-upload');

    if (btnUploadLibrary) {
        btnUploadLibrary.addEventListener('click', function () {
            libraryUploadForm.classList.remove('hidden');
        });
    }
    if (btnCancelUpload) {
        btnCancelUpload.addEventListener('click', function () {
            libraryUploadForm.classList.add('hidden');
        });
    }
    if (libraryUploadForm) {
        libraryUploadForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const formData = new FormData(libraryUploadForm);
            try {
                const res = await fetch('/api/admin/library/upload', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: formData,
                }).then(function (r) { return r.json(); });

                if (res.success) {
                    showToast('File uploaded successfully!', 'success');
                    libraryUploadForm.reset();
                    libraryUploadForm.classList.add('hidden');
                    loadLibrary();
                } else {
                    showToast(res.message || 'Upload failed.', 'error');
                }
            } catch (e) {
                showToast('Upload error.', 'error');
            }
        });
    }

    // =========================================================================
    // Users
    // =========================================================================

    async function loadUsers(search) {
        const wrap = document.getElementById('users-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p>Loading…</p>';

        try {
            const qs = search ? '?search=' + encodeURIComponent(search) : '';
            const res = await api.get('/api/admin/users' + qs);
            if (res.success) {
                wrap.innerHTML = buildUsersTable(res.data.data || []);
            }
        } catch (e) {
            wrap.innerHTML = '<p class="text-error">Failed to load users.</p>';
        }
    }

    function buildUsersTable(rows) {
        if (!rows.length) return '<p>No users found.</p>';
        let html = '<table class="data-table"><thead><tr>';
        html += '<th>ID</th><th>Phone</th><th>Name</th><th>Admin</th><th>Docs</th><th>Joined</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        rows.forEach(function (u) {
            html += '<tr>';
            html += '<td>' + u.id + '</td>';
            html += '<td>' + escHtml(u.phone) + '</td>';
            html += '<td>' + escHtml(u.name || '—') + '</td>';
            html += '<td>' + (u.is_admin ? '✅' : '—') + '</td>';
            html += '<td>' + (u.generated_documents_count || 0) + '</td>';
            html += '<td>' + formatDate(u.created_at) + '</td>';
            html += '<td><button class="btn btn-link btn-sm btn-danger" onclick="deleteUser(' + u.id + ')">Delete</button></td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    window.deleteUser = async function (id) {
        const reason = prompt('Reason for deletion (required):');
        if (!reason) return;
        try {
            const res = await api.delete('/api/admin/users/' + id, { reason });
            if (res.success) { showToast('User deleted.', 'success'); loadUsers(); }
        } catch (e) { showToast('Failed to delete user.', 'error'); }
    };

    const userSearch = document.getElementById('user-search');
    if (userSearch) {
        let timeout;
        userSearch.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () { loadUsers(userSearch.value); }, 400);
        });
    }

    // =========================================================================
    // Promo Codes
    // =========================================================================

    async function loadPromos() {
        const wrap = document.getElementById('promos-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p>Loading…</p>';

        try {
            const res = await api.get('/api/admin/promo-codes');
            if (res.success) {
                wrap.innerHTML = buildPromosTable(res.data || []);
            }
        } catch (e) {
            wrap.innerHTML = '<p class="text-error">Failed to load promo codes.</p>';
        }
    }

    function buildPromosTable(rows) {
        if (!rows.length) return '<p>No promo codes.</p>';
        let html = '<table class="data-table"><thead><tr>';
        html += '<th>Code</th><th>Type</th><th>Value</th><th>Uses</th><th>Max Uses</th><th>Expires</th><th>Active</th><th>Actions</th>';
        html += '</tr></thead><tbody>';
        rows.forEach(function (p) {
            html += '<tr>';
            html += '<td><strong>' + escHtml(p.code) + '</strong></td>';
            html += '<td>' + escHtml(p.type) + '</td>';
            html += '<td>' + (p.type === 'percent' ? p.value + '%' : 'KSh ' + p.value) + '</td>';
            html += '<td>' + p.uses_count + '</td>';
            html += '<td>' + (p.max_uses || '∞') + '</td>';
            html += '<td>' + (p.expires_at ? formatDate(p.expires_at) : '—') + '</td>';
            html += '<td>' + (p.is_active ? '✅' : '❌') + '</td>';
            html += '<td>';
            html += '<button class="btn btn-link btn-sm" onclick="togglePromo(' + p.id + ')">' + (p.is_active ? 'Disable' : 'Enable') + '</button> ';
            html += '<button class="btn btn-link btn-sm btn-danger" onclick="deletePromo(' + p.id + ')">Delete</button>';
            html += '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    window.togglePromo = async function (id) {
        try {
            const res = await api.post('/api/admin/promo-codes/' + id + '/toggle', {});
            if (res.success) { loadPromos(); }
        } catch (e) { showToast('Failed.', 'error'); }
    };

    window.deletePromo = async function (id) {
        if (!confirm('Delete this promo code?')) return;
        try {
            const res = await api.delete('/api/admin/promo-codes/' + id);
            if (res.success) { showToast('Deleted.', 'success'); loadPromos(); }
        } catch (e) { showToast('Failed.', 'error'); }
    };

    const btnNewPromo = document.getElementById('btn-new-promo');
    const promoCreateForm = document.getElementById('promo-create-form');
    const btnCancelPromo = document.getElementById('btn-cancel-promo');

    if (btnNewPromo) {
        btnNewPromo.addEventListener('click', function () {
            promoCreateForm.classList.remove('hidden');
        });
    }
    if (btnCancelPromo) {
        btnCancelPromo.addEventListener('click', function () {
            promoCreateForm.classList.add('hidden');
        });
    }
    if (promoCreateForm) {
        promoCreateForm.addEventListener('submit', async function (e) {
            e.preventDefault();
            const data = Object.fromEntries(new FormData(promoCreateForm).entries());
            try {
                const res = await api.post('/api/admin/promo-codes', data);
                if (res.success) {
                    showToast('Promo code created!', 'success');
                    promoCreateForm.reset();
                    promoCreateForm.classList.add('hidden');
                    loadPromos();
                } else {
                    showToast(res.message || 'Failed.', 'error');
                }
            } catch (e) { showToast('Failed to create promo code.', 'error'); }
        });
    }

    // =========================================================================
    // Missing Documents
    // =========================================================================

    async function loadMissing() {
        const wrap = document.getElementById('missing-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p>Loading…</p>';

        try {
            const res = await api.get('/api/admin/missing-documents');
            if (res.success) {
                wrap.innerHTML = buildMissingTable(res.data.data || []);
            }
        } catch (e) {
            wrap.innerHTML = '<p class="text-error">Failed to load requests.</p>';
        }
    }

    function buildMissingTable(rows) {
        if (!rows.length) return '<p>No requests.</p>';
        let html = '<table class="data-table"><thead><tr>';
        html += '<th>Search Query</th><th>Description</th><th>Requests</th><th>Last Requested</th>';
        html += '</tr></thead><tbody>';
        rows.forEach(function (r) {
            html += '<tr>';
            html += '<td>' + escHtml(r.search_query || '—') + '</td>';
            html += '<td>' + escHtml(r.document_description || '—') + '</td>';
            html += '<td><strong>' + r.count + '</strong></td>';
            html += '<td>' + formatDate(r.last_at) + '</td>';
            html += '</tr>';
        });
        html += '</tbody></table>';
        return html;
    }

    // =========================================================================
    // Utilities
    // =========================================================================

    function setText(id, val) {
        const el = document.getElementById(id);
        if (el) el.textContent = val;
    }

    function formatMoney(val) {
        return Number(val || 0).toLocaleString('en-KE', { minimumFractionDigits: 0 });
    }

    function formatDate(str) {
        if (!str) return '—';
        return new Date(str).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric' });
    }

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;').replace(/</g, '&lt;')
            .replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

}());
