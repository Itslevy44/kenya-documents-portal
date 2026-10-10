@extends('layouts.app')

@section('title', 'My Documents')

@section('content')

<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);padding:2.5rem 0;">
    <div class="container">
        <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:.35rem;">My Documents</h1>
        <p style="color:rgba(255,255,255,.75);font-size:.95rem;margin:0;">Manage your generated documents and drafts.</p>
    </div>
</div>

<section class="section" style="background:var(--bg);">
<div class="container">

@if($documents->isNotEmpty())

    {{-- Stats bar --}}
    @php
        $paid   = $documents->getCollection()->where('status','paid')->count();
        $drafts = $documents->getCollection()->where('status','draft')->count();
    @endphp
    <div class="grid grid-3" style="gap:1rem;margin-bottom:2rem;">
        <div class="card stat-card">
            <div class="stat-value">{{ $documents->total() }}</div>
            <div class="stat-label">Total Documents</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value" style="color:var(--success)">{{ $paid }}</div>
            <div class="stat-label">Paid &amp; Ready</div>
        </div>
        <div class="card stat-card">
            <div class="stat-value" style="color:#d97706">{{ $drafts }}</div>
            <div class="stat-label">Drafts</div>
        </div>
    </div>

    {{-- Documents table --}}
    <div class="card" style="border-radius:16px;overflow:hidden;">
        <div class="card-body" style="padding:0;">
            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Document</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($documents as $doc)
                        <tr id="doc-row-{{ $doc->session_token }}">
                            <td>
                                <div style="font-weight:600;color:var(--text);">
                                    <span id="label-{{ $doc->session_token }}">
                                        {{ $doc->label ?? ($doc->template->name ?? 'Unknown Template') }}
                                    </span>
                                </div>
                                <div style="font-size:.78rem;color:var(--text-muted);margin-top:.15rem;">
                                    {{ $doc->template->name ?? '' }}
                                </div>
                            </td>
                            <td>
                                @if($doc->status === 'paid')
                                    <span class="doc-status-badge doc-status-paid">✓ Paid</span>
                                @elseif($doc->status === 'draft')
                                    <span class="doc-status-badge doc-status-draft">⏳ Draft</span>
                                @else
                                    <span class="doc-status-badge doc-status-cancelled">✕ Cancelled</span>
                                @endif
                            </td>
                            <td style="white-space:nowrap;color:var(--text-muted);font-size:.875rem;">
                                {{ $doc->created_at->format('d M Y') }}
                            </td>
                            <td>
                                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                                    @if($doc->status === 'paid')
                                        <a href="{{ route('document.download-page', $doc->session_token) }}"
                                           class="btn btn-primary btn-sm">
                                            ↓ Download
                                        </a>
                                    @elseif($doc->status === 'draft')
                                        <a href="{{ route('payment', $doc->session_token) }}"
                                           class="btn btn-accent btn-sm">
                                            💳 Pay Now
                                        </a>
                                        <button type="button"
                                                class="btn btn-ghost btn-sm"
                                                onclick="renameDraft('{{ $doc->session_token }}')">
                                            ✏ Rename
                                        </button>
                                        <button type="button"
                                                class="btn btn-danger btn-sm"
                                                onclick="deleteDraft('{{ $doc->session_token }}')">
                                            🗑 Delete
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">{{ $documents->links() }}</div>

@else
    <div class="card empty-state" style="border-radius:16px;padding:4rem 2rem;">
        <div class="empty-state-icon">📄</div>
        <p class="empty-state-title">No documents yet</p>
        <p class="empty-state-desc">Browse our templates to generate your first document.</p>
        <a href="{{ route('library.index') }}" class="btn btn-primary btn-lg">Browse Templates</a>
    </div>
@endif

{{-- Danger zone --}}
<div class="card" style="border-radius:16px;border-color:#FECACA;margin-top:2.5rem;">
    <div class="card-body">
        <h3 style="color:var(--error);font-size:1rem;font-weight:700;margin-bottom:.5rem;">Danger Zone</h3>
        <p style="font-size:.875rem;color:var(--text-muted);margin-bottom:1rem;">
            Permanently deletes your account and all associated data. This cannot be undone.
        </p>
        <button id="btn-delete-account" class="btn btn-danger btn-sm">Delete My Account</button>
    </div>
</div>

</div>
</section>

@endsection

@push('scripts')
<script>
// ── Delete draft ──────────────────────────────────────────────
async function deleteDraft(token) {
    if (!confirm('Delete this draft permanently? This cannot be undone.')) return;
    try {
        const res = await api.delete('/api/drafts/' + token);
        if (res.success) {
            const row = document.getElementById('doc-row-' + token);
            if (row) row.remove();
            showToast('Draft deleted.', 'success');
        } else {
            showToast(res.message || 'Could not delete draft.', 'error');
        }
    } catch (e) {
        showToast(e.message || 'Error deleting draft.', 'error');
    }
}

// ── Rename draft ──────────────────────────────────────────────
async function renameDraft(token) {
    const current = document.getElementById('label-' + token)?.textContent?.trim() || '';
    const newLabel = prompt('Enter a new name for this document:', current);
    if (!newLabel || newLabel.trim() === '' || newLabel.trim() === current) return;
    try {
        const res = await api.patch('/api/drafts/' + token + '/rename', { label: newLabel.trim() });
        if (res.success) {
            const el = document.getElementById('label-' + token);
            if (el) el.textContent = res.label;
            showToast('Document renamed.', 'success');
        } else {
            showToast(res.message || 'Could not rename.', 'error');
        }
    } catch (e) {
        showToast(e.message || 'Error renaming document.', 'error');
    }
}

// ── Delete account ─────────────────────────────────────────────
document.getElementById('btn-delete-account')?.addEventListener('click', async function () {
    if (!confirm('Are you sure? This will permanently delete your account and all data.')) return;
    const reason = prompt('Optional: Why are you leaving? (helps us improve)') || '';
    try {
        const res = await api.delete('/account', { reason });
        if (res.success) {
            showToast('Account deleted. Goodbye!', 'info');
            setTimeout(function () { window.location.href = '/'; }, 1800);
        } else {
            showToast(res.message || 'Failed to delete account.', 'error');
        }
    } catch (e) {
        showToast(e.message || 'Error deleting account.', 'error');
    }
});
</script>
@endpush
