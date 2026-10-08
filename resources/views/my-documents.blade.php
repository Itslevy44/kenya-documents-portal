@extends('layouts.app')

@section('title', 'My Documents')

@section('content')
{{-- FEAT-006: Authenticated user document history --}}
<section class="section">
    <div class="container">
        <h1 class="section-title">My Documents</h1>
        <p class="section-subtitle">All documents you have generated and paid for.</p>
        <div id="my-docs-list">
            {{-- Populated by JS or server-side in FEAT-006 --}}
        </div>
    </div>
</section>
@endsection
