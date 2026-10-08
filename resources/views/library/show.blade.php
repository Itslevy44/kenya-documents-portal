@extends('layouts.app')

@section('title', $item->title)

@section('content')
<section class="section">
    <div class="container">

        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('library.index') }}">Library</a></li>
                <li aria-current="page">{{ $item->title }}</li>
            </ol>
        </nav>

        <div class="card" style="max-width:700px;margin:0 auto">
            <div class="card-body">
                <!-- Badge -->
                <span class="badge" style="background:{{ $item->is_free ? 'var(--success)' : 'var(--accent)' }};color:#fff;padding:.2em .7em;border-radius:4px;font-size:.8rem">
                    {{ $item->is_free ? 'Free' : 'Premium' }}
                </span>

                @if($item->category)
                <span style="margin-left:.5rem;font-size:.85rem;color:var(--text-muted)">{{ ucfirst($item->category) }}</span>
                @endif

                <h1 class="section-title" style="margin-top:.75rem">{{ $item->title }}</h1>

                <div style="color:var(--text-muted);font-size:.9rem;margin-bottom:1.5rem">
                    <span>{{ number_format($item->file_size / 1024, 0) }} KB</span>
                    &bull;
                    <span>{{ strtoupper(pathinfo($item->file_name, PATHINFO_EXTENSION)) }}</span>
                    &bull;
                    <span>{{ $item->download_count }} downloads</span>
                </div>

                <div style="line-height:1.7;margin-bottom:2rem">
                    {!! nl2br(e($item->description)) !!}
                </div>

                <a href="{{ route('library.download', $item->id) }}"
                   class="btn btn-primary btn-lg btn-block"
                   @if(!$item->is_free && !auth()->check()) onclick="event.preventDefault(); window.location='{{ route('signin') }}'" @endif>
                    ⬇ Download {{ $item->is_free ? '(Free)' : '(Sign in required)' }}
                </a>

                @if(!$item->is_free && !auth()->check())
                <p style="text-align:center;font-size:.85rem;color:var(--text-muted);margin-top:.75rem">
                    <a href="{{ route('signin') }}">Sign in</a> to download this document.
                </p>
                @endif

                <div style="margin-top:2rem;text-align:center">
                    <a href="{{ route('library.index') }}" class="btn btn-link">← Back to library</a>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection
