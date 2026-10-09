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

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// ===== PUBLIC ROUTES =====

Route::get('/', function () {
    $categories = \App\Models\Category::where('is_active', true)->orderBy('sort_order')->get();
    return view('home', compact('categories'));
})->name('home');

// Auth pages
Route::get('/signin', function () {
    return view('signin');
})->name('signin');

Route::post('/signout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('signout');

// Category / builder
Route::get('/category/{slug}', function ($slug) {
    $category = \App\Models\Category::where('slug', $slug)->where('is_active', true)->firstOrFail();
    $templates = $category->templates()->where('is_active', true)->orderBy('sort_order')->get();
    return view('category', compact('category', 'templates'));
})->name('category');

Route::get('/builder/{slug}', [DocumentController::class, 'show'])->name('document.show');

Route::get('/preview', function () {
    return view('preview');
})->name('preview');

Route::get('/preview-file/{token}', [DocumentController::class, 'previewFile'])
    ->name('document.preview-file');

// Payment pages
Route::get('/payment/{token}', [PaymentController::class, 'show'])->name('payment');

// Download page (after payment)
Route::get('/download/{token}', [DocumentController::class, 'downloadPage'])
    ->name('document.download-page');

// File download (PDF or Word)
Route::get('/download/{token}/{type}', [DocumentController::class, 'download'])
    ->where('type', 'pdf|word')
    ->name('document.download');

// Library
Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
Route::get('/library/search', [LibraryController::class, 'search'])->name('library.search');
Route::get('/library/{id}/{slug}', [LibraryController::class, 'show'])
    ->where('id', '[0-9]+')
    ->name('library.show');
Route::get('/library/{id}/download', [LibraryController::class, 'download'])
    ->where('id', '[0-9]+')
    ->name('library.download');

// CV Assistant
Route::get('/cv-assistant', [CvController::class, 'index'])->name('cv.assistant');

// Sitemap & robots
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [RobotsController::class, 'index'])->name('robots');

// ===== AUTHENTICATED ROUTES =====

Route::middleware('auth')->group(function () {
    // My documents
    Route::get('/my-documents', function () {
        $documents = \App\Models\GeneratedDocument::where('user_id', auth()->id())
            ->with('template')
            ->orderByDesc('created_at')
            ->paginate(10);
        return view('my-documents', compact('documents'));
    })->name('my-documents');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Account deletion
    Route::delete('/account', [\App\Http\Controllers\UserController::class, 'destroy'])->name('account.destroy');
});

// ===== ADMIN ROUTES =====

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
});

// ===== SAFE MIGRATION RUNNER FOR SHARED HOSTING =====
Route::get('/run-migrations', function () {
    $secret = request('secret');
    if ($secret !== 'kenyadocs2026' && (!auth()->check() || !auth()->user()->is_admin)) {
        abort(403, 'Unauthorized.');
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return response('<div style="font-family:sans-serif;padding:30px;"><h2>Migrations Output:</h2><pre>' . htmlspecialchars($output) . '</pre><p><a href="/">&larr; Back to Home</a></p></div>');
    } catch (\Throwable $e) {
        return response('<div style="font-family:sans-serif;padding:30px;color:red;"><h2>Migration Failed:</h2><p>' . htmlspecialchars($e->getMessage()) . '</p></div>', 500);
    }
});
