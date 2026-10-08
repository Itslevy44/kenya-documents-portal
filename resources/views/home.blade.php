@extends('layouts.app')

@section('title', 'Kenya Document Assistant')
@section('meta-description', 'Generate official Kenyan documents instantly — KSh 50 per document. Affidavits, letters, business docs and more.')

@section('content')

<!-- Hero -->
<section class="hero" role="banner">
    <div class="container">
        <h1 class="hero-title">Kenya Document Assistant</h1>
        <p class="hero-subtitle">Generate official Kenyan documents in minutes.<br>Pay KSh 50, download instantly.</p>
        <div style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap;margin-top:1.5rem">
            <a href="{{ route('library.index') }}" class="btn btn-accent btn-lg">Browse Templates</a>
            <a href="{{ route('signin') }}" class="btn btn-outline-white btn-lg">Sign In Free</a>
        </div>
    </div>
</section>

<!-- Categories -->
<section class="section" id="categories">
    <div class="container">
        <h2 class="section-title">Document Categories</h2>
        <p class="section-subtitle">Choose a category to get started.</p>

        @if($categories->isNotEmpty())
        <div class="grid grid-3 mt-3">
            @foreach($categories as $category)
            <a href="{{ route('category', $category->slug) }}" class="card card-link" aria-label="{{ $category->name }}">
                <div class="card-body" style="text-align:center;padding:2rem 1rem">
                    <div style="font-size:2.5rem;margin-bottom:.75rem">{{ $category->icon ?? '📄' }}</div>
                    <h3 class="card-title" style="margin:0 0 .5rem">{{ $category->name }}</h3>
                    <p class="card-text" style="font-size:.9rem">{{ $category->description }}</p>
                </div>
            </a>
            @endforeach
        </div>
        @else
        <p style="text-align:center;color:var(--text-muted)">Categories coming soon.</p>
        @endif
    </div>
</section>

<!-- How it works -->
<section class="section" style="background:var(--surface)">
    <div class="container">
        <h2 class="section-title">How It Works</h2>
        <div class="grid grid-3 mt-3">
            <div style="text-align:center;padding:1.5rem 1rem">
                <div style="font-size:2.5rem;margin-bottom:.75rem">✍️</div>
                <h3>1. Fill the Form</h3>
                <p>Choose your document and fill in your details.</p>
            </div>
            <div style="text-align:center;padding:1.5rem 1rem">
                <div style="font-size:2.5rem;margin-bottom:.75rem">💳</div>
                <h3>2. Pay via M-Pesa</h3>
                <p>Secure payment — just KSh 50 per document.</p>
            </div>
            <div style="text-align:center;padding:1.5rem 1rem">
                <div style="font-size:2.5rem;margin-bottom:.75rem">📥</div>
                <h3>3. Download Instantly</h3>
                <p>Get your PDF and Word file immediately.</p>
            </div>
        </div>
    </div>
</section>

@endsection
