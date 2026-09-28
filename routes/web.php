<?php

use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Admin\ProjectSettingController as AdminProjectSettingController;
use App\Http\Controllers\Admin\TaskController as AdminTaskController;
use App\Http\Controllers\ClientAccessController;
use App\Http\Controllers\ClientDashboardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes - Sistem Monitoring Task & Outstanding Apotek Keluarga
|--------------------------------------------------------------------------
*/

// =========================================================================
// 1. GERBANG AKSES KLIEN (PASSWORD GATEWAY)
// =========================================================================
Route::get('/access-gate', [ClientAccessController::class, 'showGate'])->name('client.gate');
Route::post('/access-gate', [ClientAccessController::class, 'verify'])->name('client.gate.verify');
Route::post('/access-gate/lock', [ClientAccessController::class, 'lock'])->name('client.gate.lock');

// =========================================================================
// 2. DASHBOARD MONITORING KLIEN (DILINDUNGI PASSWORD DATABASE)
// =========================================================================
Route::middleware(['client.auth'])->group(function () {
    Route::get('/', [ClientDashboardController::class, 'index'])->name('client.dashboard');
    Route::get('/export', [ClientDashboardController::class, 'export'])->name('client.export');
});

// =========================================================================
// 3. AUTENTIKASI ADMIN
// =========================================================================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

    // =====================================================================
    // 4. ADMIN TASK MANAGEMENT & PENGATURAN (DILINDUNGI AUTH ADMIN)
    // =====================================================================
    Route::middleware(['auth'])->group(function () {
        Route::get('/', function () {
            return redirect()->route('admin.tasks.index');
        });

        // CRUD Tasks
        Route::get('/tasks', [AdminTaskController::class, 'index'])->name('tasks.index');
        Route::post('/tasks', [AdminTaskController::class, 'store'])->name('tasks.store');
        Route::put('/tasks/{task}', [AdminTaskController::class, 'update'])->name('tasks.update');
        Route::delete('/tasks/{task}', [AdminTaskController::class, 'destroy'])->name('tasks.destroy');
        Route::patch('/tasks/{task}/quick-status', [AdminTaskController::class, 'quickStatus'])->name('tasks.quick-status');

        // Pengaturan Proyek & Password Akses Klien
        Route::get('/settings', [AdminProjectSettingController::class, 'index'])->name('settings.index');
        Route::put('/settings', [AdminProjectSettingController::class, 'update'])->name('settings.update');
    });
});
