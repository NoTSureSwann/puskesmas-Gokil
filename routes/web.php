<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Dokter\ResepController;
use Illuminate\Support\Facades\Route;

// --- PUBLIC ROUTES ---
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/offline', 'errors.offline')->name('offline');
Route::get('/api/obat/search', [ResepController::class, 'searchObat'])->name('api.obat.search')->middleware(['auth', 'throttle:30,1']);

Route::get('/register/{role}', [AuthController::class, 'showRegisterForm'])
    ->name('register')
    ->where('role', 'pasien|dokter|farmasi|admin');
Route::post('/register', [AuthController::class, 'register'])->name('register.submit')->middleware('throttle:99,30');

Route::get('/login/{role?}', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit')->middleware('throttle:5,1');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Email Verification Notices & Signed Verification Trigger
Route::get('/email/verify', [AuthController::class, 'showVerificationNotice'])->name('verification.notice');
Route::get('/email/verify/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->name('verification.verify');

// --- PASIEN ROUTES ---
Route::middleware(['auth', 'role:pasien', 'email.verified'])
    ->prefix('pasien')
    ->name('pasien.')
    ->group(base_path('routes/pasien.php'));

// --- DOKTER ROUTES ---
Route::middleware(['auth', 'role:dokter', 'email.verified'])
    ->prefix('dokter')
    ->name('dokter.')
    ->group(base_path('routes/dokter.php'));

// --- FARMASI ROUTES ---
Route::middleware(['auth', 'role:farmasi', 'email.verified'])
    ->prefix('farmasi')
    ->name('farmasi.')
    ->group(base_path('routes/farmasi.php'));

// --- ADMIN ROUTES ---
Route::middleware(['auth', 'role:admin', 'email.verified'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));
