@extends('layouts.app')

@section('title', 'Category')

@section('content')
{{-- FEAT-002: Template listing for a given category --}}
<section class="section">
    <div class="container">
        <h1 class="section-title" id="category-name">Templates</h1>
        <p class="section-subtitle" id="category-description"></p>
        <div id="templates-grid" class="grid grid-3">
            {{-- Populated via JS / FEAT-002 --}}
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/home.js"></script>
@endpush
