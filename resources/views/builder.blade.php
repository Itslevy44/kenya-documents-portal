@extends('layouts.app')

@section('title', 'Build Document')

@section('content')
{{-- FEAT-004: Dynamic form builder driven by template JSON schema --}}
<section class="section">
    <div class="container">
        <h1 class="section-title" id="template-name">Build Your Document</h1>
        <div class="flex gap-2" style="flex-wrap:wrap;align-items:flex-start">
            <div id="builder-form-wrap" style="flex:1;min-width:280px">
                <form id="builder-form" novalidate>
                    @csrf
                    <div id="form-fields">
                        {{-- Fields injected by builder.js --}}
                    </div>
                    <button type="submit" class="btn btn-primary btn-block mt-3" id="btn-build">
                        Generate Document
                    </button>
                </form>
            </div>
            <div id="preview-panel" style="flex:1;min-width:280px">
                {{-- Live preview populated by builder.js --}}
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script src="/js/builder.js"></script>
@endpush
