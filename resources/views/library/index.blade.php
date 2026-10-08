@extends('layouts.app')

@section('title', 'Document Library')

@section('content')
{{-- FEAT-003: Library with search and category filters --}}
<section class="section">
    <div class="container">
        <h1 class="section-title">Document Library</h1>
        <p class="section-subtitle">Browse all available Kenyan document templates.</p>

        <div class="flex gap-2 mb-4" style="flex-wrap:wrap">
            <input type="search" id="library-search" class="form-control" style="max-width:320px"
                   placeholder="Search templates…" aria-label="Search templates">
            <select id="category-filter" class="form-control" style="max-width:200px" aria-label="Filter by category">
                <option value="">All Categories</option>
            </select>
        </div>

        <div id="library-grid" class="grid grid-3">
            {{-- Populated via JS / FEAT-003 --}}
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/home.js"></script>
@endpush
