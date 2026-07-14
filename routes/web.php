<?php

use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

// Public site — Blade (server-rendered).
Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');
Route::view('/admissions', 'admissions')->name('admissions');
Route::view('/resources', 'resources')->name('resources');
Route::view('/gallery', 'gallery')->name('gallery');
Route::view('/forms-policies', 'forms')->name('forms');
Route::view('/faq', 'faq')->name('faq');
Route::view('/contact', 'contact')->name('contact');

// Admin — Inertia/React.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // CMS admin — content tools, guarded by the `cms` gate (user_type = admin).
    Route::middleware('can:cms')->group(function () {
        Route::inertia('admin/media', 'media/index')->name('media.index');

        // JSON media API (the picker opens in a dialog, so these must not be
        // Inertia responses). Kept off /admin/media to avoid the GET collision.
        Route::prefix('admin/media/items')->name('media.items.')->group(function () {
            Route::get('/', [MediaController::class, 'index'])->name('index');
            Route::post('/', [MediaController::class, 'store'])->name('store');
        });
    });
});

require __DIR__.'/settings.php';
