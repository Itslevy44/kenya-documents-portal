@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')

<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);padding:2rem 0;">
    <div class="container">
        <h1 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;color:#fff;margin-bottom:.25rem;">Admin Dashboard</h1>
        <p style="color:rgba(255,255,255,.7);font-size:.9rem;">Manage documents, users, payments and settings.</p>
    </div>
</div>

<section class="section" style="background:var(--bg);">
<div class="container" id="admin-root">

    {{-- Stats --}}
    <div class="grid grid-4" style="gap:1rem;margin-bottom:2rem;" id="stats-cards">
        <div class="card stat-card"><div class="stat-value" id="stat-revenue">…</div><div class="stat-label">Revenue (KSh)</div></div>
        <div class="card stat-card"><div class="stat-value" id="stat-docs">…</div><div class="stat-label">Docs Paid</div></div>
        <div class="card stat-card"><div class="stat-value" id="stat-top-template" style="font-size:1.1rem;">…</div><div class="stat-label">Top Template</div></div>
        <div class="card stat-card"><div class="stat-value" id="stat-today">…</div><div class="stat-label">Today (KSh)</div></div>
    </div>

    {{-- Tabs --}}
    <div class="admin-tabs mb-3" role="tablist">
        <button class="tab-btn active" data-tab="payments"   role="tab">💳 Payments</button>
        <button class="tab-btn"        data-tab="templates"  role="tab">📄 Templates</button>
        <button class="tab-btn"        data-tab="categories" role="tab">📂 Categories</button>
        <button class="tab-btn"        data-tab="library"    role="tab">📚 Library</button>
        <button class="tab-btn"        data-tab="users"      role="tab">👥 Users</button>
        <button class="tab-btn"        data-tab="promos"     role="tab">🎫 Promos</button>
        <button class="tab-btn"        data-tab="missing"    role="tab">🔍 Missing Docs</button>
    </div>

    {{-- PAYMENTS TAB --}}
    <div id="tab-payments" class="tab-panel">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.75rem;margin-bottom:1rem;">
                    <h2 class="card-title" style="margin:0;">Recent Payments</h2>
                    <div style="display:flex;gap:.5rem;flex-wrap:wrap;">
                        <select id="filter-status" class="form-control" style="width:auto;font-size:.85rem;">
                            <option value="">All statuses</option>
                            <option value="paid">Paid</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                        </select>
                        <input type="date" id="filter-date-from" class="form-control" style="width:auto;font-size:.85rem;">
                        <input type="date" id="filter-date-to"   class="form-control" style="width:auto;font-size:.85rem;">
                        <button id="btn-filter-payments" class="btn btn-primary btn-sm">Filter</button>
                    </div>
                </div>
                <div id="payments-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

    {{-- TEMPLATES TAB --}}
    <div id="tab-templates" class="tab-panel hidden">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h2 class="card-title" style="margin:0;">Document Templates</h2>
                    <button id="btn-new-template" class="btn btn-primary btn-sm">+ New Template</button>
                </div>

                {{-- Template form (hidden) --}}
                <div id="template-form-wrap" class="hidden" style="background:var(--bg);border-radius:12px;padding:1.5rem;margin-bottom:1.25rem;border:1px solid var(--border);">
                    <h3 id="template-form-title" style="font-size:1rem;font-weight:700;margin-bottom:1rem;">New Template</h3>
                    <form id="template-form">
                        <input type="hidden" id="template-id" value="">
                        <div class="grid grid-2" style="gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Category *</label>
                                <select name="category_id" id="tf-category" class="form-control" required>
                                    <option value="">Select category…</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Template Name *</label>
                                <input type="text" name="name" id="tf-name" class="form-control" required placeholder="e.g. Affidavit of Loss">
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label class="form-label">Description *</label>
                                <textarea name="description" id="tf-description" class="form-control" rows="2" required></textarea>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Price (KSh) *</label>
                                <input type="number" name="price" id="tf-price" class="form-control" min="0" step="1" required placeholder="50">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="tf-sort" class="form-control" min="0" value="0">
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label class="form-label">Schema JSON * <span style="font-weight:400;font-size:.78rem;color:var(--text-muted);">(array of form fields)</span></label>
                                <textarea name="schema" id="tf-schema" class="form-control" rows="6" required placeholder='[{"name":"full_name","label":"Full Name","type":"text","required":true}]' style="font-family:monospace;font-size:.82rem;"></textarea>
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label class="form-label">Definition JSON * <span style="font-weight:400;font-size:.78rem;color:var(--text-muted);">(document template body)</span></label>
                                <textarea name="definition" id="tf-definition" class="form-control" rows="8" required placeholder='{"title":"AFFIDAVIT","sections":[{"type":"body","content":"I, {{full_name}}..."}]}' style="font-family:monospace;font-size:.82rem;"></textarea>
                            </div>
                        </div>
                        <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                            <button type="submit" class="btn btn-primary btn-sm" id="btn-template-save">Save Template</button>
                            <button type="button" class="btn btn-ghost btn-sm" id="btn-template-cancel">Cancel</button>
                        </div>
                    </form>
                </div>

                <div id="templates-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

    {{-- CATEGORIES TAB --}}
    <div id="tab-categories" class="tab-panel hidden">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h2 class="card-title" style="margin:0;">Categories</h2>
                    <button id="btn-new-category" class="btn btn-primary btn-sm">+ New Category</button>
                </div>
                <div id="category-form-wrap" class="hidden" style="background:var(--bg);border-radius:12px;padding:1.25rem;margin-bottom:1.25rem;border:1px solid var(--border);">
                    <form id="category-form">
                        <input type="hidden" id="cat-id" value="">
                        <div class="grid grid-2" style="gap:1rem;">
                            <div class="form-group">
                                <label class="form-label">Name *</label>
                                <input type="text" name="name" id="cat-name" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label class="form-label">Icon (emoji)</label>
                                <input type="text" name="icon" id="cat-icon" class="form-control" maxlength="4" placeholder="📄">
                            </div>
                            <div class="form-group" style="grid-column:1/-1;">
                                <label class="form-label">Description</label>
                                <input type="text" name="description" id="cat-desc" class="form-control">
                            </div>
                            <div class="form-group">
                                <label class="form-label">Sort Order</label>
                                <input type="number" name="sort_order" id="cat-sort" class="form-control" min="0" value="0">
                            </div>
                        </div>
                        <div style="display:flex;gap:.5rem;margin-top:.75rem;">
                            <button type="submit" class="btn btn-primary btn-sm" id="btn-cat-save">Save</button>
                            <button type="button" class="btn btn-ghost btn-sm" id="btn-cat-cancel">Cancel</button>
                        </div>
                    </form>
                </div>
                <div id="categories-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

    {{-- LIBRARY TAB --}}
    <div id="tab-library" class="tab-panel hidden">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h2 class="card-title" style="margin:0;">Document Library</h2>
                    <button id="btn-upload-library" class="btn btn-primary btn-sm">+ Upload File</button>
                </div>
                <form id="library-upload-form" class="hidden" style="background:var(--bg);padding:1.25rem;border-radius:12px;border:1px solid var(--border);margin-bottom:1.25rem;">
                    <div class="grid grid-2" style="gap:1rem;">
                        <div class="form-group"><label class="form-label">Title *</label><input type="text" name="title" class="form-control" required></div>
                        <div class="form-group"><label class="form-label">Category</label><input type="text" name="category" class="form-control" placeholder="e.g. letters, affidavits"></div>
                        <div class="form-group" style="grid-column:1/-1;"><label class="form-label">Description *</label><textarea name="description" class="form-control" rows="2" required></textarea></div>
                        <div class="form-group" style="grid-column:1/-1;"><label class="form-label">File (PDF, DOCX, ZIP, TXT — max 20MB) *</label><input type="file" name="file" class="form-control" accept=".pdf,.docx,.doc,.zip,.txt" required></div>
                    </div>
                    <label style="display:flex;align-items:center;gap:.5rem;font-size:.875rem;margin-bottom:.75rem;cursor:pointer;">
                        <input type="checkbox" name="is_free" value="1" checked> Free download
                    </label>
                    <div style="display:flex;gap:.5rem;">
                        <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                        <button type="button" id="btn-cancel-upload" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
                <div id="library-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

    {{-- USERS TAB --}}
    <div id="tab-users" class="tab-panel hidden">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h2 class="card-title" style="margin:0;">Users</h2>
                    <input type="search" id="user-search" class="form-control" style="width:220px;font-size:.875rem;" placeholder="Search phone or name…">
                </div>
                <div id="users-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

    {{-- PROMOS TAB --}}
    <div id="tab-promos" class="tab-panel hidden">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem;">
                    <h2 class="card-title" style="margin:0;">Promo Codes</h2>
                    <button id="btn-new-promo" class="btn btn-primary btn-sm">+ New Code</button>
                </div>
                <form id="promo-create-form" class="hidden" style="background:var(--bg);padding:1.25rem;border-radius:12px;border:1px solid var(--border);margin-bottom:1.25rem;">
                    <div style="display:flex;gap:1rem;flex-wrap:wrap;">
                        <div class="form-group" style="flex:1;min-width:140px;"><label class="form-label">Code (blank = auto)</label><input type="text" name="code" class="form-control" style="text-transform:uppercase;"></div>
                        <div class="form-group" style="min-width:110px;"><label class="form-label">Type *</label><select name="type" class="form-control" required><option value="percent">Percent %</option><option value="fixed">Fixed KSh</option></select></div>
                        <div class="form-group" style="min-width:100px;"><label class="form-label">Value *</label><input type="number" name="value" class="form-control" min="0" max="100" required></div>
                        <div class="form-group" style="min-width:100px;"><label class="form-label">Max Uses</label><input type="number" name="max_uses" class="form-control" min="1" placeholder="∞"></div>
                        <div class="form-group" style="min-width:160px;"><label class="form-label">Expires</label><input type="datetime-local" name="expires_at" class="form-control"></div>
                    </div>
                    <div style="display:flex;gap:.5rem;">
                        <button type="submit" class="btn btn-primary btn-sm">Create</button>
                        <button type="button" id="btn-cancel-promo" class="btn btn-ghost btn-sm">Cancel</button>
                    </div>
                </form>
                <div id="promos-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

    {{-- MISSING DOCS TAB --}}
    <div id="tab-missing" class="tab-panel hidden">
        <div class="card" style="border-radius:14px;">
            <div class="card-body">
                <h2 class="card-title" style="margin-bottom:1rem;">Missing Document Requests</h2>
                <div id="missing-table-wrap"><p style="color:var(--text-muted);">Loading…</p></div>
            </div>
        </div>
    </div>

</div>
</section>
@endsection

@push('scripts')
<script src="/js/admin.js"></script>
@endpush
