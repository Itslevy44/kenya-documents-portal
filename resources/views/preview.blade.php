@extends('layouts.app')

@section('title', 'Preview Document')

@section('content')
<section class="section">
    <div class="container">
        <h1 class="section-title">Document Preview</h1>
        <p class="section-subtitle">Review your document before proceeding to payment.</p>

        <div class="preview-container">
            <!-- PDF Preview -->
            <div class="card preview-card" id="pdf-preview-card">
                <div class="card-body" style="padding:0">
                    <iframe id="pdf-iframe" src="" title="Document preview"
                            style="width:100%;height:600px;border:none;display:none"
                            aria-label="Document preview PDF"></iframe>
                    <div id="preview-loading" style="text-align:center;padding:3rem">
                        <p>Loading preview…</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="preview-actions mt-3" style="display:flex;gap:1rem;justify-content:center;flex-wrap:wrap">
                <a href="#" id="btn-edit" class="btn btn-outline">
                    ← Edit Document
                </a>
                <a href="#" id="btn-pay" class="btn btn-accent btn-lg">
                    Pay &amp; Download — KSh <span id="doc-price">—</span>
                </a>
            </div>
        </div>

        <!-- Document info -->
        <div id="doc-info" class="card mt-3" style="max-width:700px;margin:1rem auto 0">
            <div class="card-body">
                <p><strong>Template:</strong> <span id="doc-template-name">—</span></p>
                <p><strong>Token:</strong> <code id="doc-token">—</code></p>
                <p style="font-size:.85rem;color:var(--text-muted)">Save your token to retrieve this document later.</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('scripts')
<script>
(function () {
    // Data passed via URL query or sessionStorage from builder.js
    const params = new URLSearchParams(window.location.search);
    const token = params.get('token') || sessionStorage.getItem('doc_token');
    const previewUrl = params.get('preview_url') || sessionStorage.getItem('doc_preview_url');
    const paymentUrl = params.get('payment_url') || sessionStorage.getItem('doc_payment_url');
    const templateName = sessionStorage.getItem('doc_template_name');
    const price = sessionStorage.getItem('doc_price');
    const editUrl = sessionStorage.getItem('doc_edit_url');

    if (token) {
        document.getElementById('doc-token').textContent = token;
        sessionStorage.setItem('doc_token', token);
    }
    if (templateName) {
        document.getElementById('doc-template-name').textContent = templateName;
    }
    if (price) {
        document.getElementById('doc-price').textContent = Number(price).toLocaleString();
    }
    if (editUrl) {
        document.getElementById('btn-edit').href = editUrl;
    }
    if (paymentUrl) {
        document.getElementById('btn-pay').href = paymentUrl;
    }

    // Load preview PDF
    if (previewUrl) {
        const iframe = document.getElementById('pdf-iframe');
        const loading = document.getElementById('preview-loading');
        iframe.onload = function () {
            loading.style.display = 'none';
            iframe.style.display = 'block';
        };
        iframe.src = previewUrl;
    } else {
        document.getElementById('preview-loading').textContent = 'No preview available.';
    }
}());
</script>
@endpush
