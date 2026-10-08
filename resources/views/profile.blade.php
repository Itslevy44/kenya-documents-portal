@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
{{-- FEAT-007: User profile management --}}
<section class="section">
    <div class="container">
        <div class="card" style="max-width:560px;margin:0 auto">
            <div class="card-body">
                <h1 class="section-title">My Profile</h1>
                <form id="profile-form" novalidate>
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ auth()->user()->name ?? '' }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="id_number">ID Number</label>
                        <input type="text" id="id_number" name="id_number" class="form-control"
                               placeholder="National ID number" maxlength="10">
                    </div>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
