<?php

namespace App\Http\Controllers;

use App\Models\MissingDocumentRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MissingDocumentController extends Controller
{
    /**
     * Store a missing document request from a user
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'search_query'         => ['required', 'string', 'max:255'],
            'document_description' => ['nullable', 'string', 'max:1000'],
            'phone'                => ['nullable', 'regex:/^07\d{8}$/'],
        ]);

        MissingDocumentRequest::create([
            'user_id'              => Auth::id(),
            'search_query'         => $validated['search_query'],
            'document_description' => $validated['document_description'] ?? null,
            'phone'                => $validated['phone'] ?? (Auth::check() ? Auth::user()->phone : null),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Thank you! We have noted your request and will work on adding this document.',
        ]);
    }

    /**
     * Admin: List all missing document requests grouped by description
     */
    public function index()
    {
        $requests = MissingDocumentRequest::selectRaw('
                document_description,
                search_query,
                COUNT(*) as request_count,
                MAX(created_at) as last_requested
            ')
            ->groupBy('document_description', 'search_query')
            ->orderByDesc('request_count')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => $requests,
        ]);
    }
}
