<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AiDataset;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EpidemiologiController extends Controller
{
    /**
     * Tampilkan dasbor Epidemiologi.
     */
    public function index(): View
    {
        $thirtyDaysAgo = Carbon::today()->subDays(30);
        
        // Mengambil tren penyakit berdasarkan kemungkinan_penyakit dari AiDataset
        // Karena kemungkinan_penyakit disimpan sebagai array JSON, kita perlu memprosesnya.
        $datasets = AiDataset::query()->where('created_at', '>=', $thirtyDaysAgo)->get();
        
        $diseaseCounts = [];
        $urgencyStats = [
            'Tinggi' => 0,
            'Sedang' => 0,
            'Rendah' => 0
        ];

        foreach ($datasets as $data) {
            $penyakits = $data->kemungkinan_penyakit ?? [];
            foreach ($penyakits as $penyakit) {
                if (!isset($diseaseCounts[$penyakit])) {
                    $diseaseCounts[$penyakit] = 0;
                }
                $diseaseCounts[$penyakit]++;
            }

            if (isset($urgencyStats[$data->tingkat_urgensi])) {
                $urgencyStats[$data->tingkat_urgensi]++;
            } else {
                $urgencyStats['Sedang']++; // Default
            }
        }

        // Sort penyakit by count
        arsort($diseaseCounts);
        $topDiseases = array_slice($diseaseCounts, 0, 10); // Top 10 diseases

        // Siapkan data untuk Chart.js
        $chartData = [
            'labels' => array_keys($topDiseases),
            'data' => array_values($topDiseases)
        ];

        return view('admin.epidemiologi', compact('chartData', 'urgencyStats', 'topDiseases', 'datasets'));
    }
}
