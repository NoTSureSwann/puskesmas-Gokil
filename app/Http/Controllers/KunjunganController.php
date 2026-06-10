<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIEngineService;
use Carbon\Carbon;

class KunjunganController extends Controller
{
    protected $aiEngine;

    public function __construct(AIEngineService $aiEngine)
    {
        $this->aiEngine = $aiEngine;
    }

    /**
     * Contoh fungsi untuk mendaftarkan kunjungan pasien baru
     * dan menggunakan AI Engine untuk memberikan insight/prediksi.
     */
    public function store(Request $request)
    {
        // Validasi input (Boilerplate)
        $validated = $request->validate([
            'poli_id' => 'required|integer',
            'keluhan' => 'required|string',
        ]);

        $now = Carbon::now();
        $dayOfWeek = $now->dayOfWeek; // 0 = Sunday, 1 = Monday
        $hour = $now->hour;

        // 1. Prediksi Lonjakan Antrian (ML)
        $surgePrediction = $this->aiEngine->predictSurge($validated['poli_id'], $dayOfWeek, $hour);

        // 2. Analisis Keluhan Teks (DL)
        $complaintAnalysis = $this->aiEngine->predictComplaint($validated['keluhan']);

        // 3. Rekomendasi Optimasi Antrian (RL)
        // Dummy queue length untuk contoh
        $currentQueueLength = 15; 
        $queueOptimization = $this->aiEngine->optimizeQueue($currentQueueLength);

        // Simulasi simpan data ke database
        // Kunjungan::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran pasien berhasil.',
            'ai_insights' => [
                'surge_prediction' => $surgePrediction,
                'complaint_analysis' => $complaintAnalysis,
                'queue_optimization' => $queueOptimization,
            ]
        ]);
    }
}
