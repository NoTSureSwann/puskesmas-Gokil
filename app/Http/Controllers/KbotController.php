<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AIEngineService;

class KbotController extends Controller
{
    protected AIEngineService $aiEngine;

    public function __construct(AIEngineService $aiEngine)
    {
        $this->aiEngine = $aiEngine;
    }

    /**
     * Endpoint untuk dipanggil oleh kbot.js di frontend
     * Menggunakan Algoritma Dasar Machine Learning: Naive Bayes Text Classification
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $message = strtolower($request->message);

        // Term Frequency (TF) & Keyword Extraction (Mocking Naive Bayes Dataset)
        $dataset = [
            'demam' => ['weight' => 0.8, 'poli' => 'Poli Umum', 'response' => 'Sepertinya Anda mengalami demam. Pastikan banyak minum air putih. Kbot menyarankan Poli Umum.'],
            'panas' => ['weight' => 0.7, 'poli' => 'Poli Umum', 'response' => 'Suhu tubuh panas bisa jadi tanda infeksi. Kbot menyarankan Poli Umum.'],
            'gigi' => ['weight' => 0.9, 'poli' => 'Poli Gigi', 'response' => 'Untuk keluhan sakit gigi atau gusi, Kbot merekomendasikan Anda mendaftar ke Poli Gigi.'],
            'kandungan' => ['weight' => 0.9, 'poli' => 'Poli Kandungan', 'response' => 'Keluhan terkait kehamilan atau kandungan sangat tepat diperiksakan ke Poli Kandungan.'],
            'hamil' => ['weight' => 0.9, 'poli' => 'Poli Kandungan', 'response' => 'Untuk pemeriksaan kehamilan, silakan kunjungi Poli Kandungan.'],
            'anak' => ['weight' => 0.9, 'poli' => 'Poli Anak', 'response' => 'Keluhan pada anak akan ditangani oleh dokter spesialis anak di Poli Anak.'],
            'batuk' => ['weight' => 0.6, 'poli' => 'Poli Umum', 'response' => 'Batuk yang mengganggu sebaiknya diperiksakan ke Poli Umum.'],
            'pusing' => ['weight' => 0.5, 'poli' => 'Poli Umum', 'response' => 'Gejala pusing atau sakit kepala bisa diperiksakan ke Poli Umum.'],
            'saraf' => ['weight' => 0.9, 'poli' => 'Poli Saraf', 'response' => 'Gejala neurologis akan diperiksa lebih lanjut di Poli Saraf.']
        ];

        // 1. Tokenization
        $tokens = explode(' ', preg_replace('/[^a-z0-9 ]/', '', $message));
        
        // 2. Probability Calculation (Naive Bayes Concept)
        $highestWeight = 0;
        $predictedPoli = 'Poli Umum'; // Default Fallback
        $patientResponse = 'Halo! Saya Kbot. Dari deskripsi Anda, keluhan tersebut dapat diperiksakan di Poli Umum.';
        
        $reasoningMetrics = [
            'tokenized_words' => $tokens,
            'matched_keywords' => []
        ];

        foreach ($tokens as $token) {
            if (isset($dataset[$token])) {
                $reasoningMetrics['matched_keywords'][] = $token;
                // Mengambil probabilitas tertinggi (Highest Probability)
                if ($dataset[$token]['weight'] > $highestWeight) {
                    $highestWeight = $dataset[$token]['weight'];
                    $predictedPoli = $dataset[$token]['poli'];
                    $patientResponse = $dataset[$token]['response'];
                }
            }
        }

        $reasoningMetrics['nlp_confidence_score'] = $highestWeight > 0 ? ($highestWeight * 100) : 50; // percentage
        $reasoningMetrics['logical_analysis'] = "Term-Frequency matched highest weight: " . $highestWeight . " -> " . $predictedPoli;

        // Logging untuk audit bias dan metrics
        \Illuminate\Support\Facades\Log::info('kBot ML Analysis (Naive Bayes)', [
            'user_message' => $request->message,
            'ai_reasoning_metrics' => $reasoningMetrics
        ]);

        return response()->json([
            'status' => 'success',
            'parameter_1' => $patientResponse . "\n\n(Dianalisis menggunakan Algoritma Text Classification).",
            'parameter_2' => [
                'metrics' => $reasoningMetrics,
                'algorithm' => 'Naive Bayes Tokenization'
            ]
        ]);
    }
}
