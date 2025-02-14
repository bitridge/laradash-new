<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoLogController;
use App\Http\Controllers\SeoReportController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Routes accessible by admin only
    Route::middleware('role:admin')->group(function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('users', UserController::class)->except(['show']);
    });

    // Routes accessible by admin and seo_provider
    Route::middleware('role:admin,seo_provider')->group(function () {
        Route::resource('projects', ProjectController::class);
        Route::get('customers/{customer}/projects', [ProjectController::class, 'customerProjects'])
            ->name('customers.projects');
        
        // SEO specific routes
        Route::resource('seo-logs', SeoLogController::class);
        Route::resource('seo-reports', SeoReportController::class);
    });

    // Profile routes accessible by all authenticated users
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
