@extends('layouts.app')

@section('title', 'Download — {{ $template->name }}')

@section('content')

<section class="section" style="background:var(--bg);">
<div class="container">
<div style="max-width:560px;margin:0 auto;">

    <div class="card" style="border-radius:20px;box-shadow:var(--shadow-lg);">
        <div class="card-body" style="padding:clamp(2rem,5vw,3rem);text-align:center;">

            {{-- Success icon --}}
            <div class="download-success-icon" style="margin-bottom:1.5rem;">
                <svg width="36" height="36" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
            </div>

            <h1 style="font-size:1.65rem;font-weight:800;letter-spacing:-.02em;margin-bottom:.4rem;">
                Payment Successful!
            </h1>
            <p style="color:var(--text-muted);font-size:.95rem;margin-bottom:2rem;">
                Your <strong>{{ $template->name }}</strong> is ready to download.
            </p>

            {{-- Download buttons --}}
            <div style="display:flex;flex-direction:column;gap:.875rem;margin-bottom:2rem;">
                <a href="{{ route('document.download', ['token' => $doc->session_token, 'type' => 'pdf']) }}"
                   class="download-btn"
                   download>
                    <div class="dl-icon dl-pdf">📄</div>
                    <div style="text-align:left;">
                        <div style="font-size:1rem;font-weight:700;">Download PDF</div>
                        <div style="font-size:.78rem;color:var(--text-muted);">Print-ready, high quality</div>
                    </div>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:auto;flex-shrink:0;"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>

                <a href="{{ route('document.download', ['token' => $doc->session_token, 'type' => 'word']) }}"
                   class="download-btn"
                   download>
                    <div class="dl-icon dl-word">📝</div>
                    <div style="text-align:left;">
                        <div style="font-size:1rem;font-weight:700;">Download Word (.docx)</div>
                        <div style="font-size:.78rem;color:var(--text-muted);">Fully editable in Microsoft Word</div>
                    </div>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:auto;flex-shrink:0;"><path d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                </a>
            </div>

            {{-- Edit window --}}
            @if($doc->edit_window_expires_at && $doc->edit_window_expires_at->isFuture())
            <div class="alert alert-success" style="text-align:left;margin-bottom:1.5rem;">
                <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                <div>
                    <strong>Edit window open</strong> — you can re-edit this document
                    {{ 3 - $doc->edit_count }} more time(s) until
                    {{ $doc->edit_window_expires_at->format('d M Y, H:i') }}.
                    @if($doc->edit_count < 3)
                    <br><a href="{{ route('document.show', $doc->template->slug) }}?token={{ $doc->session_token }}"
                           style="color:var(--success);font-weight:600;">Re-edit document →</a>
                    @endif
                </div>
            </div>
            @endif

            {{-- Receipt --}}
            @if($doc->payment)
            <div style="background:var(--bg);border-radius:10px;padding:.875rem 1rem;font-size:.82rem;color:var(--text-muted);margin-bottom:1.5rem;text-align:left;">
                <div style="display:flex;justify-content:space-between;margin-bottom:.25rem;">
                    <span>M-Pesa Receipt</span>
                    <strong style="color:var(--text);">{{ $doc->payment->mpesa_receipt ?? 'Pending' }}</strong>
                </div>
                <div style="display:flex;justify-content:space-between;">
                    <span>Amount Paid</span>
                    <strong style="color:var(--text);">KSh {{ number_format($doc->payment->amount - $doc->payment->discount_amount, 2) }}</strong>
                </div>
            </div>
            @endif

            {{-- Navigation --}}
            <div style="display:flex;justify-content:center;gap:.75rem;flex-wrap:wrap;">
                <a href="{{ route('home') }}" class="btn btn-ghost btn-sm">← Home</a>
                @auth
                <a href="{{ route('my-documents') }}" class="btn btn-primary btn-sm">My Documents</a>
                @endauth
            </div>

        </div>
    </div>

</div>
</div>
</section>

@endsection
