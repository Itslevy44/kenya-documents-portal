@extends('layouts.app')

@section('title', '404 - Page Not Found')

@section('content')
<section class="section">
    <div class="container" style="text-align:center;padding:5rem 1rem">
        <div style="font-size:5rem;margin-bottom:1rem">🤔</div>
        <h1 style="font-size:2.5rem;margin-bottom:.5rem">404 - Page Not Found</h1>
        <p style="font-size:1.1rem;color:var(--text-muted);margin-bottom:2rem">
            The page you are looking for does not exist.
        </p>
        <a href="{{ route('home') }}" class="btn btn-primary">← Back to Home</a>
    </div>
</section>
@endsection
