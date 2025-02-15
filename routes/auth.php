<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Middleware\MathCaptcha;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->middleware(MathCaptcha::class)
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store'])
        ->middleware(MathCaptcha::class);
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
