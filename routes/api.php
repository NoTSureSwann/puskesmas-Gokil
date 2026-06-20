<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Barcode Scanner API
Route::get('/kunjungan/{kode}', [\App\Http\Controllers\Api\ScannerApiController::class, 'cekAntrian']);
