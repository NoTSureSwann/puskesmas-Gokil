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

        return view('farmasi.dashboard', compact(
            'totalReseps', 'menungguCount', 'diprosesCount', 'selesaiCount',
            'resepsMenunggu', 'resepsDiproses', 'resepsSelesai'
        ));
    }
}
