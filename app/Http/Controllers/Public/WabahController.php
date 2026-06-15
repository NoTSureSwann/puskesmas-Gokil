<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\WabahGeospasial;
use Illuminate\Http\Request;

class WabahController extends Controller
{
    /**
     * Tampilkan antarmuka peta geospasial
     */
    public function index()
    {
        return view('wabah.peta');
    }

    /**
     * Kembalikan data spasial dalam format JSON (Dikonsumsi oleh Leaflet.js & AI Bot)
     */
    public function getApiData()
    {
        $outbreaks = WabahGeospasial::all();
        
        $today = \Carbon\Carbon::today();
        $totalPasienHariIni = \App\Models\Kunjungan::query()->whereDate('created_at', '=', $today, 'and')->count();
        $kasusTinggi = WabahGeospasial::query()->where('tingkat_bahaya', '=', 'Tinggi', 'and')->sum('kasus_aktif');

        $topDiseases = WabahGeospasial::query()->orderBy('kasus_aktif', 'desc')
            ->take(5)
            ->get(['nama_penyakit', 'kasus_aktif', 'tingkat_bahaya']);

        return response()->json([
            'success' => true,
            'message' => 'Data spasial berhasil diambil',
            'data' => [
                'outbreaks' => $outbreaks,
                'stats' => [
                    'kunjungan_hari_ini' => $totalPasienHariIni,
                    'kasus_tingkat_tinggi' => $kasusTinggi,
                    'total_klaster_wabah' => $outbreaks->count()
                ],
                'trends' => $topDiseases
            ],
            'timestamp' => \Carbon\Carbon::now()->format('Y-m-d H:i:s')
        ]);
    }
}
