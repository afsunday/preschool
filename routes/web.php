<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\SitePageController;
use Illuminate\Support\Facades\Route;

// Public site — the homepage now renders from the CMS (DB-driven).
Route::get('/', [SitePageController::class, 'show'])->name('home');
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
            Route::get('/{medium}', [MediaController::class, 'show'])->name('show');
            Route::patch('/{medium}', [MediaController::class, 'update'])->name('update');
            Route::delete('/{medium}', [MediaController::class, 'destroy'])->name('destroy');
        });

        // Page builder JSON API (consumed by the React editor).
        Route::prefix('admin/builder')->name('builder.')->group(function () {
            Route::get('schema', [PageBuilderController::class, 'schema'])->name('schema');
            Route::post('render-section', [PageBuilderController::class, 'renderSection'])->name('render');
            Route::get('options/{source}', [PageBuilderController::class, 'options'])->name('options');
            Route::get('pages/{page}', [PageBuilderController::class, 'show'])->name('show');
            Route::put('pages/{page}', [PageBuilderController::class, 'save'])->name('save');
            Route::get('pages/{page}/preview', [PageBuilderController::class, 'preview'])->name('preview');
        });

        // Pages list + editor (Inertia).
        Route::get('admin/pages', [PageBuilderController::class, 'index'])->name('pages.index');
        Route::post('admin/pages', [PageBuilderController::class, 'store'])->name('pages.store');
        Route::post('admin/pages/pull', [PageBuilderController::class, 'pull'])->name('pages.pull');
        Route::get('admin/pages/{page}/edit', [PageBuilderController::class, 'edit'])->name('pages.edit');
        Route::delete('admin/pages/{page}', [PageBuilderController::class, 'destroy'])->name('pages.destroy');
    });
});

require __DIR__.'/settings.php';
