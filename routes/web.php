<?php

use App\Http\Controllers\DocumentController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CvController;
use Illuminate\Support\Facades\Route;

// ============================================================
// PUBLIC ROUTES
// ============================================================

Route::get('/', function () {
    $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
    return view('home', compact('categories'));
})->name('home');

// Auth pages
Route::get('/signin', fn () => view('signin'))->name('signin');
Route::post('/signout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('signout');

// Category / builder
Route::get('/category/{slug}', function ($slug) {
    $category  = \App\Models\Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
    $templates = $category->templates()->where('is_active', true)->orderBy('sort_order')->get();
    return view('category', compact('category', 'templates'));
})->name('category');

Route::get('/builder/{slug}', [DocumentController::class, 'show'])->name('document.show');

// Preview
Route::get('/preview-file/{token}', [DocumentController::class, 'previewFile'])
    ->name('document.preview-file');

// Payment
Route::get('/payment/{token}', [PaymentController::class, 'show'])->name('payment');

// Download page (after payment)
Route::get('/download/{token}', [DocumentController::class, 'downloadPage'])
    ->name('document.download-page');

// File download — PDF or Word (H1: uses signed URL verification in controller)
Route::get('/download/{token}/{type}', [DocumentController::class, 'download'])
    ->where('type', 'pdf|word')
    ->name('document.download');

// ============================================================
// LIBRARY  (H2: download route registered BEFORE {slug} pattern)
// ============================================================
Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
Route::get('/library/search', [LibraryController::class, 'search'])->name('library.search');

// Download must come BEFORE the /{id}/{slug} pattern
Route::get('/library/{id}/file', [LibraryController::class, 'download'])
    ->where('id', '[0-9]+')
    ->name('library.download');

Route::get('/library/{id}/{slug}', [LibraryController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('library.show');

// CV Assistant
Route::get('/cv-assistant', [CvController::class, 'index'])->name('cv.assistant');

// Sitemap & robots
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

// ============================================================
// AUTHENTICATED ROUTES
// ============================================================

Route::middleware('auth')->group(function () {

    // My documents dashboard
    Route::get('/my-documents', function () {
        $documents = \App\Models\GeneratedDocument::where('user_id', auth()->id())
            ->with('template')
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('my-documents', compact('documents'));
    })->name('my-documents');

    // User draft CRUD (H1 + task #11)
    Route::delete('/api/drafts/{token}', [DocumentController::class, 'destroyDraft'])
        ->name('draft.destroy');
    Route::patch('/api/drafts/{token}/rename', [DocumentController::class, 'renameDraft'])
        ->name('draft.rename');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Account deletion
    Route::delete('/account', [\App\Http\Controllers\UserController::class, 'destroy'])->name('account.destroy');
});

// ============================================================
// ADMIN ROUTES
// ============================================================

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// ============================================================
// NOTE: The /run-migrations route has been removed (security: C2).
// Run migrations via CLI: php artisan migrate --force
// ============================================================
