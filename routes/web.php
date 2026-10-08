<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Routes are registered here and loaded by the RouteServiceProvider.
| All routes use web middleware (sessions, CSRF).
|
*/

Route::get('/', function () {
    return view('home');
})->name('home');

// Auth routes
Route::get('/signin', function () {
    return view('signin');
})->name('signin');

// Category / builder
Route::get('/category/{slug}', function ($slug) {
    return view('category', compact('slug'));
})->name('category');

Route::get('/builder/{slug}', function ($slug) {
    return view('builder', compact('slug'));
})->name('builder');

Route::get('/preview/{token}', function ($token) {
    return view('preview', compact('token'));
})->name('preview');

// Payment & download
Route::get('/payment/{token}', function ($token) {
    return view('payment', compact('token'));
})->name('payment');

Route::get('/download/{token}', function ($token) {
    return view('download', compact('token'));
})->name('download');

// Library
Route::get('/library', function () {
    return view('library.index');
})->name('library.index');

Route::get('/library/{slug}', function ($slug) {
    return view('library.show', compact('slug'));
})->name('library.show');

// User account
Route::middleware('auth')->group(function () {
    Route::get('/my-documents', function () {
        return view('my-documents');
    })->name('my-documents');

    Route::get('/profile', function () {
        return view('profile');
    })->name('profile');
});

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', function () {
        return view('admin.dashboard');
    })->name('admin.dashboard');
});
