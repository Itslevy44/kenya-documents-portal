@extends('layouts.app')

@section('title', 'Preview Document')

@section('content')
{{-- FEAT-004: Document preview before payment --}}
<section class="section">
    <div class="container text-center">
        <h1 class="section-title">Document Preview</h1>
        <p class="section-subtitle">Review your document before proceeding to payment.</p>
        <div id="document-preview" class="card" style="max-width:700px;margin:0 auto">
            <div class="card-body">
                {{-- PDF/DOCX preview iframe or HTML render --}}
            </div>
        </div>
        <div class="mt-3 flex justify-center gap-2">
            <a href="#" id="btn-edit" class="btn btn-outline">Edit</a>
            <a href="#" id="btn-pay" class="btn btn-accent">Pay &amp; Download — KSh 50</a>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/builder.js"></script>
@endpush
