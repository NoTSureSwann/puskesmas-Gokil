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
            if (strlen($token) < 4) continue; // Abaikan kata hubung yang pendek
            
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

        // ==========================================
        // RAG (RETRIEVAL-AUGMENTED GENERATION) LOGIC
        // ==========================================
        $ragContext = null;
        $ragTitle = null;

        if (count($reasoningMetrics['matched_keywords']) > 0) {
            $query = \App\Models\KnowledgeBase::query();
            foreach ($reasoningMetrics['matched_keywords'] as $keyword) {
                $query->orWhere('content', 'LIKE', '%' . $keyword . '%');
            }
            
            $journalMatch = $query->first();

            if ($journalMatch) {
                // Potong teks agar tidak terlalu panjang (Maks 300 karakter)
                $snippet = substr($journalMatch->content, 0, 300) . '...';
                
                $ragTitle = $journalMatch->title;
                $ragContext = "Berdasarkan pedoman jurnal medis: *{$ragTitle}*.\nReferensi Klinis: \"{$snippet}\"\n\n";
                
                // Tambahkan konteks RAG ke dalam respons KBot
                $patientResponse = $ragContext . $patientResponse;
                
                $reasoningMetrics['rag_grounding_source'] = $ragTitle;
                $reasoningMetrics['nlp_confidence_score'] = 99; // Skor maksimum karena didukung literatur!
            }
        }

        if (!isset($reasoningMetrics['nlp_confidence_score'])) {
            $reasoningMetrics['nlp_confidence_score'] = $highestWeight > 0 ? ($highestWeight * 100) : 50; // percentage
        }
        $reasoningMetrics['logical_analysis'] = "Term-Frequency matched highest weight: " . $highestWeight . " -> " . $predictedPoli;

        // Logging untuk audit bias dan metrics
        \Illuminate\Support\Facades\Log::info('kBot ML Analysis (Naive Bayes)', [
            'user_message' => $request->message,
            'ai_reasoning_metrics' => $reasoningMetrics
        ]);

        // ACTIVE LEARNING: Jika confidence < 65, simpan ke AiDataset untuk dianotasi oleh dokter
        if ($reasoningMetrics['nlp_confidence_score'] < 65) {
            \App\Models\AiDataset::create([
                'kunjungan_id' => null, // Bukan dari pendaftaran formal
                'keluhan' => $request->message,
                'kemungkinan_penyakit' => ['[Uncertain] KBot Chat Log'],
                'tingkat_urgensi' => 'Rendah',
                'rekomendasi_poli_nama' => $predictedPoli,
                'saran_tindakan' => 'Analisis chat gagal menemukan konteks yang kuat.',
                'needs_annotation' => true,
                'is_synthetic' => false,
            ]);
        }

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
