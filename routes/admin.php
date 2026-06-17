<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\AdminDashboardController;
use Illuminate\Support\Facades\Route;

// Main Dashboard Summary
Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

// Laporan
Route::get('/laporan', [\App\Http\Controllers\Admin\LaporanController::class, 'index'])->name('laporan.index');

// Ambulans
Route::get('/ambulans', [\App\Http\Controllers\Admin\AdminAmbulansController::class, 'index'])->name('ambulans.index');
Route::post('/ambulans/{id}/status', [\App\Http\Controllers\Admin\AdminAmbulansController::class, 'updateStatus'])->name('ambulans.status');

// User Management CRUD
Route::prefix('users')->name('users.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'usersIndex'])->name('index');
    Route::get('/create', [AdminDashboardController::class, 'usersCreate'])->name('create');
    Route::post('/', [AdminDashboardController::class, 'usersStore'])->name('store');
    Route::get('/{id}/edit', [AdminDashboardController::class, 'usersEdit'])->name('edit');
    Route::put('/{id}', [AdminDashboardController::class, 'usersUpdate'])->name('update');
    Route::post('/{id}/toggle', [AdminDashboardController::class, 'usersToggle'])->name('toggle');
    Route::delete('/{id}', [AdminDashboardController::class, 'usersDestroy'])->name('destroy');
});

// Clinic Management CRUD
Route::prefix('poli')->name('poli.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'poliIndex'])->name('index');
    Route::post('/', [AdminDashboardController::class, 'poliStore'])->name('store');
    Route::put('/{id}', [AdminDashboardController::class, 'poliUpdate'])->name('update');
    Route::post('/{id}/toggle', [AdminDashboardController::class, 'poliToggle'])->name('toggle');
    Route::delete('/{id}', [AdminDashboardController::class, 'poliDestroy'])->name('destroy');
});

// Medicine Inventory Management CRUD
Route::prefix('obat')->name('obat.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'obatIndex'])->name('index');
    Route::post('/', [AdminDashboardController::class, 'obatStore'])->name('store');
    Route::put('/{id}', [AdminDashboardController::class, 'obatUpdate'])->name('update');
    Route::post('/{id}/toggle', [AdminDashboardController::class, 'obatToggle'])->name('toggle');
    Route::delete('/{id}', [AdminDashboardController::class, 'obatDestroy'])->name('destroy');
});

// Reports & Logs
Route::prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/kunjungan', [AdminDashboardController::class, 'laporanKunjungan'])->name('kunjungan');
    Route::get('/cetak', [AdminDashboardController::class, 'laporanCetak'])->name('cetak');
    Route::get('/ai-dataset', [AdminDashboardController::class, 'laporanAiDataset'])->name('ai_dataset');
    Route::get('/ai-dataset/export/{format}', [AdminDashboardController::class, 'exportAiDataset'])->name('ai_dataset.export');
});
