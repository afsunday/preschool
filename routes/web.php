<?php

use App\Http\Controllers\ContactSubmissionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialCategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageBuilderController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\PortalClassroomController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PortalJoinController;
use App\Http\Controllers\PortalMessageController;
use App\Http\Controllers\PortalPostController;
use App\Http\Controllers\PortalReportCardController;
use App\Http\Controllers\PortalReportController;
use App\Http\Controllers\PortalSettingsController;
use App\Http\Controllers\PortalUploadController;
use App\Http\Controllers\SitePageController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SitePageController::class, 'home'])->name('home');
Route::get('/about', [SitePageController::class, 'about'])->name('about');
Route::get('/admissions', [SitePageController::class, 'admissions'])->name('admissions');
Route::get('/resources', [SitePageController::class, 'resources'])->name('resources');
Route::get('/gallery', [SitePageController::class, 'gallery'])->name('gallery');
Route::get('/forms-policies', [SitePageController::class, 'forms'])->name('forms');
Route::get('/faq', [SitePageController::class, 'faq'])->name('faq');
Route::get('/contact', [SitePageController::class, 'contact'])->name('contact');
Route::post('/contact', [SitePageController::class, 'submitContact'])->name('contact.submit');
Route::post('/newsletter', [SitePageController::class, 'subscribeNewsletter'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{subscriber}', [NewsletterController::class, 'unsubscribe'])
    ->middleware('signed')->name('newsletter.unsubscribe');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::prefix('portal')->name('portal.')->group(function () {
        Route::get('/', [PortalController::class, 'home'])->name('home');
        Route::post('uploads', [PortalUploadController::class, 'store'])->name('uploads.store');
        Route::get('join', [PortalJoinController::class, 'show'])->name('join');
        Route::post('join', [PortalJoinController::class, 'store'])->name('join.store');

        // The portal's own profile screen (posts to the shared settings routes).
        Route::get('settings', [PortalSettingsController::class, 'edit'])->name('settings');

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

    // CMS admin — `can:cms` is the door (staff only); each area is then gated by
    // a specific permission. A super user passes every check (Gate::before).
    Route::middleware('can:cms')->group(function () {
        Route::middleware('permission:content.media')->group(function () {
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
        });

        Route::middleware('permission:content.pages')->group(function () {
            // Page builder JSON API (consumed by the React editor).
            Route::prefix('admin/builder')->name('builder.')->group(function () {
                Route::get('pages/{page}/schema', [PageBuilderController::class, 'schema'])->name('schema');
                Route::get('options/{source}', [PageBuilderController::class, 'options'])->name('options');
                Route::get('pages/{page}', [PageBuilderController::class, 'show'])->name('show');
                Route::put('pages/{page}', [PageBuilderController::class, 'save'])->name('save');
                Route::get('pages/{page}/preview', [PageBuilderController::class, 'preview'])->name('preview');
                Route::post('pages/{page}/render', [PageBuilderController::class, 'renderPage'])->name('render');
            });

            Route::get('admin/pages', [PageBuilderController::class, 'index'])->name('pages.index');
            Route::post('admin/pages', [PageBuilderController::class, 'store'])->name('pages.store');
            Route::post('admin/pages/pull', [PageBuilderController::class, 'pull'])->name('pages.pull');
            Route::get('admin/pages/{page}/edit', [PageBuilderController::class, 'edit'])->name('pages.edit');
            Route::delete('admin/pages/{page}', [PageBuilderController::class, 'destroy'])->name('pages.destroy');
        });

        // Resources library — the materials shown on the home teaser + resources page.
        Route::middleware('permission:content.resources')->group(function () {
            Route::get('admin/materials', [MaterialController::class, 'index'])->name('materials.index');
            Route::post('admin/materials', [MaterialController::class, 'store'])->name('materials.store');
            Route::put('admin/materials/{material}', [MaterialController::class, 'update'])->name('materials.update');
            Route::delete('admin/materials/{material}', [MaterialController::class, 'destroy'])->name('materials.destroy');

            Route::post('admin/material-categories', [MaterialCategoryController::class, 'store'])->name('material-categories.store');
            Route::put('admin/material-categories/{materialCategory}', [MaterialCategoryController::class, 'update'])->name('material-categories.update');
            Route::delete('admin/material-categories/{materialCategory}', [MaterialCategoryController::class, 'destroy'])->name('material-categories.destroy');
        });

        // Contact form inbox.
        Route::middleware('permission:comms.messages')->group(function () {
            Route::get('admin/messages', [ContactSubmissionController::class, 'index'])->name('messages.index');
            Route::patch('admin/messages/{submission}', [ContactSubmissionController::class, 'update'])->name('messages.update');
            Route::delete('admin/messages/{submission}', [ContactSubmissionController::class, 'destroy'])->name('messages.destroy');
        });

        // Newsletter: subscribers + compose/send.
        Route::middleware('permission:comms.newsletter')->group(function () {
            Route::get('admin/newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
            Route::post('admin/newsletter/send', [NewsletterController::class, 'send'])->name('newsletter.send');
            Route::get('admin/newsletter/campaigns/{campaign}/preview', [NewsletterController::class, 'preview'])->name('newsletter.preview');
            Route::delete('admin/newsletter/subscribers/{subscriber}', [NewsletterController::class, 'destroySubscriber'])->name('newsletter.subscribers.destroy');
        });

        // Team — teachers and back-office access in one place.
        Route::middleware('permission:team.staff')->group(function () {
            Route::get('admin/team', [TeamController::class, 'index'])->name('team.index');
            Route::post('admin/team', [TeamController::class, 'store'])->name('team.store');
            Route::put('admin/team/{user}', [TeamController::class, 'update'])->name('team.update');
            Route::delete('admin/team/{user}', [TeamController::class, 'destroy'])->name('team.destroy');
        });

        // Families directory — read-only list of parents and their children.
        Route::middleware('permission:parents.view')->group(function () {
            Route::get('admin/parents', [ParentController::class, 'index'])->name('parents.index');
        });
    });
});

require __DIR__.'/settings.php';
