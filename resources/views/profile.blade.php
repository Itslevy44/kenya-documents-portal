@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<section class="section">
    <div class="container">
        <div class="card" style="max-width:560px;margin:0 auto">
            <div class="card-body">
                <h1 class="section-title">My Profile</h1>

                @if(session('success'))
                <div class="alert alert-success" style="background:#E8F5E9;border:1px solid #A5D6A7;padding:1rem;border-radius:var(--radius);margin-bottom:1rem;color:var(--success)">
                    {{ session('success') }}
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-error" style="background:#FFEBEE;border:1px solid #EF9A9A;padding:1rem;border-radius:var(--radius);margin-bottom:1rem;color:var(--error)">
                    <ul style="margin:0;padding-left:1.25rem">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('profile.update') }}" novalidate>
                    @csrf

                    <h3 style="margin-bottom:1rem;font-size:1rem;color:var(--text-muted)">Account</h3>

                    <div class="form-group">
                        <label class="form-label" for="name">Display Name</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email (optional)</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ old('email', $user->email) }}" maxlength="255">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="{{ old('phone', $user->phone) }}"
                               maxlength="10" inputmode="numeric" required>
                    </div>

                    <h3 style="margin:1.5rem 0 1rem;font-size:1rem;color:var(--text-muted)">Personal Details</h3>

                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Legal Name</label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               value="{{ old('full_name', $profile?->full_name) }}" required maxlength="255">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="id_number">National ID Number</label>
                        <input type="text" id="id_number" name="id_number" class="form-control"
                               value="{{ old('id_number', $profile?->id_number ?? '') }}"
                               placeholder="8-digit ID number" maxlength="20">
                        <span class="form-hint">Stored encrypted. Used to auto-fill document forms.</span>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="address">Address (optional)</label>
                        <input type="text" id="address" name="address" class="form-control"
                               value="{{ old('address', $profile?->address) }}" maxlength="500">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="city">City/Town (optional)</label>
                        <input type="text" id="city" name="city" class="form-control"
                               value="{{ old('city', $profile?->city) }}" maxlength="100">
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Save Changes</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
