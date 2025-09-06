<?php

use App\Http\Controllers\UIController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [UIController::class, 'index'])->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
