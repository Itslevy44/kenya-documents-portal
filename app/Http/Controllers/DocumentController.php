<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Download;
use App\Models\GeneratedDocument;
use App\Models\Template;
use App\Services\DocumentService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class DocumentController extends Controller
{
    public function __construct(
        protected DocumentService $documentService,
        protected StorageService $storageService
    ) {}

    /**
     * Show document builder for a template
     */
    public function show(string $slug)
    {
        $template = Template::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $category = $template->category;
        
        return view('builder', compact('template', 'category'));
    }

    /**
     * Generate preview PDF from form data
     */
    public function preview(Request $request, string $slug)
    {
        $template = Template::where('slug', $slug)->where('is_active', true)->firstOrFail();
        
        $schema = $template->schema;
        $rules = $this->buildValidationRules($schema);
        $validated = $request->validate($rules);
        
        // Create GeneratedDocument
        $sessionToken = Str::random(32);
        
        $doc = GeneratedDocument::create([
            'user_id' => Auth::id(),
            'session_token' => $sessionToken,
            'template_id' => $template->id,
            'form_data' => $validated,
            'status' => 'draft',
        ]);
        
        // Store session token for unauthenticated users
        session(['doc_token_' . $sessionToken => true]);
        
        // Generate preview
        $this->storageService->ensureDocumentsDirectoryExists();
        $previewPath = $this->documentService->generatePreview($doc);
        
        return response()->json([
            'success' => true,
            'session_token' => $sessionToken,
            'preview_url' => route('document.preview-file', ['token' => $sessionToken]),
            'payment_url' => route('payment', ['token' => $sessionToken]),
            'template_name' => $template->name,
            'amount' => $template->price,
        ]);
    }

    /**
     * Stream preview PDF
     */
    public function previewFile(string $token)
    {
        $doc = $this->findDocumentOrFail($token);
        
        if (!$doc->preview_path || !$this->storageService->exists($doc->preview_path)) {
            abort(404, 'Preview not found');
        }
        
        return response()->file(
            $this->storageService->getFullPath($doc->preview_path),
            ['Content-Type' => 'application/pdf']
        );
    }

    /**
     * Trigger full document generation after payment (called by webhook)
     */
    public function generate(string $token)
    {
        $doc = GeneratedDocument::where('session_token', $token)->firstOrFail();
        
        if ($doc->status !== 'paid') {
            return response()->json(['success' => false, 'message' => 'Payment required.'], 402);
        }
        
        // Check edit window
        if ($doc->edit_count > 0) {
            if ($doc->edit_count >= 3) {
                return response()->json([
                    'success' => false,
                    'message' => 'Maximum edits (3) reached.',
                ], 422);
            }
            
            if ($doc->edit_window_expires_at && $doc->edit_window_expires_at->isPast()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Edit window expired (24 hours after payment).',
                ], 422);
            }
        }
        
        $this->storageService->ensureDocumentsDirectoryExists();
        $paths = $this->documentService->generateDocuments($doc);
        
        // Increment edit count on re-generation
        if ($doc->edit_count > 0) {
            $doc->increment('edit_count');
        }
        
        return response()->json([
            'success' => true,
            'pdf_url' => route('document.download', ['token' => $token, 'type' => 'pdf']),
            'word_url' => route('document.download', ['token' => $token, 'type' => 'word']),
        ]);
    }

    /**
     * Download final document
     */
    public function download(string $token, string $type)
    {
        $doc = $this->findDocumentOrFail($token);
        
        if ($doc->status !== 'paid') {
            abort(402, 'Payment required to download this document.');
        }
        
        if (!in_array($type, ['pdf', 'word'])) {
            abort(400, 'Invalid file type requested.');
        }
        
        $filePath = $type === 'pdf' ? $doc->pdf_path : $doc->word_path;
        
        if (!$filePath || !$this->storageService->exists($filePath)) {
            // Try to generate if not yet generated
            $this->storageService->ensureDocumentsDirectoryExists();
            $paths = $this->documentService->generateDocuments($doc);
            $filePath = $type === 'pdf' ? $paths['pdf'] : $paths['word'];
        }
        
        if (!$filePath || !$this->storageService->exists($filePath)) {
            abort(404, 'Document file not found.');
        }
        
        // Log download
        Download::create([
            'generated_document_id' => $doc->id,
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'file_type' => $type,
            'downloaded_at' => now(),
        ]);
        
        $downloadName = $doc->template->slug . '-' . substr($token, 0, 8) . '.' . ($type === 'word' ? 'docx' : 'pdf');
        
        return $this->storageService->streamDownload($filePath, $downloadName);
    }

    /**
     * Show download page (after payment success)
     */
    public function downloadPage(string $token)
    {
        $doc = $this->findDocumentOrFail($token);
        
        if ($doc->status !== 'paid') {
            return redirect()->route('payment', ['token' => $token])
                ->with('info', 'Please complete payment first.');
        }
        
        $template = $doc->template;
        
        return view('download', compact('doc', 'template'));
    }

    /**
     * Find a GeneratedDocument by token, checking auth or session ownership
     */
    protected function findDocumentOrFail(string $token): GeneratedDocument
    {
        $doc = GeneratedDocument::where('session_token', $token)->firstOrFail();
        
        // Allow if authenticated and is owner
        if (Auth::check() && $doc->user_id === Auth::id()) {
            return $doc;
        }
        
        // Allow if session token matches (unauthenticated)
        if (session('doc_token_' . $token)) {
            return $doc;
        }
        
        // Allow if admin
        if (Auth::check() && Auth::user()->is_admin) {
            return $doc;
        }
        
        abort(403, 'Access denied.');
    }

    // =========================================================================
    // User Draft CRUD
    // =========================================================================

    /**
     * Delete a draft document owned by the authenticated user
     */
    public function destroyDraft(string $token)
    {
        $doc = GeneratedDocument::where('session_token', $token)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if ($doc->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid documents cannot be deleted. Contact support if needed.',
            ], 422);
        }

        // Clean up files
        if ($doc->preview_path) {
            $this->storageService->delete($doc->preview_path);
        }
        if ($doc->pdf_path) {
            $this->storageService->delete($doc->pdf_path);
        }
        if ($doc->word_path) {
            $this->storageService->delete($doc->word_path);
        }

        $doc->delete();

        return response()->json(['success' => true, 'message' => 'Draft deleted successfully.']);
    }

    /**
     * Rename / update the label of a draft document
     */
    public function renameDraft(Request $request, string $token)
    {
        $request->validate([
            'label' => ['required', 'string', 'max:120'],
        ]);

        $doc = GeneratedDocument::where('session_token', $token)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $doc->update(['label' => $request->label]);

        return response()->json(['success' => true, 'message' => 'Document renamed.', 'label' => $request->label]);
    }

    /**
     * Build validation rules from template schema
     */
    protected function buildValidationRules(array $schema): array
    {
        $rules = [];
        $fields = $schema['fields'] ?? $schema;
        
        foreach ($fields as $field) {
            $name = $field['name'] ?? null;
            if (!$name) continue;
            
            $fieldRules = [];
            
            if (!empty($field['required'])) {
                $fieldRules[] = 'required';
            } else {
                $fieldRules[] = 'nullable';
            }
            
            $type = $field['type'] ?? 'text';
            switch ($type) {
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'phone':
                    $fieldRules[] = 'regex:/^07\d{8}$/';
                    break;
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:1000';
                    break;
            }
            
            $rules[$name] = $fieldRules;
        }
        
        return $rules;
    }
}
