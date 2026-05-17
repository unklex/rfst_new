<?php

use App\Http\Controllers\Admin\MagicLinkController;
use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Admin magic-link login. Defined explicitly under /admin/magic-link/* so
// Filament's panel routes (registered at /admin) don't shadow them — Laravel
// resolves more-specific matches first, and these names don't collide.
Route::prefix('admin/magic-link')->name('admin.magic-link.')->group(function () {
    Route::get('/', [MagicLinkController::class, 'showRequestForm'])->name('request');
    Route::post('/', [MagicLinkController::class, 'sendLink'])
        ->middleware('throttle:5,1')
        ->name('send');
    Route::get('/sent', [MagicLinkController::class, 'showSent'])->name('sent');
    Route::get('/consume/{token}', [MagicLinkController::class, 'consume'])
        ->where('token', '[A-Za-z0-9]{64}')
        ->middleware('throttle:10,1')
        ->name('consume');
});
