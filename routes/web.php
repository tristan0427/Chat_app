<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\UIController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [UIController::class, 'index'])->name('home');

//Route::get('dashboard', function () {
//    return Inertia::render('Dashboard');
//})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/chat', [ChatController::class, 'index'])->name('chat.index');

    // AJAX endpoints (JSON)
    Route::get('/chat/users', [ChatController::class, 'users'])->name('chat.users');
    Route::get('/chat/messages', [ChatController::class, 'messages'])->name('chat.messages');
    Route::post('/chat/messages', [ChatController::class, 'store'])->name('chat.messages.store');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
