@extends('layouts.app')

@section('title', '404 — Page Not Found')

@section('content')
<section class="section text-center">
    <div class="container">
        <h1 class="section-title" style="font-size:4rem">404</h1>
        <p class="section-subtitle">The page you are looking for could not be found.</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-3">Go Home</a>
    </div>
</section>
@endsection
