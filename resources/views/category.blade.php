@extends('layouts.app')

@section('title', $category->name . ' — Document Templates')
@section('meta-description', $category->description)

@section('content')

<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);padding:2.5rem 0;">
    <div class="container">
        <nav class="breadcrumb" style="margin-bottom:.75rem;">
            <ol style="color:rgba(255,255,255,.6);">
                <li><a href="{{ route('home') }}" style="color:rgba(255,255,255,.7);">Home</a></li>
                <li aria-current="page" style="color:#fff;font-weight:600;">{{ $category->name }}</li>
            </ol>
        </nav>
        <div style="text-align:center;">
            @if($category->icon)
            <div style="font-size:2.5rem;margin-bottom:.6rem;">{{ $category->icon }}</div>
            @endif
            <h1 style="font-size:clamp(1.6rem,3vw,2.2rem);font-weight:800;color:#fff;letter-spacing:-.02em;margin-bottom:.4rem;">
                {{ $category->name }}
            </h1>
            <p style="color:rgba(255,255,255,.75);font-size:.95rem;max-width:560px;margin:0 auto;">
                {{ $category->description }}
            </p>
        </div>
    </div>
</div>

<section class="section" style="background:var(--bg);">
<div class="container">

@if($templates->isNotEmpty())
<div class="grid grid-3" style="gap:1.5rem;">
    @foreach($templates as $template)
    <div class="card" style="border-radius:16px;">
        <div class="card-body">
            <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:.75rem;margin-bottom:.75rem;">
                <h3 class="card-title" style="font-size:1.05rem;margin:0;">{{ $template->name }}</h3>
                <span style="font-size:1rem;font-weight:800;color:var(--primary);white-space:nowrap;flex-shrink:0;">
                    KSh {{ number_format($template->price, 0) }}
                </span>
            </div>
            <p class="card-text" style="font-size:.875rem;line-height:1.55;margin-bottom:1.25rem;">
                {{ \Illuminate\Support\Str::limit($template->description, 110) }}
            </p>
            <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;">
                <div style="display:flex;gap:.4rem;flex-wrap:wrap;">
                    <span class="badge badge-success">PDF</span>
                    <span class="badge badge-info">Word</span>
                    <span class="badge badge-primary">Instant</span>
                </div>
                <a href="{{ route('document.show', $template->slug) }}"
                   class="btn btn-primary btn-sm">
                    Generate →
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <div class="empty-state-icon">📂</div>
    <p class="empty-state-title">No templates yet</p>
    <p class="empty-state-desc">Templates for this category are coming soon.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">← Back to Home</a>
</div>
@endif

</div>
</section>

@endsection
