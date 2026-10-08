@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('content')
{{-- FEAT-008: Admin dashboard — stats, templates, users, transactions --}}
<section class="section">
    <div class="container">
        <h1 class="section-title">Admin Dashboard</h1>

        <div class="grid grid-4 mb-4" id="stats-cards">
            {{-- Stats cards populated by admin.js --}}
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="card-title">Recent Transactions</h2>
                <div class="table-responsive" id="transactions-table">
                    {{-- Table populated by admin.js --}}
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <h2 class="card-title">Templates Management</h2>
                <a href="#" id="btn-new-template" class="btn btn-primary btn-sm mb-3">+ New Template</a>
                <div id="templates-table">
                    {{-- Table populated by admin.js --}}
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/admin.js"></script>
@endpush
