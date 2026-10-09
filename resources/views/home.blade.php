@extends('layouts.app')

@section('title', 'Kenya Document Assistant & Legal Templates')
@section('meta-description', 'Generate official Kenyan documents, sworn affidavits, letters, agreements, and ATS CVs in minutes. Pay KSh 50, download PDF and Word instantly.')

@section('content')

<!-- Modern Hero Section -->
<section class="hero" role="banner" style="position: relative; overflow: hidden;">
    <div class="container" style="position: relative; z-index: 2;">
        <!-- Top Trust Pill -->
        <div style="margin-bottom: 1.25rem;">
            <span class="badge badge-accent" style="font-size: .85rem; padding: .4rem 1.1rem; border-radius: 999px; box-shadow: 0 2px 8px rgba(0,0,0,.15); text-transform: none; font-weight: 600;">
                🇰🇪 Official Kenyan Administrative, Legal &amp; Career Documents
            </span>
        </div>

        <h1 class="hero-title" style="letter-spacing: -0.02em;">
            Kenya Document Assistant
        </h1>
        <p class="hero-subtitle" style="max-width: 720px; margin-inline: auto;">
            Generate valid Kenyan affidavits, formal letters, contracts, and ATS-compliant CVs in minutes.<br>
            Fill the form, preview your draft, and download ready-to-sign PDF and Word files.
        </p>

        <!-- Main Action Buttons -->
        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1.75rem;">
            <a href="{{ route('library.index') }}" class="btn btn-accent btn-lg" style="box-shadow: 0 4px 14px rgba(255,143,0,.35);">
                📑 Browse Document Templates
            </a>
            <a href="/cv-assistant" class="btn btn-outline-white btn-lg" style="background: rgba(255,255,255,.15); border-color: rgba(255,255,255,.8); color: #fff;">
                ✨ Smart CV Assistant
            </a>
            @guest
            <a href="{{ route('signin') }}" class="btn btn-outline-white btn-lg">
                Sign In Free
            </a>
            @endguest
        </div>

        <!-- Trust & Metrics Bar -->
        <div class="hero-metrics" style="display: flex; justify-content: center; gap: clamp(1rem, 4vw, 3rem); flex-wrap: wrap; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid rgba(255,255,255,.2);">
            <div style="text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--accent-light, #FFB300);">50+</div>
                <div style="font-size: .85rem; opacity: .85;">Vetted Templates</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 800; color: #fff;">From KSh 50</div>
                <div style="font-size: .85rem; opacity: .85;">Transparent Pricing</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 800; color: var(--accent-light, #FFB300);">Instant</div>
                <div style="font-size: .85rem; opacity: .85;">PDF &amp; Word Download</div>
            </div>
            <div style="text-align: center;">
                <div style="font-size: 1.75rem; font-weight: 800; color: #fff;">100%</div>
                <div style="font-size: .85rem; opacity: .85;">Kenyan Law Compliant</div>
            </div>
        </div>
    </div>
</section>

<!-- Spotlight: CV Assistant Promo Banner -->
<section style="background: #FFFFFF; border-bottom: 1px solid var(--border); padding: 2.5rem 0;">
    <div class="container">
        <div class="card" style="background: linear-gradient(135deg, #F0FDF4 0%, #DCFCE7 100%); border: 2px solid #86EFAC; border-radius: 16px; box-shadow: 0 4px 20px rgba(34,197,94,.12);">
            <div class="card-body" style="padding: clamp(1.5rem, 3vw, 2.5rem); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
                <div style="max-width: 650px;">
                    <span class="badge badge-primary mb-2">🔥 NEW CAREER FEATURE</span>
                    <h2 style="font-size: clamp(1.3rem, 2.5vw, 1.8rem); font-weight: 800; color: #14532D; margin-bottom: .5rem;">
                        Job-Ready CV Builder &amp; ATS Resume Reviewer
                    </h2>
                    <p style="color: #166534; font-size: .98rem; margin: 0; line-height: 1.6;">
                        Upload your existing CV for automated scoring and smart keyword enhancements, or use our structured builder to generate a professional Kenyan CV format ready for job applications.
                    </p>
                </div>
                <div>
                    <a href="/cv-assistant" class="btn btn-primary btn-lg" style="white-space: nowrap; box-shadow: 0 4px 12px rgba(22,101,52,.25);">
                        Launch CV Assistant →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Document Categories -->
<section class="section" id="categories">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">Document Categories</h2>
            <p class="section-subtitle">Select a category below to generate your tailored legal or administrative document.</p>
        </div>

        @if($categories->isNotEmpty())
        <div class="grid grid-3 mt-3" style="gap: 1.5rem;">
            @foreach($categories as $category)
            <a href="{{ route('category', $category->slug) }}" class="card card-link category-card" aria-label="{{ $category->name }}" style="border-radius: 14px; border: 1px solid var(--border); transition: all .25s ease;">
                <div class="card-body" style="text-align: center; padding: 2.25rem 1.25rem;">
                    <div style="width: 68px; height: 68px; margin: 0 auto 1.25rem; border-radius: 50%; background: #E8F5E9; display: flex; align-items: center; justify-content: center; font-size: 2.2rem;">
                        {{ $category->icon ?? '📄' }}
                    </div>
                    <h3 class="card-title" style="margin: 0 0 .6rem; font-size: 1.2rem; font-weight: 700; color: var(--primary);">
                        {{ $category->name }}
                    </h3>
                    <p class="card-text" style="font-size: .92rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                        {{ $category->description }}
                    </p>
                    <div style="margin-top: 1.25rem; font-size: .88rem; font-weight: 600; color: var(--accent);">
                        Explore Templates &rarr;
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <p style="text-align: center; color: var(--text-muted);">Categories coming soon.</p>
        @endif
    </div>
</section>

<!-- How it works -->
<section class="section" style="background: var(--surface); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="text-center mb-4">
            <h2 class="section-title">How Kenya Docs Works</h2>
            <p class="section-subtitle">3 quick steps to generate your legally compliant document.</p>
        </div>

        <div class="grid grid-3 mt-3" style="gap: 2rem;">
            <div class="card" style="padding: 2rem 1.5rem; text-align: center; border-radius: 14px; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="width: 56px; height: 56px; margin: 0 auto 1rem; border-radius: 50%; background: #E8F5E9; color: var(--primary); font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                    1
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: .5rem; color: var(--text);">Choose &amp; Fill</h3>
                <p style="font-size: .92rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                    Select the template that fits your situation and fill our easy, guided form in less than 2 minutes.
                </p>
            </div>

            <div class="card" style="padding: 2rem 1.5rem; text-align: center; border-radius: 14px; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="width: 56px; height: 56px; margin: 0 auto 1rem; border-radius: 50%; background: #FFF8E1; color: var(--accent-dark); font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                    2
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: .5rem; color: var(--text);">Preview &amp; Pay via M-Pesa</h3>
                <p style="font-size: .92rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                    Review an instant watermark draft. Pay KSh 50 seamlessly with our secure M-Pesa STK push.
                </p>
            </div>

            <div class="card" style="padding: 2rem 1.5rem; text-align: center; border-radius: 14px; border: 1px solid var(--border); box-shadow: var(--shadow);">
                <div style="width: 56px; height: 56px; margin: 0 auto 1rem; border-radius: 50%; background: #E8F5E9; color: var(--primary); font-size: 1.6rem; font-weight: 800; display: flex; align-items: center; justify-content: center;">
                    3
                </div>
                <h3 style="font-size: 1.15rem; font-weight: 700; margin-bottom: .5rem; color: var(--text);">Download &amp; Sign</h3>
                <p style="font-size: .92rem; color: var(--text-muted); line-height: 1.5; margin: 0;">
                    Receive ready-to-print PDF and fully editable Microsoft Word documents instantly.
                </p>
            </div>
        </div>
    </div>
</section>

<!-- Trust & Security Assurance -->
<section class="section py-4">
    <div class="container text-center" style="max-width: 800px;">
        <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--primary); margin-bottom: .75rem;">
            🛡️ Secure, Private &amp; Compliant
        </h3>
        <p style="color: var(--text-muted); font-size: .95rem; line-height: 1.6;">
            We do not share your private identity details. All documents are formatted to meet Kenyan Commissioner for Oaths, judicial, corporate, and civil registration standards.
        </p>
    </div>
</section>

@endsection
