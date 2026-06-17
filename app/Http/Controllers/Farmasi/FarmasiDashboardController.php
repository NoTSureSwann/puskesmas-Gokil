<?php

declare(strict_types=1);

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use App\Models\Resep;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Class FarmasiDashboardController
 * Handles the Pharmacist Kanban board overview.
 */
class FarmasiDashboardController extends Controller
{
    /**
     * Tampilkan halaman utama/Kanban board Farmasi.
     */
    public function index(): View
    {
        $today = Carbon::today();

        $totalReseps = Resep::query()->whereDate('created_at', $today)->count();
        $menungguCount = Resep::query()->whereDate('created_at', $today)->where('status', 'menunggu')->count();
        $diprosesCount = Resep::query()->whereDate('created_at', $today)->where('status', 'diproses')->count();
        $selesaiCount = Resep::query()->whereDate('created_at', $today)->where('status', 'selesai')->count();

        // 1. Resep Menunggu (Urgen paling atas)
        $resepsMenunggu = Resep::query()->whereDate('created_at', $today)
            ->where('status', 'menunggu')
            ->with(['kunjungan.pasien.user', 'detailResep'])
            ->orderBy('prioritas', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 2. Resep Diproses
        $resepsDiproses = Resep::query()->whereDate('created_at', $today)
            ->where('status', 'diproses')
            ->with(['kunjungan.pasien.user', 'detailResep'])
            ->orderBy('prioritas', 'desc')
            ->orderBy('created_at', 'asc')
            ->get();

        // 3. Resep Selesai
        $resepsSelesai = Resep::query()->whereDate('created_at', $today)
            ->where('status', 'selesai')
            ->with(['kunjungan.pasien.user', 'detailResep'])
            ->orderBy('updated_at', 'desc')
            ->get();

        // 4. Smart Pharmacy: Peringatan Stok & Tren AI
        $stokRendah = \App\Models\Obat::stokRendah()->get();
        $aiAlert = null;
        
        $datasets = \App\Models\AiDataset::where('created_at', '>=', Carbon::today()->subDays(7))->get();
        if ($datasets->isNotEmpty()) {
            $diseaseCounts = [];
            foreach ($datasets as $data) {
                $penyakits = $data->kemungkinan_penyakit ?? [];
                foreach ($penyakits as $penyakit) {
                    $diseaseCounts[$penyakit] = ($diseaseCounts[$penyakit] ?? 0) + 1;
                }
            }
            arsort($diseaseCounts);
            $topDisease = array_key_first($diseaseCounts);
            
            if ($topDisease && $diseaseCounts[$topDisease] >= 3) {
                $aiAlert = "Tren penyakit <strong>{$topDisease}</strong> meningkat dalam 7 hari terakhir ({$diseaseCounts[$topDisease]} kasus). Pastikan stok obat terkait aman.";
            }
        }

        return view('farmasi.dashboard', compact(
            'totalReseps', 'menungguCount', 'diprosesCount', 'selesaiCount',
            'resepsMenunggu', 'resepsDiproses', 'resepsSelesai', 'stokRendah', 'aiAlert'
        ));
    }
}
