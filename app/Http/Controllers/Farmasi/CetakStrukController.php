<?php

declare(strict_types=1);

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\LogCetak;
use App\Models\Resep;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Class CetakStrukController
 * Handles PDF receipt stub generation and printing logs on SQLite.
 */
class CetakStrukController extends Controller
{
    /**
     * Cetak struk resep / etiket obat dalam format PDF.
     * Mencatat riwayat pencetakan di database SQLite.
     */
    public function cetak(int $resepId): Response
    {
        $resep = Resep::with(['kunjungan.pasien.user', 'detailResep.obat', 'dokter.user'])->findOrFail($resepId);
        $user = Auth::user();

        // 1. Tentukan apakah ini cetakan ulang (reprint)
        $exists = LogCetak::query()->where('resep_id', $resep->id)->exists();
        $isReprint = $exists;

        // 2. Buat nama file PDF unik
        $cleanNoResep = str_replace('-', '_', $resep->no_resep);
        $filename = "resep_{$cleanNoResep}.pdf";
        $folderPath = "public/reseps";
        $fullPath = "{$folderPath}/{$filename}";

        // Buat folder jika belum ada
        if (!Storage::exists($folderPath)) {
            Storage::makeDirectory($folderPath);
        }

        // 3. Generate PDF menggunakan DomPDF
        $pdf = Pdf::loadView('farmasi.resep.struk-pdf', compact('resep', 'isReprint'));
        
        // Simpan PDF ke storage lokal
        Storage::put($fullPath, $pdf->output());

        // 4. Catat ke log_cetak (SQLite DB)
        LogCetak::create([
            'resep_id' => $resep->id,
            'farmasi_user_id' => $user->id,
            'no_resep' => $resep->no_resep,
            'nama_pasien' => $resep->kunjungan->pasien->user->name,
            'filename_pdf' => $filename,
            'path_pdf' => Storage::url("reseps/{$filename}"),
            'dicetak_pada' => now(),
            'is_reprint' => $isReprint,
        ]);

        // 5. Stream PDF ke browser
        return $pdf->stream($filename);
    }
}
