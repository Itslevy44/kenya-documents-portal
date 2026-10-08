@extends('layouts.app')

@section('title', $template->name)
@section('meta-description', $template->meta_description ?? $template->description)

@section('content')
<section class="section">
    <div class="container">

        <!-- Breadcrumb -->
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <ol>
                <li><a href="{{ route('home') }}">Home</a></li>
                <li><a href="{{ route('category', $template->category->slug) }}">{{ $template->category->name }}</a></li>
                <li aria-current="page">{{ $template->name }}</li>
            </ol>
        </nav>

        <div class="builder-layout">
            <!-- Form Panel -->
            <div class="builder-form-panel">
                <h1 class="section-title" id="template-name">{{ $template->name }}</h1>
                <p class="section-subtitle">{{ $template->description }}</p>
                <div class="price-tag">
                    <span>KSh {{ number_format($template->price, 0) }}</span>
                </div>

                <form id="builder-form" novalidate
                      data-slug="{{ $template->slug }}"
                      data-schema="{{ json_encode($template->schema) }}"
                      data-template-id="{{ $template->id }}">
                    @csrf
                    <div id="form-fields">
                        {{-- Fields rendered by builder.js from schema --}}
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3" id="btn-preview">
                        <span id="btn-preview-label">Preview Document</span>
                        <span id="btn-preview-spinner" class="hidden">Generating preview…</span>
                    </button>
                </form>
            </div>

            <!-- Preview Panel -->
            <div class="builder-preview-panel" id="preview-panel" aria-live="polite">
                <div class="preview-placeholder">
                    <span style="font-size:3rem">📄</span>
                    <p>Fill in the form and click <strong>Preview Document</strong> to see a draft.</p>
                </div>
            </div>
        </div>

    </div>
</section>
@endsection

@push('scripts')
<script src="/js/builder.js"></script>
@endpush
