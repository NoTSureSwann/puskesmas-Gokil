<?php

declare(strict_types=1);

use App\Http\Controllers\Farmasi\FarmasiDashboardController;
use App\Http\Controllers\Farmasi\ResepFarmasiController;
use App\Http\Controllers\Farmasi\CetakStrukController;
use Illuminate\Support\Facades\Route;

// Dashboard Kanban Board
Route::get('/', [FarmasiDashboardController::class, 'index'])->name('dashboard');

// Prescription Processing Flows
Route::get('/resep/{id}/proses', [ResepFarmasiController::class, 'showProcessForm'])->name('resep.showProcess');
Route::post('/resep/{id}/start', [ResepFarmasiController::class, 'process'])->name('resep.start');
Route::post('/resep/{id}/selesai', [ResepFarmasiController::class, 'selesai'])->name('resep.selesai');

// PDF Printing
Route::get('/resep/{id}/cetak', [CetakStrukController::class, 'cetak'])->name('resep.cetak');
