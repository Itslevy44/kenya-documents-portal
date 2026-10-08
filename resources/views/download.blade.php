@extends('layouts.app')

@section('title', 'Download Document')

@section('content')
{{-- FEAT-005/006: Download confirmed document --}}
<section class="section">
    <div class="container text-center">
        <h1 class="section-title">Your Document is Ready</h1>
        <p class="section-subtitle">Payment confirmed. Download your document below.</p>
        <div class="card" style="max-width:480px;margin:2rem auto">
            <div class="card-body">
                <p id="doc-name" class="mb-3"></p>
                <a href="#" id="btn-download-pdf" class="btn btn-primary btn-block mb-2">
                    Download PDF
                </a>
                <a href="#" id="btn-download-docx" class="btn btn-outline btn-block">
                    Download Word (.docx)
                </a>
            </div>
        </div>
        <a href="{{ route('home') }}" class="btn btn-link mt-2">Back to Home</a>
    </div>
</section>
@endsection
