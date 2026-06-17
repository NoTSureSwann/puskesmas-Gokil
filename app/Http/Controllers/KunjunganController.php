<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIEngineService;
use App\Models\AiDataset;
use Carbon\Carbon;

class KunjunganController extends Controller
{
    protected AIEngineService $aiEngine;

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

        // 2. Analisis Keluhan Teks (DL & NER)
        $complaintAnalysis = $this->aiEngine->predictComplaint($validated['keluhan']);

        // Check for Out-of-Domain
        if ($complaintAnalysis && isset($complaintAnalysis['is_out_of_domain']) && $complaintAnalysis['is_out_of_domain'] === true) {
            return response()->json([
                'status' => 'error',
                'message' => 'Keluhan tidak valid. Harap masukkan keluhan medis yang sebenarnya.',
            ], 422);
        }

        // Check for Emergency
        $message = 'Pendaftaran pasien berhasil.';
        if ($complaintAnalysis && isset($complaintAnalysis['is_emergency']) && $complaintAnalysis['is_emergency'] === true) {
            $message = 'PERINGATAN DARURAT: Sistem mendeteksi kondisi kritis! Pasien disarankan langsung menuju IGD.';
        }

        // 3. Rekomendasi Optimasi Antrian (RL)
        // Dummy queue length untuk contoh
        $currentQueueLength = 15; 
        $queueOptimization = $this->aiEngine->optimizeQueue($currentQueueLength);

        // Simulasi A/B Testing
        $modelVersion = rand(0, 1) ? 'v1' : 'v2';
        $confidence = $complaintAnalysis['confidence'] ?? 0.5;

        // Simpan data ke database untuk Dashboard ML Analytics & A/B Testing
        AiDataset::create([
            'kunjungan_id' => null, // Dummy for now
            'keluhan' => $validated['keluhan'],
            'kemungkinan_penyakit' => $complaintAnalysis['extracted_entities'] ?? [],
            'tingkat_urgensi' => $complaintAnalysis['cdc_triage'] ?? 'GREEN TAG',
            'rekomendasi_poli_nama' => $complaintAnalysis['predicted_poli_id'] ?? 1,
            'saran_tindakan' => $complaintAnalysis['action'] ?? '',
            'model_version' => $modelVersion,
            'nlp_confidence_score' => $confidence,
        ]);

        // Kunjungan::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => $message,
            'ai_insights' => [
                'surge_prediction' => $surgePrediction,
                'complaint_analysis' => $complaintAnalysis,
                'queue_optimization' => $queueOptimization,
            ]
        ]);
    }
}
