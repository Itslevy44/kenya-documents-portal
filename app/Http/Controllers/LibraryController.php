<?php

namespace App\Http\Controllers;

use App\Models\LibraryItem;
use App\Models\MissingDocumentRequest;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class LibraryController extends Controller
{
    public function __construct(protected TelegramService $telegramService)
    {}

    /**
     * Paginated library listing with optional category filter, cached 1 hour
     */
    public function index(Request $request)
    {
        $category = $request->query('category');
        $cacheKey = 'library.index.' . ($category ?? 'all') . '.' . $request->query('page', 1);

        $items = Cache::remember($cacheKey, 3600, function () use ($category) {
            $query = LibraryItem::where('is_active', true)->orderByDesc('download_count');
            if ($category) {
                $query->where('category', $category);
            }
            return $query->paginate(12);
        });

        $categories = Cache::remember('library.categories', 3600, function () {
            return LibraryItem::where('is_active', true)
                ->whereNotNull('category')
                ->distinct()
                ->orderBy('category')
                ->pluck('category');
        });

        return view('library.index', compact('items', 'categories', 'category'));
    }

    /**
     * Single library item detail view
     */
    public function show(int $id, string $slug)
    {
        $item = LibraryItem::where('id', $id)->where('is_active', true)->firstOrFail();

        return view('library.show', compact('item'));
    }

    /**
     * Download a library item
     */
    public function download(int $id)
    {
        $item = LibraryItem::where('id', $id)->where('is_active', true)->firstOrFail();

        // Free items: stream directly
        // Paid items: check if user has an associated payment
        if (!$item->is_free) {
            if (!Auth::check()) {
                return redirect()->route('signin')
                    ->with('error', 'Sign in to download this document.');
            }
            // For non-free items, authentication alone grants access
            // (A separate payment flow for library items could be added later)
        }

        // Increment download count
        $item->increment('download_count');

        // Stream from Telegram
        try {
            return $this->telegramService->streamFile($item->telegram_file_id);
        } catch (\Exception $e) {
            abort(503, 'File temporarily unavailable. Please try again later.');
        }
    }

    /**
     * FULLTEXT search on library items
     */
    public function search(Request $request)
    {
        $request->validate([
            'q' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $query   = $request->input('q');
        $results = null;

        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql') {
            try {
                $cleanQuery = preg_replace('/[+\-><()~*\"@]+/', ' ', $query);
                $cleanQuery = trim($cleanQuery);
                if (!empty($cleanQuery)) {
                    $results = LibraryItem::where('is_active', true)
                        ->whereRaw('MATCH(title, description) AGAINST(? IN BOOLEAN MODE)', [$cleanQuery . '*'])
                        ->orderByDesc('download_count')
                        ->paginate(12);
                }
            } catch (\Exception $e) {
                $results = null;
            }
        }

        // If no fulltext results, fall back to LIKE search
        if (!$results || $results->isEmpty()) {
            $escaped = str_replace(['%', '_'], ['\\%', '\\_'], $query);
            $results = LibraryItem::where('is_active', true)
                ->where(function ($q) use ($escaped) {
                    $q->where('title', 'LIKE', '%' . $escaped . '%')
                      ->orWhere('description', 'LIKE', '%' . $escaped . '%');
                })
                ->orderByDesc('download_count')
                ->paginate(12);
        }

        // Log missing document request if no results
        if ($results->isEmpty()) {
            MissingDocumentRequest::create([
                'user_id'      => Auth::id(),
                'search_query' => $query,
                'phone'        => Auth::check() ? Auth::user()->phone : null,
            ]);
        }

        return view('library.index', [
            'items'      => $results,
            'categories' => collect(),
            'category'   => null,
            'search'     => $query,
        ]);
    }
}
