<?php

use App\Http\Controllers\StravaController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('strava')->name('strava.')->group(function () {
    Route::get('/', [StravaController::class, 'index'])->name('index');
    Route::get('/connect', [StravaController::class, 'connect'])->name('connect');
    Route::get('/callback', [StravaController::class, 'callback'])->name('callback');
    Route::delete('/disconnect', [StravaController::class, 'disconnect'])->name('disconnect');
    Route::post('/sync', [StravaController::class, 'sync'])->name('sync');
});
