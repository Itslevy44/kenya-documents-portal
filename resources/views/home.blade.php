@extends('layouts.app')

@section('title', 'Kenya Document Assistant & Legal Templates')
@section('meta-description', 'Generate official Kenyan documents, sworn affidavits, letters, agreements and ATS-ready CVs in minutes. Pay KSh 50, download PDF and Word instantly.')

@section('content')

{{-- ── HERO ─────────────────────────────────────────────────── --}}
<section class="hero" role="banner">
    <div class="container" style="position:relative;z-index:2;">

        <div class="hero-badge">
            🇰🇪 &nbsp;Official Kenyan Administrative, Legal &amp; Career Documents
        </div>

        <h1 class="hero-title">
            Kenya Document<br><span>Assistant</span>
        </h1>

        <p class="hero-subtitle">
            Generate legally compliant affidavits, formal letters, contracts and ATS-ready CVs in minutes.
            Fill the form, preview your draft, pay KSh&nbsp;50 via M-Pesa and download PDF&nbsp;&amp;&nbsp;Word instantly.
        </p>

        <div class="hero-actions">
            <a href="{{ route('library.index') }}" class="btn btn-accent btn-xl">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Browse Templates
            </a>
            <a href="{{ route('cv.assistant') }}" class="btn btn-outline-white btn-xl">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                Smart CV Builder
            </a>
            @guest
            <a href="{{ route('signin') }}" class="btn btn-outline-white btn-xl">
                Sign In Free
            </a>
            @endguest
        </div>

        <div class="hero-stats">
            <div style="text-align:center">
                <span class="hero-stat-num">50+</span>
                <span class="hero-stat-label">Vetted Templates</span>
            </div>
            <div style="text-align:center">
                <span class="hero-stat-num">KSh&nbsp;50</span>
                <span class="hero-stat-label">Starting Price</span>
            </div>
            <div style="text-align:center">
                <span class="hero-stat-num">Instant</span>
                <span class="hero-stat-label">PDF &amp; Word Download</span>
            </div>
            <div style="text-align:center">
                <span class="hero-stat-num">100%</span>
                <span class="hero-stat-label">Kenya Law Compliant</span>
            </div>
        </div>
    </div>
</section>

{{-- ── CV FEATURE BANNER ────────────────────────────────────── --}}
<section style="background:#fff;border-bottom:1px solid var(--border);padding:2.5rem 0;">
    <div class="container">
        <div class="card" style="background:linear-gradient(135deg,#F0FDF4 0%,#DCFCE7 100%);border:2px solid #86EFAC;border-radius:16px;box-shadow:0 4px 20px rgba(34,197,94,.1);">
            <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1.5rem;">
                <div style="flex:1;min-width:260px;">
                    <span class="badge badge-success" style="margin-bottom:.75rem;">🔥 &nbsp;Career Feature</span>
                    <h2 style="font-size:clamp(1.25rem,2.5vw,1.75rem);font-weight:800;color:#14532D;margin-bottom:.5rem;line-height:1.2;">
                        Job-Ready CV Builder &amp; ATS Resume Reviewer
                    </h2>
                    <p style="color:#166534;font-size:.96rem;margin:0;line-height:1.6;">
                        Craft a Kenyan-market CV from scratch or upload your existing resume for an instant ATS score, smart keyword boost and professional restructure.
                    </p>
                </div>
                <div style="flex-shrink:0;">
                    <a href="{{ route('cv.assistant') }}" class="btn btn-primary btn-lg" style="white-space:nowrap;">
                        Launch CV Assistant →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ── DOCUMENT CATEGORIES ──────────────────────────────────── --}}
<section class="section" id="categories" style="background:var(--bg);">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">What we offer</span>
            <h2 class="section-title">Document Categories</h2>
            <p class="section-subtitle">Select a category to generate your tailored legal or administrative document.</p>
        </div>

        @if($categories->isNotEmpty())
        <div class="grid grid-3" style="gap:1.5rem;">
            @foreach($categories as $category)
            <a href="{{ route('category', $category->slug) }}"
               class="card card-link"
               aria-label="Browse {{ $category->name }} templates"
               style="border-radius:16px;">
                <div class="card-body" style="text-align:center;padding:2.25rem 1.5rem;">
                    <div class="cat-icon">{{ $category->icon ?? '📄' }}</div>
                    <h3 class="card-title" style="font-size:1.15rem;color:var(--primary);margin-bottom:.5rem;">
                        {{ $category->name }}
                    </h3>
                    <p class="card-text" style="font-size:.88rem;line-height:1.55;margin-bottom:1.25rem;">
                        {{ $category->description }}
                    </p>
                    <span style="font-size:.85rem;font-weight:700;color:var(--accent);display:inline-flex;align-items:center;gap:.3rem;">
                        Explore Templates
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </span>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <div class="empty-state">
            <div class="empty-state-icon">📂</div>
            <p class="empty-state-title">Categories coming soon</p>
            <p class="empty-state-desc">We're adding more document categories. Check back shortly.</p>
        </div>
        @endif
    </div>
</section>

{{-- ── HOW IT WORKS ─────────────────────────────────────────── --}}
<section class="section" style="background:#fff;border-top:1px solid var(--border);border-bottom:1px solid var(--border);">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Simple process</span>
            <h2 class="section-title">How Kenya Docs Works</h2>
            <p class="section-subtitle">Three steps from template to ready-to-sign document in under three minutes.</p>
        </div>

        <div class="grid grid-3" style="gap:1.5rem;margin-top:.5rem;">

            <div class="card how-step" style="text-align:center;">
                <div class="how-step-num">1</div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem;">Choose &amp; Fill</h3>
                <p style="font-size:.9rem;color:var(--text-muted);line-height:1.6;">
                    Pick your document type and complete our guided, plain-language form in under two minutes.
                </p>
            </div>

            <div class="card how-step" style="text-align:center;">
                <div class="how-step-num">2</div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem;">Preview &amp; Pay via M-Pesa</h3>
                <p style="font-size:.9rem;color:var(--text-muted);line-height:1.6;">
                    Review a watermarked draft instantly, then pay just KSh&nbsp;50 via a secure M-Pesa STK push — no card required.
                </p>
            </div>

            <div class="card how-step" style="text-align:center;">
                <div class="how-step-num">3</div>
                <h3 style="font-size:1.1rem;font-weight:700;margin-bottom:.5rem;">Download &amp; Sign</h3>
                <p style="font-size:.9rem;color:var(--text-muted);line-height:1.6;">
                    Receive a print-ready PDF and a fully editable Microsoft Word file the moment payment confirms.
                </p>
            </div>

        </div>
    </div>
</section>

{{-- ── FEATURES GRID ────────────────────────────────────────── --}}
<section class="section" style="background:var(--bg);">
    <div class="container">
        <div class="section-header">
            <span class="section-eyebrow">Why choose us</span>
            <h2 class="section-title">Built for Kenyans, by Kenyans</h2>
            <p class="section-subtitle">Everything you need to get professionally formatted, legally valid documents — fast.</p>
        </div>

        <div class="grid grid-4" style="gap:1.25rem;margin-top:.5rem;">
            @php
            $features = [
                ['icon'=>'⚡','title'=>'Instant Generation','desc'=>'Documents are created on-the-fly in seconds — no waiting, no queues.'],
                ['icon'=>'⚖️','title'=>'Legally Compliant','desc'=>'Formatted to Kenyan Commissioner for Oaths, judicial and civil registration standards.'],
                ['icon'=>'🔒','title'=>'Secure &amp; Private','desc'=>'Your personal details are encrypted and never shared with third parties.'],
                ['icon'=>'📱','title'=>'M-Pesa Payments','desc'=>'Pay seamlessly from any Kenyan mobile network via STK Push — no card needed.'],
                ['icon'=>'📄','title'=>'PDF &amp; Word','desc'=>'Download a print-ready PDF and an editable Word document simultaneously.'],
                ['icon'=>'✏️','title'=>'Re-edit Window','desc'=>'Made a mistake? Edit and regenerate your document up to 3 times within 24 hours.'],
                ['icon'=>'📚','title'=>'Document Library','desc'=>'Free and premium reference documents covering Kenyan law and administration.'],
                ['icon'=>'🤖','title'=>'Smart CV Builder','desc'=>'ATS-optimised CV generator with automated scoring and restructuring for the Kenyan job market.'],
            ];
            @endphp

            @foreach($features as $f)
            <div class="card" style="border-radius:14px;padding:0;">
                <div class="card-body" style="padding:1.5rem 1.25rem;">
                    <div style="font-size:1.75rem;margin-bottom:.8rem;">{{ $f['icon'] }}</div>
                    <h3 style="font-size:.98rem;font-weight:700;margin-bottom:.4rem;color:var(--text);">{!! $f['title'] !!}</h3>
                    <p style="font-size:.85rem;color:var(--text-muted);line-height:1.55;margin:0;">{!! $f['desc'] !!}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ── TRUST SECTION ────────────────────────────────────────── --}}
<section class="section" style="background:var(--primary-dark);color:#fff;">
    <div class="container" style="max-width:860px;text-align:center;">

        <h2 style="font-size:clamp(1.5rem,3vw,2.1rem);font-weight:800;color:#fff;margin-bottom:1rem;letter-spacing:-.02em;">
            🛡️ &nbsp;Trusted. Secure. Always Available.
        </h2>
        <p style="font-size:1rem;color:rgba(255,255,255,.75);line-height:1.7;margin-bottom:2rem;">
            We do not store or share your personal identity information beyond what is necessary to generate your document. National ID numbers are stored end-to-end encrypted. Payments are processed exclusively through M-Pesa — Kenya's most trusted mobile money platform.
        </p>

        <div style="display:flex;justify-content:center;flex-wrap:wrap;gap:2rem;">
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:800;color:var(--accent-light);">AES-256</div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.55);margin-top:.25rem;">Encrypted Storage</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:800;color:var(--accent-light);">M-Pesa</div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.55);margin-top:.25rem;">Secure Payments</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:800;color:var(--accent-light);">24/7</div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.55);margin-top:.25rem;">Always Available</div>
            </div>
            <div style="text-align:center;">
                <div style="font-size:1.75rem;font-weight:800;color:var(--accent-light);">Kenya Law</div>
                <div style="font-size:.8rem;color:rgba(255,255,255,.55);margin-top:.25rem;">Compliant Formats</div>
            </div>
        </div>
    </div>
</section>

{{-- ── BOTTOM CTA ───────────────────────────────────────────── --}}
<section class="section" style="background:#fff;">
    <div class="container" style="max-width:640px;text-align:center;">
        <h2 style="font-size:clamp(1.5rem,3vw,2rem);font-weight:800;letter-spacing:-.02em;margin-bottom:.75rem;">
            Ready to generate your document?
        </h2>
        <p style="color:var(--text-muted);font-size:1rem;margin-bottom:2rem;line-height:1.6;">
            Join thousands of Kenyans who get their legal and career documents done in minutes — no office visits, no queues.
        </p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;">
            <a href="{{ route('library.index') }}" class="btn btn-primary btn-xl">
                Browse All Templates
            </a>
            @guest
            <a href="{{ route('signin') }}" class="btn btn-outline btn-xl">
                Create Free Account
            </a>
            @endguest
        </div>
    </div>
</section>

@endsection
