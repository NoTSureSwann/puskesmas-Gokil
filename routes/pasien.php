<?php

declare(strict_types=1);

use App\Http\Controllers\Pasien\PasienDashboardController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [PasienDashboardController::class, 'index'])->name('dashboard');

// Booking Kunjungan
Route::get('/daftar', [PasienDashboardController::class, 'showDaftarForm'])->name('daftar');
Route::post('/daftar', [PasienDashboardController::class, 'daftar'])->name('daftar.submit');
Route::post('/analyze-symptoms', [PasienDashboardController::class, 'analyzeSymptoms'])->name('analyze');

// Riwayat Kunjungan
Route::get('/riwayat', [PasienDashboardController::class, 'riwayat'])->name('riwayat');
Route::get('/kunjungan/{id}', [PasienDashboardController::class, 'showKunjungan'])->name('kunjungan');
Route::get('/kunjungan/{id}/telemedisin', [PasienDashboardController::class, 'telemedisinRoom'])->name('kunjungan.telemedisin');
Route::get('/jurnal-kesehatan/{id}/download', [\App\Http\Controllers\Pasien\JurnalKesehatanController::class, 'download'])->name('jurnal.download');

// Tagihan & Pembayaran
Route::get('/tagihan', [\App\Http\Controllers\Pasien\TagihanController::class, 'index'])->name('tagihan.index');
Route::get('/tagihan/{id}', [\App\Http\Controllers\Pasien\TagihanController::class, 'show'])->name('tagihan.show');
Route::post('/tagihan/{id}/pay', [\App\Http\Controllers\Pasien\TagihanController::class, 'simulatePayment'])->name('tagihan.pay');

Route::post('/kunjungan/{id}/batal', [PasienDashboardController::class, 'batalKunjungan'])->name('kunjungan.batal');

// Profile
Route::get('/profil', [PasienDashboardController::class, 'showProfil'])->name('profil');
Route::put('/profil', [PasienDashboardController::class, 'updateProfil'])->name('profil.update');

// Notifications
Route::post('/notifikasi/read', [PasienDashboardController::class, 'markNotificationsAsRead'])->name('notifikasi.read');

// Stunting Calculator
Route::get('/stunting', [\App\Http\Controllers\Pasien\StuntingController::class, 'index'])->name('stunting');
Route::post('/stunting/calculate', [\App\Http\Controllers\Pasien\StuntingController::class, 'calculate'])->name('stunting.calculate');

// Ambulans
Route::post('/ambulans/call', [\App\Http\Controllers\Pasien\AmbulansController::class, 'call'])->name('ambulans.call');
