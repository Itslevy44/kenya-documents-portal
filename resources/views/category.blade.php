@extends('layouts.app')

@section('title', $category->name)

@section('content')
<section class="section">
    <div class="container">

        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li aria-current="page">{{ $category->name }}</li>
            </ol>
        </nav>

        <div style="text-align:center;margin-bottom:2rem">
            @if($category->icon)
            <div style="font-size:3rem;margin-bottom:.5rem">{{ $category->icon }}</div>
            @endif
            <h1 class="section-title">{{ $category->name }}</h1>
            <p class="section-subtitle">{{ $category->description }}</p>
        </div>

        @if($templates->isNotEmpty())
        <div class="grid grid-3">
            @foreach($templates as $template)
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title">{{ $template->name }}</h3>
                    <p class="card-text">{{ \Illuminate\Support\Str::limit($template->description, 100) }}</p>
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:1rem">
                        <span style="font-weight:700;color:var(--primary);font-size:1.1rem">
                            KSh {{ number_format($template->price, 0) }}
                        </span>
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
        <div style="text-align:center;padding:3rem 1rem">
            <p style="color:var(--text-muted)">No templates available in this category yet.</p>
            <a href="{{ route('home') }}" class="btn btn-link">← Back to Home</a>
        </div>
        @endif

    </div>
</section>
@endsection
