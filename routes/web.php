<?php

use Illuminate\Support\Facades\Route;

// Public site — Blade (server-rendered).
Route::view('/', 'home')->name('home');
Route::view('/about', 'about')->name('about');

// Admin — Inertia/React.

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
