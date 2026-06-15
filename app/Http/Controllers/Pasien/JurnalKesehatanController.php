<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\ViewModels\JurnalKesehatanViewModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JurnalKesehatanController extends Controller
{
    /**
     * Unduh Jurnal Kesehatan pasien dalam format PDF.
     */
    public function download(string|int $id)
    {
        $pasienId = Auth::user()->pasien->id;

        // Keamanan: Pastikan kunjungan benar-benar milik pasien ini
        $kunjungan = Kunjungan::query()
            ->where('pasien_id', $pasienId)
            ->findOrFail($id);

        // Hanya bisa diunduh jika status sudah selesai/resep/lunas (sudah diperiksa)
        if (!in_array($kunjungan->status, ['diperiksa', 'resep', 'selesai'])) {
            return back()->with('error', 'Jurnal kesehatan belum tersedia. Kunjungan ini belum diperiksa oleh dokter.');
        }

        // Terapkan pola MVVM: Serahkan seluruh pengolahan presentasi ke ViewModel
        $viewModel = new JurnalKesehatanViewModel($kunjungan);
        $data = $viewModel->toArray();

        // Render PDF (menggunakan view khusus PDF)
        $pdf = Pdf::loadView('pasien.pdf.jurnal', $data);
        
        // Atur ukuran kertas
        $pdf->setPaper('A4', 'portrait');

        // Nama file PDF
        $fileName = 'Jurnal_Kesehatan_' . str_replace(' ', '_', $data['identitas_pasien']['nama_lengkap']) . '_' . $kunjungan->no_kunjungan . '.pdf';

        return $pdf->download($fileName);
    }
}
