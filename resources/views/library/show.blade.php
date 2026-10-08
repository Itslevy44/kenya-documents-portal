@extends('layouts.app')

@section('title', 'Template Details')

@section('content')
{{-- FEAT-003: Single template detail page --}}
<section class="section">
    <div class="container">
        <div class="flex gap-2" style="flex-wrap:wrap;align-items:flex-start">
            <div id="template-details" style="flex:2;min-width:260px">
                <h1 class="section-title" id="template-name"></h1>
                <p id="template-description" class="section-subtitle"></p>
                <div id="template-meta" class="mb-3"></div>
                <span class="price-tag" id="template-price"></span>
            </div>
            <div id="template-preview-thumb" style="flex:1;min-width:200px">
                {{-- Preview thumbnail --}}
            </div>
        </div>
        <div class="mt-4">
            <a href="#" id="btn-use-template" class="btn btn-primary btn-lg">Use This Template</a>
            <a href="{{ route('library.index') }}" class="btn btn-link ml-2">← Back to Library</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/home.js"></script>
@endpush
