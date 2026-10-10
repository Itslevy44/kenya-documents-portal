@extends('layouts.app')

@section('title', 'My Profile')

@section('content')

<div style="background:linear-gradient(160deg,#0a3d12 0%,#1B5E20 100%);padding:2.5rem 0;">
    <div class="container" style="text-align:center;">
        <div class="profile-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
        </div>
        <h1 style="font-size:1.75rem;font-weight:800;color:#fff;margin-bottom:.25rem;">My Profile</h1>
        <p style="color:rgba(255,255,255,.7);font-size:.9rem;">
            {{ auth()->user()->phone }} &nbsp;·&nbsp; Member since {{ auth()->user()->created_at->format('M Y') }}
        </p>
    </div>
</div>

<section class="section" style="background:var(--bg);">
<div class="container">
<div style="max-width:600px;margin:0 auto;">

    @if(session('success'))
    <div class="alert alert-success" style="margin-bottom:1.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M5 13l4 4L19 7"/></svg>
        {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert alert-error" style="margin-bottom:1.5rem;">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;"><path d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <ul style="margin:0;padding-left:1.25rem;">
            @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    <div class="card" style="border-radius:16px;">
        <div class="card-body" style="padding:clamp(1.5rem,4vw,2.25rem);">

            <form method="POST" action="{{ route('profile.update') }}" novalidate>
                @csrf

                {{-- Account section --}}
                <div class="form-section-title">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    Account Information
                </div>

                <div class="grid grid-2" style="gap:1.1rem;">
                    <div class="form-group">
                        <label class="form-label" for="name">Display Name *</label>
                        <input type="text" id="name" name="name" class="form-control"
                               value="{{ old('name', $user->name) }}" required maxlength="255">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" id="email" name="email" class="form-control"
                               value="{{ old('email', $user->email) }}" maxlength="255"
                               placeholder="name@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="phone">Phone Number *</label>
                        <input type="tel" id="phone" name="phone" class="form-control"
                               value="{{ old('phone', $user->phone) }}"
                               maxlength="10" inputmode="numeric" required
                               placeholder="07XXXXXXXX">
                        <span class="form-hint">Changing phone requires a new OTP on next login.</span>
                    </div>
                </div>

                {{-- Personal details --}}
                <div class="form-section-title" style="margin-top:1.75rem;">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                    Personal Details
                </div>

                <div class="grid grid-2" style="gap:1.1rem;">
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" for="full_name">Full Legal Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control"
                               value="{{ old('full_name', $profile?->full_name) }}"
                               required maxlength="255"
                               placeholder="As it appears on your ID">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="id_number">National ID Number</label>
                        <input type="text" id="id_number" name="id_number" class="form-control"
                               value="{{ old('id_number', $profile?->id_number ?? '') }}"
                               placeholder="8-digit ID" maxlength="20">
                        <span class="form-hint">🔒 Stored encrypted. Used to auto-fill document forms.</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="city">City / Town</label>
                        <input type="text" id="city" name="city" class="form-control"
                               value="{{ old('city', $profile?->city) }}" maxlength="100"
                               placeholder="e.g. Nairobi">
                    </div>
                    <div class="form-group" style="grid-column:1/-1;">
                        <label class="form-label" for="address">Address</label>
                        <input type="text" id="address" name="address" class="form-control"
                               value="{{ old('address', $profile?->address) }}" maxlength="500"
                               placeholder="Street address or P.O. Box">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:1.5rem;font-weight:700;">
                    Save Changes
                </button>
            </form>

        </div>
    </div>

    {{-- Quick links --}}
    <div class="card" style="border-radius:16px;margin-top:1.25rem;">
        <div class="card-body" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:1rem;">
            <div>
                <div style="font-weight:700;margin-bottom:.2rem;">My Documents</div>
                <div style="font-size:.85rem;color:var(--text-muted);">View all your generated documents</div>
            </div>
            <a href="{{ route('my-documents') }}" class="btn btn-outline btn-sm">View Documents →</a>
        </div>
    </div>

</div>
</div>
</section>

@endsection
