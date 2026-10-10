@extends('layouts.app')

@section('title', $template->name . ' — Document Builder')
@section('meta-description', $template->meta_description ?? $template->description)

@section('content')

<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);padding:2rem 0;">
    <div class="container">
        <nav class="breadcrumb" aria-label="Breadcrumb" style="margin-bottom:.75rem;">
            <ol style="color:rgba(255,255,255,.6);">
                <li><a href="{{ route('home') }}" style="color:rgba(255,255,255,.7);">Home</a></li>
                <li><a href="{{ route('category', $template->category->slug) }}" style="color:rgba(255,255,255,.7);">{{ $template->category->name }}</a></li>
                <li aria-current="page" style="color:#fff;font-weight:600;">{{ $template->name }}</li>
            </ol>
        </nav>
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <h1 style="font-size:clamp(1.4rem,3vw,2rem);font-weight:800;color:#fff;margin-bottom:.25rem;letter-spacing:-.02em;">
                    {{ $template->name }}
                </h1>
                <p style="color:rgba(255,255,255,.75);font-size:.9rem;margin:0;">{{ $template->description }}</p>
            </div>
            <div class="price-tag">KSh {{ number_format($template->price, 0) }}</div>
        </div>
    </div>
</div>

<section class="section" style="background:var(--bg);">
<div class="container">
<div class="builder-layout">

    {{-- Form panel --}}
    <div class="builder-form-panel">
        <div class="card" style="border-radius:16px;">
            <div class="card-body" style="padding:clamp(1.5rem,4vw,2rem);">

                <div style="display:flex;align-items:center;gap:.6rem;margin-bottom:1.5rem;padding-bottom:1.25rem;border-bottom:1px solid var(--border);">
                    <div style="width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#E8F5E9,#A7F3D0);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                        <svg width="18" height="18" fill="none" stroke="var(--primary)" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    </div>
                    <div>
                        <div style="font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-muted);">Document Builder</div>
                        <div style="font-size:.9rem;font-weight:600;color:var(--text);">Fill in your details below</div>
                    </div>
                </div>

                <form id="builder-form" novalidate
                      data-slug="{{ $template->slug }}"
                      data-schema="{{ json_encode($template->schema) }}"
                      data-template-id="{{ $template->id }}">
                    @csrf
                    <div id="form-fields">
                        {{-- Fields rendered by builder.js from schema --}}
                        <div style="text-align:center;padding:2rem;color:var(--text-muted);">
                            <span class="spinner spinner-dark" style="width:24px;height:24px;"></span>
                            <p style="margin-top:.5rem;font-size:.9rem;">Loading form…</p>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg" id="btn-preview" style="margin-top:1.5rem;font-weight:700;">
                        <span id="btn-preview-label" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            Preview Document
                        </span>
                        <span id="btn-preview-spinner" class="hidden" style="display:flex;align-items:center;gap:.5rem;justify-content:center;">
                            <span class="spinner" style="width:18px;height:18px;border-width:2px;"></span>
                            Generating preview…
                        </span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Preview panel --}}
    <div class="builder-preview-panel" id="preview-panel" aria-live="polite">
        <div class="preview-placeholder" id="preview-placeholder">
            <svg width="48" height="48" fill="none" stroke="var(--border)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto .75rem;"><path d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
            <p style="font-size:.95rem;color:var(--text-muted);line-height:1.5;">
                Fill in the form and click<br><strong style="color:var(--text);">Preview Document</strong>
            </p>
        </div>
    </div>

</div>
</div>
</section>

@endsection

@push('scripts')
<script src="/js/builder.js"></script>
@endpush
