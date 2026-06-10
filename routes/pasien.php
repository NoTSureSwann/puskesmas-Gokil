<?php

declare(strict_types=1);

use App\Http\Controllers\Pasien\PasienDashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [PasienDashboardController::class, 'index'])->name('dashboard');

// Booking Kunjungan
Route::get('/daftar', [PasienDashboardController::class, 'showDaftarForm'])->name('daftar');
Route::post('/daftar', [PasienDashboardController::class, 'daftar'])->name('daftar.submit');

// Riwayat Kunjungan
Route::get('/riwayat', [PasienDashboardController::class, 'riwayat'])->name('riwayat');
Route::get('/kunjungan/{id}', [PasienDashboardController::class, 'showKunjungan'])->name('kunjungan');
Route::post('/kunjungan/{id}/batal', [PasienDashboardController::class, 'batalKunjungan'])->name('kunjungan.batal');

// Profile
Route::get('/profil', [PasienDashboardController::class, 'showProfil'])->name('profil');
Route::put('/profil', [PasienDashboardController::class, 'updateProfil'])->name('profil.update');

// Notifications
Route::post('/notifikasi/read', [PasienDashboardController::class, 'markNotificationsAsRead'])->name('notifikasi.read');
