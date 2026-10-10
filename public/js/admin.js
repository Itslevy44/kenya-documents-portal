/**
 * admin.js — Admin Dashboard v2
 * Kenya Docs
 */
(function () {
    'use strict';

    if (!document.getElementById('admin-root')) return;

    // =========================================================================
    // Tab navigation
    // =========================================================================

    const tabs   = document.querySelectorAll('.tab-btn');
    const panels = document.querySelectorAll('.tab-panel');

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabs.forEach(function (b) { b.classList.toggle('active', b === btn); });
            panels.forEach(function (p) { p.classList.toggle('hidden', p.id !== 'tab-' + btn.dataset.tab); });
            loadTab(btn.dataset.tab);
        });
    });

    loadStats();
    loadPayments();

    function loadTab(name) {
        ({
            payments:   loadPayments,
            templates:  loadTemplates,
            categories: loadCategories,
            library:    loadLibrary,
            users:      loadUsers,
            promos:     loadPromos,
            missing:    loadMissing,
        }[name] || function () {})();
    }

    // =========================================================================
    // Stats
    // =========================================================================

    async function loadStats() {
        try {
            const res = await api.get('/api/admin/stats');
            if (res.success) {
                setText('stat-revenue', formatMoney(res.total_revenue));
                setText('stat-docs', (res.paid_doc_count || 0).toLocaleString());
                setText('stat-top-template', res.top_templates?.[0]?.name || '—');
                const today     = new Date().toISOString().split('T')[0];
                const todayData = (res.daily_revenue || []).find(function (d) { return d.date === today; });
                setText('stat-today', formatMoney(todayData ? todayData.revenue : 0));
            }
        } catch (e) { /* ignore */ }
    }

    // =========================================================================
    // Payments
    // =========================================================================

    async function loadPayments(filters) {
        const wrap = el('payments-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            const qs  = filters ? '?' + new URLSearchParams(filters).toString() : '';
            const res = await api.get('/api/admin/payments' + qs);
            if (res.success) wrap.innerHTML = buildPaymentsTable(res.data.data || []);
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed to load payments.</p>'; }
    }

    function buildPaymentsTable(rows) {
        if (!rows.length) return '<div class="empty-state"><p>No payments found.</p></div>';
        return '<div class="table-responsive">' +
            '<table class="data-table"><thead><tr>' +
            '<th>#</th><th>Template</th><th>Phone</th><th>Amount</th><th>Receipt</th><th>Status</th><th>Date</th><th>Actions</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (p) {
                const color = p.status === 'paid' ? 'var(--success)' : p.status === 'failed' ? 'var(--error)' : 'var(--accent)';
                const amt   = (parseFloat(p.amount) - parseFloat(p.discount_amount || 0));
                return '<tr>' +
                    '<td>' + p.id + '</td>' +
                    '<td>' + esc(p.generated_document?.template?.name || '—') + '</td>' +
                    '<td>' + esc(p.phone || '—') + '</td>' +
                    '<td>KSh ' + formatMoney(amt) + '</td>' +
                    '<td>' + esc(p.mpesa_receipt || '—') + '</td>' +
                    '<td><span style="color:' + color + ';font-weight:700;">' + esc(p.status) + '</span></td>' +
                    '<td style="white-space:nowrap">' + fmtDate(p.created_at) + '</td>' +
                    '<td>' + (p.status !== 'paid' ? '<button class="btn btn-link btn-sm" onclick="unlockPayment(' + p.id + ')">Unlock</button>' : '') + '</td>' +
                    '</tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    window.unlockPayment = async function (id) {
        if (!confirm('Mark this payment as paid and unlock the document?')) return;
        try {
            const res = await api.post('/api/admin/payments/' + id + '/unlock', {});
            if (res.success) { showToast('Payment unlocked.', 'success'); loadPayments(); }
        } catch (e) { showToast('Failed to unlock.', 'error'); }
    };

    el('btn-filter-payments')?.addEventListener('click', function () {
        loadPayments({
            status:    el('filter-status').value,
            date_from: el('filter-date-from').value,
            date_to:   el('filter-date-to').value,
        });
    });

    // =========================================================================
    // Templates  (full CRUD)
    // =========================================================================

    let categoriesCache = [];

    async function loadTemplates() {
        const wrap = el('templates-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            // Load categories for the form dropdown
            if (!categoriesCache.length) {
                const cr = await api.get('/api/admin/categories');
                if (cr.success) categoriesCache = cr.data || [];
                populateCategoryDropdown();
            }
            const res = await api.get('/api/admin/templates');
            if (res.success) wrap.innerHTML = buildTemplatesTable(res.data || []);
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed to load templates.</p>'; }
    }

    function populateCategoryDropdown() {
        const sel = el('tf-category');
        if (!sel) return;
        sel.innerHTML = '<option value="">Select category…</option>';
        categoriesCache.forEach(function (c) {
            sel.innerHTML += '<option value="' + c.id + '">' + esc(c.name) + '</option>';
        });
    }

    function buildTemplatesTable(rows) {
        if (!rows.length) return '<p style="color:var(--text-muted)">No templates yet.</p>';
        return '<div class="table-responsive"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>Category</th><th>Name</th><th>Price</th><th>Status</th><th>Actions</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (t) {
                return '<tr>' +
                    '<td>' + t.id + '</td>' +
                    '<td>' + esc(t.category?.name || '—') + '</td>' +
                    '<td><strong>' + esc(t.name) + '</strong></td>' +
                    '<td>KSh ' + formatMoney(t.price) + '</td>' +
                    '<td>' + (t.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>') + '</td>' +
                    '<td style="display:flex;gap:.35rem;flex-wrap:wrap;">' +
                    '<button class="btn btn-link btn-sm" onclick="editTemplate(' + t.id + ')">Edit</button>' +
                    '<button class="btn btn-link btn-sm" onclick="toggleTemplate(' + t.id + ')">' + (t.is_active ? 'Deactivate' : 'Activate') + '</button>' +
                    '<button class="btn btn-link btn-sm text-error" onclick="deleteTemplate(' + t.id + ')">Delete</button>' +
                    '</td></tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    // Show form for new template
    el('btn-new-template')?.addEventListener('click', function () {
        el('template-id').value = '';
        el('template-form').reset();
        el('template-form-title').textContent = 'New Template';
        el('btn-template-save').textContent = 'Save Template';
        populateCategoryDropdown();
        el('template-form-wrap').classList.remove('hidden');
        el('template-form-wrap').scrollIntoView({ behavior: 'smooth' });
    });

    el('btn-template-cancel')?.addEventListener('click', function () {
        el('template-form-wrap').classList.add('hidden');
    });

    window.editTemplate = async function (id) {
        try {
            const res = await api.get('/api/admin/templates');
            const t   = (res.data || []).find(function (x) { return x.id === id; });
            if (!t) return showToast('Template not found.', 'error');

            el('template-id').value  = t.id;
            el('tf-category').value  = t.category_id;
            el('tf-name').value      = t.name;
            el('tf-description').value = t.description;
            el('tf-price').value     = t.price;
            el('tf-sort').value      = t.sort_order || 0;
            el('tf-schema').value    = JSON.stringify(t.schema, null, 2);
            el('tf-definition').value = JSON.stringify(t.definition, null, 2);

            el('template-form-title').textContent = 'Edit Template: ' + t.name;
            el('btn-template-save').textContent   = 'Update Template';
            el('template-form-wrap').classList.remove('hidden');
            el('template-form-wrap').scrollIntoView({ behavior: 'smooth' });
        } catch (e) { showToast('Failed to load template.', 'error'); }
    };

    el('template-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const id  = el('template-id').value;
        let schema, definition;
        try { schema = JSON.parse(el('tf-schema').value); } catch (err) { return showToast('Schema JSON is invalid: ' + err.message, 'error'); }
        try { definition = JSON.parse(el('tf-definition').value); } catch (err) { return showToast('Definition JSON is invalid: ' + err.message, 'error'); }

        const data = {
            category_id: el('tf-category').value,
            name:        el('tf-name').value,
            description: el('tf-description').value,
            price:       el('tf-price').value,
            sort_order:  el('tf-sort').value,
            schema, definition,
        };

        try {
            const res = id
                ? await api.put('/api/admin/templates/' + id, data)
                : await api.post('/api/admin/templates', data);

            if (res.success) {
                showToast(id ? 'Template updated.' : 'Template created.', 'success');
                el('template-form-wrap').classList.add('hidden');
                el('template-form').reset();
                loadTemplates();
            } else {
                showToast(res.message || 'Save failed.', 'error');
            }
        } catch (err) { showToast(err.message || 'Save failed.', 'error'); }
    });

    window.toggleTemplate = async function (id) {
        try {
            const res = await api.post('/api/admin/templates/' + id + '/toggle', {});
            if (res.success) { showToast('Template updated.', 'success'); loadTemplates(); }
        } catch (e) { showToast('Failed.', 'error'); }
    };

    window.deleteTemplate = async function (id) {
        if (!confirm('Delete this template? This cannot be undone.')) return;
        try {
            const res = await api.delete('/api/admin/templates/' + id);
            if (res.success) { showToast('Template deleted.', 'success'); loadTemplates(); }
            else showToast(res.message || 'Cannot delete.', 'error');
        } catch (e) { showToast(e.message || 'Failed.', 'error'); }
    };

    // =========================================================================
    // Categories
    // =========================================================================

    async function loadCategories() {
        const wrap = el('categories-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            const res = await api.get('/api/admin/categories');
            if (res.success) {
                categoriesCache = res.data || [];
                wrap.innerHTML = buildCategoriesTable(res.data || []);
            }
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed to load categories.</p>'; }
    }

    function buildCategoriesTable(rows) {
        if (!rows.length) return '<p style="color:var(--text-muted)">No categories yet.</p>';
        return '<div class="table-responsive"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>Icon</th><th>Name</th><th>Status</th><th>Actions</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (c) {
                return '<tr>' +
                    '<td>' + c.id + '</td>' +
                    '<td style="font-size:1.3rem;">' + esc(c.icon || '') + '</td>' +
                    '<td><strong>' + esc(c.name) + '</strong></td>' +
                    '<td>' + (c.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>') + '</td>' +
                    '<td style="display:flex;gap:.35rem;">' +
                    '<button class="btn btn-link btn-sm" onclick="editCategory(' + c.id + ')">Edit</button>' +
                    '<button class="btn btn-link btn-sm text-error" onclick="deleteCategory(' + c.id + ')">Delete</button>' +
                    '</td></tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    el('btn-new-category')?.addEventListener('click', function () {
        el('cat-id').value = '';
        el('category-form').reset();
        el('category-form-wrap').classList.remove('hidden');
    });

    el('btn-cat-cancel')?.addEventListener('click', function () {
        el('category-form-wrap').classList.add('hidden');
    });

    window.editCategory = async function (id) {
        const c = categoriesCache.find(function (x) { return x.id === id; });
        if (!c) return;
        el('cat-id').value   = c.id;
        el('cat-name').value = c.name;
        el('cat-icon').value = c.icon || '';
        el('cat-desc').value = c.description || '';
        el('cat-sort').value = c.sort_order || 0;
        el('category-form-wrap').classList.remove('hidden');
    };

    el('category-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const id   = el('cat-id').value;
        const data = { name: el('cat-name').value, icon: el('cat-icon').value, description: el('cat-desc').value, sort_order: el('cat-sort').value };
        try {
            const res = id ? await api.put('/api/admin/categories/' + id, data) : await api.post('/api/admin/categories', data);
            if (res.success) { showToast('Category saved.', 'success'); el('category-form-wrap').classList.add('hidden'); loadCategories(); }
            else showToast(res.message || 'Failed.', 'error');
        } catch (e) { showToast(e.message || 'Failed.', 'error'); }
    });

    window.deleteCategory = async function (id) {
        if (!confirm('Delete this category?')) return;
        try {
            const res = await api.delete('/api/admin/categories/' + id);
            if (res.success) { showToast('Deleted.', 'success'); loadCategories(); }
            else showToast(res.message || 'Cannot delete (has templates?).', 'error');
        } catch (e) { showToast(e.message || 'Failed.', 'error'); }
    };

    // =========================================================================
    // Library
    // =========================================================================

    async function loadLibrary() {
        const wrap = el('library-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            const res = await api.get('/api/admin/library');
            if (res.success) wrap.innerHTML = buildLibraryTable(res.data.data || []);
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed to load library.</p>'; }
    }

    function buildLibraryTable(rows) {
        if (!rows.length) return '<p style="color:var(--text-muted)">No library items.</p>';
        return '<div class="table-responsive"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>Title</th><th>Category</th><th>Free</th><th>Active</th><th>Downloads</th><th>Actions</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (item) {
                return '<tr>' +
                    '<td>' + item.id + '</td>' +
                    '<td>' + esc(item.title) + '</td>' +
                    '<td>' + esc(item.category || '—') + '</td>' +
                    '<td>' + (item.is_free ? '✅' : '❌') + '</td>' +
                    '<td>' + (item.is_active ? '✅' : '❌') + '</td>' +
                    '<td>' + (item.download_count || 0) + '</td>' +
                    '<td><button class="btn btn-link btn-sm text-error" onclick="deleteLibraryItem(' + item.id + ')">Delete</button></td>' +
                    '</tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    window.deleteLibraryItem = async function (id) {
        if (!confirm('Delete this library item?')) return;
        try {
            const res = await api.delete('/api/admin/library/' + id);
            if (res.success) { showToast('Deleted.', 'success'); loadLibrary(); }
        } catch (e) { showToast('Failed.', 'error'); }
    };

    el('btn-upload-library')?.addEventListener('click', function () { el('library-upload-form').classList.remove('hidden'); });
    el('btn-cancel-upload')?.addEventListener('click', function () { el('library-upload-form').classList.add('hidden'); });

    el('library-upload-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const btn = this.querySelector('button[type=submit]');
        btn.disabled = true;
        btn.textContent = 'Uploading…';
        try {
            const res = await fetch('/api/admin/library/upload', {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: new FormData(this),
            }).then(function (r) { return r.json(); });

            if (res.success) {
                showToast('File uploaded!', 'success');
                this.reset();
                el('library-upload-form').classList.add('hidden');
                loadLibrary();
            } else {
                showToast(res.message || 'Upload failed.', 'error');
            }
        } catch (e) { showToast('Upload error.', 'error'); }
        finally { btn.disabled = false; btn.textContent = 'Upload'; }
    });

    // =========================================================================
    // Users
    // =========================================================================

    async function loadUsers(search) {
        const wrap = el('users-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            const qs  = search ? '?search=' + encodeURIComponent(search) : '';
            const res = await api.get('/api/admin/users' + qs);
            if (res.success) wrap.innerHTML = buildUsersTable(res.data.data || []);
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed to load users.</p>'; }
    }

    function buildUsersTable(rows) {
        if (!rows.length) return '<p style="color:var(--text-muted)">No users found.</p>';
        return '<div class="table-responsive"><table class="data-table"><thead><tr>' +
            '<th>ID</th><th>Phone</th><th>Name</th><th>Admin</th><th>Docs</th><th>Joined</th><th>Actions</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (u) {
                return '<tr>' +
                    '<td>' + u.id + '</td>' +
                    '<td>' + esc(u.phone) + '</td>' +
                    '<td>' + esc(u.name || '—') + '</td>' +
                    '<td>' + (u.is_admin ? '<span class="badge badge-warning">Admin</span>' : '—') + '</td>' +
                    '<td>' + (u.generated_documents_count || 0) + '</td>' +
                    '<td>' + fmtDate(u.created_at) + '</td>' +
                    '<td><button class="btn btn-link btn-sm text-error" onclick="deleteUser(' + u.id + ')">Delete</button></td>' +
                    '</tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    window.deleteUser = async function (id) {
        const reason = prompt('Reason for deletion (required):');
        if (!reason) return;
        try {
            const res = await api.delete('/api/admin/users/' + id, { reason });
            if (res.success) { showToast('User deleted.', 'success'); loadUsers(); }
        } catch (e) { showToast('Failed.', 'error'); }
    };

    let userSearchTimer;
    el('user-search')?.addEventListener('input', function () {
        clearTimeout(userSearchTimer);
        userSearchTimer = setTimeout(function () { loadUsers(el('user-search').value); }, 400);
    });

    // =========================================================================
    // Promo Codes
    // =========================================================================

    async function loadPromos() {
        const wrap = el('promos-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            const res = await api.get('/api/admin/promo-codes');
            if (res.success) wrap.innerHTML = buildPromosTable(res.data || []);
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed.</p>'; }
    }

    function buildPromosTable(rows) {
        if (!rows.length) return '<p style="color:var(--text-muted)">No promo codes.</p>';
        return '<div class="table-responsive"><table class="data-table"><thead><tr>' +
            '<th>Code</th><th>Type</th><th>Value</th><th>Uses</th><th>Expires</th><th>Active</th><th>Actions</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (p) {
                return '<tr>' +
                    '<td><strong>' + esc(p.code) + '</strong></td>' +
                    '<td>' + esc(p.type) + '</td>' +
                    '<td>' + (p.type === 'percent' ? p.value + '%' : 'KSh ' + p.value) + '</td>' +
                    '<td>' + p.uses_count + (p.max_uses ? ' / ' + p.max_uses : '') + '</td>' +
                    '<td>' + (p.expires_at ? fmtDate(p.expires_at) : '—') + '</td>' +
                    '<td>' + (p.is_active ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Off</span>') + '</td>' +
                    '<td style="display:flex;gap:.35rem;">' +
                    '<button class="btn btn-link btn-sm" onclick="togglePromo(' + p.id + ')">' + (p.is_active ? 'Disable' : 'Enable') + '</button>' +
                    '<button class="btn btn-link btn-sm text-error" onclick="deletePromo(' + p.id + ')">Delete</button>' +
                    '</td></tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    window.togglePromo = async function (id) {
        try { const res = await api.post('/api/admin/promo-codes/' + id + '/toggle', {}); if (res.success) loadPromos(); }
        catch (e) { showToast('Failed.', 'error'); }
    };

    window.deletePromo = async function (id) {
        if (!confirm('Delete this promo code?')) return;
        try { const res = await api.delete('/api/admin/promo-codes/' + id); if (res.success) { showToast('Deleted.', 'success'); loadPromos(); } }
        catch (e) { showToast('Failed.', 'error'); }
    };

    el('btn-new-promo')?.addEventListener('click', function () { el('promo-create-form').classList.remove('hidden'); });
    el('btn-cancel-promo')?.addEventListener('click', function () { el('promo-create-form').classList.add('hidden'); });

    el('promo-create-form')?.addEventListener('submit', async function (e) {
        e.preventDefault();
        const data = Object.fromEntries(new FormData(this).entries());
        try {
            const res = await api.post('/api/admin/promo-codes', data);
            if (res.success) { showToast('Promo created!', 'success'); this.reset(); el('promo-create-form').classList.add('hidden'); loadPromos(); }
            else showToast(res.message || 'Failed.', 'error');
        } catch (e) { showToast('Failed.', 'error'); }
    });

    // =========================================================================
    // Missing Documents
    // =========================================================================

    async function loadMissing() {
        const wrap = el('missing-table-wrap');
        if (!wrap) return;
        wrap.innerHTML = '<p style="color:var(--text-muted)">Loading…</p>';
        try {
            const res = await api.get('/api/admin/missing-documents');
            if (res.success) wrap.innerHTML = buildMissingTable(res.data.data || []);
        } catch (e) { wrap.innerHTML = '<p class="text-error">Failed.</p>'; }
    }

    function buildMissingTable(rows) {
        if (!rows.length) return '<p style="color:var(--text-muted)">No requests logged.</p>';
        return '<div class="table-responsive"><table class="data-table"><thead><tr>' +
            '<th>Search Query</th><th>Description</th><th>Count</th><th>Last Requested</th>' +
            '</tr></thead><tbody>' +
            rows.map(function (r) {
                return '<tr>' +
                    '<td>' + esc(r.search_query || '—') + '</td>' +
                    '<td>' + esc(r.document_description || '—') + '</td>' +
                    '<td><strong>' + r.count + '</strong></td>' +
                    '<td>' + fmtDate(r.last_at) + '</td>' +
                    '</tr>';
            }).join('') +
            '</tbody></table></div>';
    }

    // =========================================================================
    // Utilities
    // =========================================================================

    function el(id) { return document.getElementById(id); }
    function setText(id, v) { const e = el(id); if (e) e.textContent = v; }
    function formatMoney(v) { return Number(v || 0).toLocaleString('en-KE', { minimumFractionDigits: 0 }); }
    function fmtDate(s) { return s ? new Date(s).toLocaleDateString('en-KE', { day: '2-digit', month: 'short', year: 'numeric' }) : '—'; }
    function esc(s) {
        return String(s || '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    // api.put and api.patch/delete helpers (extend api.js)
    if (window.api && !window.api.put) {
        window.api.put = function (url, data) {
            return fetch(url, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify(data),
            }).then(function (r) { return r.json(); });
        };
    }

}());
