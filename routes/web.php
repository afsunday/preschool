<?php

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
});

require __DIR__.'/settings.php';
