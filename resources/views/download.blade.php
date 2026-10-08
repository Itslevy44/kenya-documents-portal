@extends('layouts.app')

@section('title', 'Download Your Document')

@section('content')
<section class="section">
    <div class="container">
        <div class="card" style="max-width:560px;margin:3rem auto;text-align:center">
            <div class="card-body">
                <div style="font-size:4rem;margin-bottom:1rem">✅</div>
                <h1 class="section-title">Payment Successful!</h1>
                <p class="section-subtitle">Your <strong>{{ $template->name }}</strong> is ready to download.</p>

                <!-- Download Buttons -->
                <div class="download-options" style="margin:2rem 0;display:flex;flex-direction:column;gap:1rem">
                    <a href="{{ route('document.download', ['token' => $doc->session_token, 'type' => 'pdf']) }}"
                       class="btn btn-primary btn-lg"
                       download>
                        📄 Download PDF
                    </a>
                    <a href="{{ route('document.download', ['token' => $doc->session_token, 'type' => 'word']) }}"
                       class="btn btn-outline btn-lg"
                       download>
                        📝 Download Word (.docx)
                    </a>
                </div>

                <!-- Edit window info -->
                @if($doc->edit_window_expires_at && $doc->edit_window_expires_at->isFuture())
                <div class="alert" style="background:#E8F5E9;border:1px solid #A5D6A7;padding:1rem;border-radius:var(--radius);margin-bottom:1rem">
                    <p style="margin:0;font-size:.9rem">
                        <strong>Edit window:</strong> You can re-edit this document up to
                        {{ 3 - $doc->edit_count }} more time(s) until
                        {{ $doc->edit_window_expires_at->format('d M Y H:i') }}.
                    </p>
                    @if($doc->edit_count < 3)
                    <a href="{{ route('document.show', $doc->template->slug) }}?token={{ $doc->session_token }}"
                       class="btn btn-link" style="font-size:.9rem">Re-edit document →</a>
                    @endif
                </div>
                @endif

                <!-- Receipt info -->
                @if($doc->payment)
                <div style="font-size:.85rem;color:var(--text-muted);border-top:1px solid var(--border);padding-top:1rem">
                    <p>M-Pesa Receipt: <strong>{{ $doc->payment->mpesa_receipt ?? 'Pending' }}</strong></p>
                    <p>Amount Paid: KSh {{ number_format($doc->payment->amount - $doc->payment->discount_amount, 2) }}</p>
                </div>
                @endif

                <div style="margin-top:1.5rem">
                    <a href="{{ route('home') }}" class="btn btn-link">← Back to Home</a>
                    @auth
                    <a href="{{ route('my-documents') }}" class="btn btn-link">My Documents →</a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
