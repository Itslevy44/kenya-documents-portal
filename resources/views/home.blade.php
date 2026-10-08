@extends('layouts.app')

@section('title', 'Home')

@section('content')
{{-- FEAT-002: Hero section, featured categories grid, search --}}
<section class="hero">
    <div class="container">
        <h1 class="hero-title">Kenya Document Assistant</h1>
        <p class="hero-subtitle">Generate official Kenyan documents in minutes — KSh 50 per document.</p>
        <a href="{{ route('library.index') }}" class="btn btn-accent btn-lg">Browse Templates</a>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Popular Categories</h2>
        <p class="section-subtitle">Choose a document category to get started.</p>
        <div id="categories-grid" class="grid grid-3">
            {{-- Populated via home.js / FEAT-002 --}}
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/home.js"></script>
@endpush
