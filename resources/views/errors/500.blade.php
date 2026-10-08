@extends('layouts.app')

@section('title', '500 - Server Error')

@section('content')
<section class="section">
    <div class="container" style="text-align:center;padding:5rem 1rem">
        <div style="font-size:5rem;margin-bottom:1rem">⚠️</div>
        <h1 style="font-size:2.5rem;margin-bottom:.5rem">500 - Server Error</h1>
        <p style="font-size:1.1rem;color:var(--text-muted);margin-bottom:2rem">
            Something went wrong on our end. Please try again in a moment.
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary">← Back to Home</a>
    </div>
</section>
@endsection
