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
Route::post('/resep/{id}/item', [ResepFarmasiController::class, 'addResepItem'])->name('resep.item.add');
Route::put('/resep/{id}/item/{detailId}', [ResepFarmasiController::class, 'updateResepItem'])->name('resep.item.update');
Route::delete('/resep/{id}/item/{detailId}', [ResepFarmasiController::class, 'deleteResepItem'])->name('resep.item.delete');
Route::post('/resep/{id}/selesai', [ResepFarmasiController::class, 'selesai'])->name('resep.selesai');
Route::post('/resep/{id}/cek-interaksi', [ResepFarmasiController::class, 'cekInteraksi'])->name('resep.cekInteraksi');

// PDF Printing
Route::get('/resep/{id}/cetak', [CetakStrukController::class, 'cetak'])->name('resep.cetak');

// Manajemen Master Obat (Farmasi)
Route::resource('obat', \App\Http\Controllers\Farmasi\ObatFarmasiController::class)->except(['create', 'edit', 'show']);
Route::post('obat/bulk-destroy', [\App\Http\Controllers\Farmasi\ObatFarmasiController::class, 'bulkDestroy'])->name('obat.bulk-destroy');
Route::post('obat/{id}/toggle', [\App\Http\Controllers\Farmasi\ObatFarmasiController::class, 'toggle'])->name('obat.toggle');
