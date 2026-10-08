@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
<section class="section">
    <div class="container">
        <h1 class="section-title">Admin Dashboard</h1>

        <!-- Stats Cards -->
        <div class="grid grid-4 mb-4" id="stats-cards">
            <div class="card stat-card">
                <div class="card-body" style="text-align:center">
                    <div class="stat-value" id="stat-revenue">—</div>
                    <div class="stat-label">Total Revenue (KSh)</div>
                </div>
            </div>
            <div class="card stat-card">
                <div class="card-body" style="text-align:center">
                    <div class="stat-value" id="stat-docs">—</div>
                    <div class="stat-label">Documents Paid</div>
                </div>
            </div>
            <div class="card stat-card">
                <div class="card-body" style="text-align:center">
                    <div class="stat-value" id="stat-top-template">—</div>
                    <div class="stat-label">Top Template</div>
                </div>
            </div>
            <div class="card stat-card">
                <div class="card-body" style="text-align:center">
                    <div class="stat-value" id="stat-today">—</div>
                    <div class="stat-label">Today's Revenue (KSh)</div>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="admin-tabs mb-3" role="tablist">
            <button class="tab-btn active" data-tab="payments" role="tab">Payments</button>
            <button class="tab-btn" data-tab="templates" role="tab">Templates</button>
            <button class="tab-btn" data-tab="library" role="tab">Library</button>
            <button class="tab-btn" data-tab="users" role="tab">Users</button>
            <button class="tab-btn" data-tab="promos" role="tab">Promo Codes</button>
            <button class="tab-btn" data-tab="missing" role="tab">Missing Docs</button>
        </div>

        <!-- Payments Tab -->
        <div id="tab-payments" class="tab-panel">
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:.5rem;margin-bottom:1rem">
                        <h2 class="card-title" style="margin:0">Recent Payments</h2>
                        <div style="display:flex;gap:.5rem;flex-wrap:wrap">
                            <select id="filter-status" class="form-control" style="width:auto">
                                <option value="">All</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="failed">Failed</option>
                            </select>
                            <input type="date" id="filter-date-from" class="form-control" style="width:auto">
                            <input type="date" id="filter-date-to" class="form-control" style="width:auto">
                            <button id="btn-filter-payments" class="btn btn-primary btn-sm">Filter</button>
                        </div>
                    </div>
                    <div class="table-responsive" id="payments-table-wrap">
                        <p style="color:var(--text-muted)">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Templates Tab -->
        <div id="tab-templates" class="tab-panel hidden">
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                        <h2 class="card-title" style="margin:0">Templates</h2>
                        <button id="btn-new-template" class="btn btn-primary btn-sm">+ New Template</button>
                    </div>
                    <div id="templates-table-wrap">
                        <p style="color:var(--text-muted)">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Library Tab -->
        <div id="tab-library" class="tab-panel hidden">
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                        <h2 class="card-title" style="margin:0">Document Library</h2>
                        <button id="btn-upload-library" class="btn btn-primary btn-sm">+ Upload File</button>
                    </div>
                    <form id="library-upload-form" class="hidden" style="background:var(--bg);padding:1rem;border-radius:var(--radius);margin-bottom:1rem">
                        <div class="form-group">
                            <label class="form-label">Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. letters, affidavits">
                        </div>
                        <div class="form-group">
                            <label class="form-label">File</label>
                            <input type="file" name="file" class="form-control" required>
                        </div>
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_free" value="1" checked> Free download
                        </label>
                        <div style="margin-top:1rem;display:flex;gap:.5rem">
                            <button type="submit" class="btn btn-primary btn-sm">Upload</button>
                            <button type="button" id="btn-cancel-upload" class="btn btn-outline btn-sm">Cancel</button>
                        </div>
                    </form>
                    <div id="library-table-wrap">
                        <p style="color:var(--text-muted)">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Tab -->
        <div id="tab-users" class="tab-panel hidden">
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                        <h2 class="card-title" style="margin:0">Users</h2>
                        <input type="search" id="user-search" class="form-control" style="width:250px" placeholder="Search by phone or name…">
                    </div>
                    <div id="users-table-wrap">
                        <p style="color:var(--text-muted)">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promo Codes Tab -->
        <div id="tab-promos" class="tab-panel hidden">
            <div class="card">
                <div class="card-body">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:1rem">
                        <h2 class="card-title" style="margin:0">Promo Codes</h2>
                        <button id="btn-new-promo" class="btn btn-primary btn-sm">+ New Code</button>
                    </div>
                    <form id="promo-create-form" class="hidden" style="background:var(--bg);padding:1rem;border-radius:var(--radius);margin-bottom:1rem">
                        <div style="display:flex;gap:1rem;flex-wrap:wrap">
                            <div class="form-group" style="flex:1;min-width:120px">
                                <label class="form-label">Code (leave blank to auto-generate)</label>
                                <input type="text" name="code" class="form-control" style="text-transform:uppercase">
                            </div>
                            <div class="form-group" style="min-width:100px">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-control" required>
                                    <option value="percent">Percent (%)</option>
                                    <option value="fixed">Fixed (KSh)</option>
                                </select>
                            </div>
                            <div class="form-group" style="min-width:100px">
                                <label class="form-label">Value</label>
                                <input type="number" name="value" class="form-control" min="0" required>
                            </div>
                            <div class="form-group" style="min-width:100px">
                                <label class="form-label">Max Uses</label>
                                <input type="number" name="max_uses" class="form-control" min="1" placeholder="Unlimited">
                            </div>
                            <div class="form-group" style="min-width:150px">
                                <label class="form-label">Expires</label>
                                <input type="datetime-local" name="expires_at" class="form-control">
                            </div>
                        </div>
                        <div style="margin-top:.5rem;display:flex;gap:.5rem">
                            <button type="submit" class="btn btn-primary btn-sm">Create</button>
                            <button type="button" id="btn-cancel-promo" class="btn btn-outline btn-sm">Cancel</button>
                        </div>
                    </form>
                    <div id="promos-table-wrap">
                        <p style="color:var(--text-muted)">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Missing Documents Tab -->
        <div id="tab-missing" class="tab-panel hidden">
            <div class="card">
                <div class="card-body">
                    <h2 class="card-title">Missing Document Requests</h2>
                    <div id="missing-table-wrap">
                        <p style="color:var(--text-muted)">Loading…</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="/js/admin.js"></script>
@endpush
