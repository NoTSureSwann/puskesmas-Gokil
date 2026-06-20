<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ScannerApiController extends Controller
{
    /**
     * Mengecek status antrian dan triase berdasarkan kode kunjungan (barcode).
     *
     * @param string $kode
     * @return JsonResponse
     */
    public function cekAntrian(string $kode): JsonResponse
    {
        // Cari data kunjungan beserta relasi pasien dan poli
        $kunjungan = Kunjungan::with(['pasien.user', 'poli'])->where('no_kunjungan', $kode)->first();

        if (!$kunjungan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Data kunjungan tidak ditemukan. Pastikan kode barcode benar.'
            ], 404);
        }

        // Tentukan pesan analisis AI (Triase) dari keluhan atau data lain
        $aiAnalysis = 'Pasien belum melakukan skrining AI (KBot).';
        if (!empty($kunjungan->keluhan)) {
            $aiAnalysis = "Keluhan Utama: " . $kunjungan->keluhan;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'no_kunjungan' => $kunjungan->no_kunjungan,
                'no_antrian' => str_pad((string)$kunjungan->no_antrian, 3, '0', STR_PAD_LEFT),
                'nama_pasien' => $kunjungan->pasien->user->name ?? 'Anonim',
                'poli' => $kunjungan->poli->nama_poli ?? 'Belum Ditentukan',
                'status_kunjungan' => ucfirst($kunjungan->status),
                'ai_analysis' => $aiAnalysis,
            ]
        ]);
    }
}
