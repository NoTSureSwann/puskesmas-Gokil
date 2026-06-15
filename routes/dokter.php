<?php

declare(strict_types=1);

use App\Http\Controllers\Dokter\DokterDashboardController;
use App\Http\Controllers\Dokter\ResepController;
use Illuminate\Support\Facades\Route;

// Dashboard & Profile
Route::get('/', [DokterDashboardController::class, 'index'])->name('dashboard');
Route::get('/profil', [DokterDashboardController::class, 'showProfil'])->name('profil');
Route::put('/profil', [DokterDashboardController::class, 'updateProfil'])->name('profil.update');

// Patient Queue Management
Route::post('/kunjungan/{id}/panggil', [DokterDashboardController::class, 'panggil'])->name('kunjungan.panggil');
Route::post('/kunjungan/{id}/periksa', [DokterDashboardController::class, 'periksa'])->name('kunjungan.periksa');
Route::get('/kunjungan/{id}/telemedisin', [DokterDashboardController::class, 'telemedisinRoom'])->name('kunjungan.telemedisin');

// Patient History Search
Route::get('/pasien/{nik}/riwayat', [DokterDashboardController::class, 'showPasienHistory'])->name('pasien.riwayat');

// Prescription (Resep) Management
Route::get('/resep/create/{kunjunganId}', [ResepController::class, 'create'])->name('resep.create');
Route::post('/resep', [ResepController::class, 'store'])->name('resep.store');
Route::get('/resep/{id}', [ResepController::class, 'show'])->name('resep.show');
Route::get('/riwayat-resep', [ResepController::class, 'riwayatResep'])->name('resep.index');

// ML Analytics & RLHF
Route::get('/ml-analytics', [\App\Http\Controllers\Dokter\MlAnalyticController::class, 'index'])->name('ml.analytics');
Route::get('/ml-analytics/realtime-data', [\App\Http\Controllers\Dokter\MlAnalyticController::class, 'realtimeData'])->name('ml.analytics.data');
Route::post('/ml-analytics/feedback/{id}', [\App\Http\Controllers\Dokter\MlAnalyticController::class, 'submitFeedback'])->name('ml.analytics.feedback');
