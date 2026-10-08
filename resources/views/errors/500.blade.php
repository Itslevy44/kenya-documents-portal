@extends('layouts.app')

@section('title', '500 — Server Error')

@section('content')
<section class="section text-center">
    <div class="container">
        <h1 class="section-title" style="font-size:4rem">500</h1>
        <p class="section-subtitle">Something went wrong on our end. Please try again shortly.</p>
        <a href="{{ route('home') }}" class="btn btn-primary mt-3">Go Home</a>
    </div>
</section>
@endsection
