<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SeoLogController;
use App\Http\Controllers\SeoReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

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

    Route::resource('reports', ReportController::class);
    Route::get('reports/{report}/pdf', [ReportController::class, 'generatePdf'])->name('reports.pdf');

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-email', [SettingController::class, 'testEmail'])->name('settings.test-email');
    });

    // Backup routes
    Route::middleware(['auth', 'role:admin'])->group(function () {
        Route::post('/settings/backup/now', [BackupController::class, 'createBackup'])->name('settings.backup.now');
        Route::post('/settings/backup/test-ftp', [BackupController::class, 'testFtpConnection'])->name('settings.backup.test-ftp');
        Route::get('/settings/backup/list', [BackupController::class, 'listBackups'])->name('settings.backup.list');
        Route::get('/settings/backup/download/{filename}', [BackupController::class, 'downloadBackup'])->name('settings.backup.download');
        Route::delete('/settings/backup/{filename}', [BackupController::class, 'deleteBackup'])->name('settings.backup.delete');
        Route::get('/settings/google-drive/connect', [BackupController::class, 'connectGoogleDrive'])->name('settings.google-drive.connect');
        Route::get('/settings/google-drive/callback', [BackupController::class, 'handleGoogleDriveCallback'])->name('settings.google-drive.callback');
        Route::post('/settings/google-drive/disconnect', [BackupController::class, 'disconnectGoogleDrive'])->name('settings.google-drive.disconnect');
    });
});

require __DIR__.'/auth.php';
