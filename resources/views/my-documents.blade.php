@extends('layouts.app')

@section('title', 'My Documents')

@section('content')
<section class="section">
    <div class="container">
        <h1 class="section-title">My Documents</h1>
        <p class="section-subtitle">All documents you have generated and paid for.</p>

        @if($documents->isNotEmpty())
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Document</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($documents as $doc)
                    <tr>
                        <td>
                            <strong>{{ $doc->template->name ?? 'Unknown' }}</strong>
                        </td>
                        <td>
                            @if($doc->status === 'paid')
                                <span style="color:var(--success)">✅ Paid</span>
                            @elseif($doc->status === 'draft')
                                <span style="color:var(--accent)">⏳ Draft</span>
                            @else
                                <span style="color:var(--error)">❌ Cancelled</span>
                            @endif
                        </td>
                        <td>{{ $doc->created_at->format('d M Y') }}</td>
                        <td>
                            @if($doc->status === 'paid')
                                <a href="{{ route('document.download-page', $doc->session_token) }}"
                                   class="btn btn-primary btn-sm">Download</a>
                            @elseif($doc->status === 'draft')
                                <a href="{{ route('payment', $doc->session_token) }}"
                                   class="btn btn-accent btn-sm">Pay Now</a>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            {{ $documents->links() }}
        </div>
        @else
        <div style="text-align:center;padding:3rem 1rem">
            <p style="font-size:1.1rem;color:var(--text-muted)">You haven't generated any documents yet.</p>
            <a href="{{ route('home') }}" class="btn btn-primary mt-2">Browse Templates</a>
        </div>
        @endif

        <!-- Account deletion -->
        <div style="margin-top:3rem;border-top:1px solid var(--border);padding-top:1.5rem">
            <h3 style="color:var(--error)">Danger Zone</h3>
            <p style="font-size:.9rem;color:var(--text-muted)">
                Deleting your account permanently removes all your data. This cannot be undone.
            </p>
            <button id="btn-delete-account" class="btn btn-danger btn-sm">Delete My Account</button>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
document.getElementById('btn-delete-account')?.addEventListener('click', async function () {
    if (!confirm('Are you sure? This will permanently delete your account and all data.')) return;
    const reason = prompt('Optional: Tell us why you are leaving (helps us improve):');
    try {
        const res = await api.delete('/account', { reason: reason || '' });
        if (res.success) {
            showToast('Account deleted.', 'info');
            setTimeout(function () { window.location.href = '/'; }, 1500);
        }
    } catch (e) {
        showToast(e.message || 'Failed to delete account.', 'error');
    }
});
</script>
@endpush
