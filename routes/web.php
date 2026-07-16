<?php

use App\Http\Controllers\MediaController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\PortalClassroomController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalJoinController;
use App\Http\Controllers\PortalMessageController;
use App\Http\Controllers\PortalPostController;
use App\Http\Controllers\PortalReportCardController;
use App\Http\Controllers\PortalReportController;
use App\Http\Controllers\PortalUploadController;
use App\Http\Controllers\SitePageController;
use Illuminate\Support\Facades\Route;

// Public site — every page renders from the CMS (DB-driven). The controller
// renders the Blade view named by the slug, so the URL and the view can differ
// (/forms-policies -> forms).
Route::get('/', [SitePageController::class, 'show'])->name('home');
Route::get('/about', [SitePageController::class, 'show'])->defaults('slug', 'about')->name('about');
Route::get('/admissions', [SitePageController::class, 'show'])->defaults('slug', 'admissions')->name('admissions');
Route::get('/resources', [SitePageController::class, 'show'])->defaults('slug', 'resources')->name('resources');
Route::get('/gallery', [SitePageController::class, 'show'])->defaults('slug', 'gallery')->name('gallery');
Route::get('/forms-policies', [SitePageController::class, 'show'])->defaults('slug', 'forms')->name('forms');
Route::get('/faq', [SitePageController::class, 'show'])->defaults('slug', 'faq')->name('faq');
Route::get('/contact', [SitePageController::class, 'show'])->defaults('slug', 'contact')->name('contact');

// Admin — Inertia/React.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');

    // Parent/teacher-facing portal (distinct chrome from the admin CMS).
    // Access is per-room via ClassroomPolicy: staff run their room, a parent
    // reaches a room only through their own child.
    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [PortalController::class, 'home'])->name('home');

        // Ordinary uploads (photos in posts, chats, day logs). Lands in temp/
        // immediately; the receiving controller promotes it on submit.
        Route::post('uploads', [PortalUploadController::class, 'store'])->name('uploads.store');

        // Linking a parent to a child — redeeming the child's invite code is the
        // whole relationship system.
        Route::get('join', [PortalJoinController::class, 'show'])->name('join');
        Route::post('join', [PortalJoinController::class, 'store'])->name('join.store');

        // Classes are admin-only to create (enforced by ClassroomPolicy).
        Route::post('classes', [PortalClassroomController::class, 'store'])->name('classes.store');
        Route::patch('classes/{classroom}', [PortalClassroomController::class, 'update'])
            ->name('classes.update');
        Route::delete('classes/{classroom}', [PortalClassroomController::class, 'destroy'])
            ->name('classes.destroy');

        Route::prefix('classes/{classroom}')->name('classes.')->group(function () {
            Route::get('/', [PortalController::class, 'feed'])->name('feed');
            Route::get('students', [PortalController::class, 'students'])->name('students');
            Route::get('today', [PortalController::class, 'today'])->name('today');
            Route::get('chats/{conversation?}', [PortalController::class, 'chats'])->name('chats');

            Route::post('posts', [PortalPostController::class, 'store'])->name('posts.store');
            Route::delete('posts/{post}', [PortalPostController::class, 'destroy'])->name('posts.destroy');

            Route::post('chats/{conversation}/messages', [PortalMessageController::class, 'store'])
                ->name('messages.store');
        });

        // Report cards — the termly document. Staff-only writes.
        Route::prefix('children/{child}/report-cards')->name('report-cards.')->group(function () {
            Route::post('/', [PortalReportCardController::class, 'store'])->name('store');
            // Streamed through the app, never a public URL — a report card is
            // private to one family.
            Route::get('{card}/download', [PortalReportCardController::class, 'download'])
                ->name('download');
            Route::patch('{card}', [PortalReportCardController::class, 'update'])->name('update');
            Route::delete('{card}', [PortalReportCardController::class, 'destroy'])->name('destroy');
        });

        // Daily reports — staff-only writes (enforced in the controller).
        Route::prefix('children/{child}/report')->name('report.')->group(function () {
            Route::patch('/', [PortalReportController::class, 'update'])->name('update');
            Route::post('entries', [PortalReportController::class, 'addEntry'])->name('entries.store');
            Route::delete('entries/{entry}', [PortalReportController::class, 'removeEntry'])->name('entries.destroy');
            Route::post('publish', [PortalReportController::class, 'publish'])->name('publish');
            Route::delete('publish', [PortalReportController::class, 'unpublish'])->name('unpublish');
        });
    });

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
            Route::get('options/{source}', [PageBuilderController::class, 'options'])->name('options');
            Route::get('pages/{page}', [PageBuilderController::class, 'show'])->name('show');
            Route::put('pages/{page}', [PageBuilderController::class, 'save'])->name('save');
            Route::get('pages/{page}/preview', [PageBuilderController::class, 'preview'])->name('preview');
            Route::post('pages/{page}/render', [PageBuilderController::class, 'renderPage'])->name('render');
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
