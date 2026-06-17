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

        // Call Python AI Engine
        $complaintAnalysis = $this->aiEngine->predictComplaint($message);

        // Fallback jika AI mati atau error
        if (!$complaintAnalysis || !isset($complaintAnalysis['predicted_poli_id'])) {
            return response()->json([
                'status' => 'error',
                'parameter_1' => 'Maaf, sistem AI sedang offline atau tidak dapat memproses keluhan Anda saat ini. Silakan hubungi petugas secara manual.',
                'parameter_2' => []
            ]);
        }

        // Ekstraksi data dari AI
        $poliMapping = [
            1 => 'Poli Umum',
            2 => 'Poli Gigi',
            3 => 'Poli KIA',
            4 => 'Poli Gizi',
            5 => 'Poli Anak',
            6 => 'Poli Kandungan',
            7 => 'Poli Saraf'
        ];
        
        $predictedPoliId = $complaintAnalysis['predicted_poli_id'];
        $predictedPoliName = $poliMapping[$predictedPoliId] ?? 'Poli Umum';
        $confidenceScore = ($complaintAnalysis['confidence'] ?? 0.5) * 100;
        
        $extractedEntities = $complaintAnalysis['extracted_entities'] ?? [];
        $symptomsStr = empty($extractedEntities) ? 'tidak ada spesifik' : implode(', ', $extractedEntities);
        $triage = $complaintAnalysis['cdc_triage'] ?? 'Unknown';

        $isEmergency = $complaintAnalysis['is_emergency'] ?? false;
        $isOod = $complaintAnalysis['is_out_of_domain'] ?? false;

        // Pembentukan Respons KBot
        if ($isOod) {
            $patientResponse = "Saya mendeteksi bahwa pesan Anda tidak berkaitan dengan keluhan medis atau layanan puskesmas. Harap masukkan keluhan kesehatan yang sebenarnya agar saya bisa membantu.";
        } elseif ($isEmergency) {
            $patientResponse = "🚨 **PERINGATAN DARURAT:** Sistem mendeteksi kondisi medis kritis berdasarkan gejala Anda ({$symptomsStr})! KBot menyarankan Anda untuk SEGERA menuju IGD (Instalasi Gawat Darurat) terdekat. Jangan menunggu!";
        } else {
            $patientResponse = "Halo! Saya KBot. Berdasarkan keluhan Anda, AI berhasil mengekstrak gejala klinis berikut: **{$symptomsStr}**.\n\n";
            $patientResponse .= "KBot menyarankan Anda mendaftar ke **{$predictedPoliName}**.\n";
            $patientResponse .= "Kategori Triage Anda adalah: **{$triage}**.";
        }

        $reasoningMetrics = [
            'symptoms_extracted' => $extractedEntities,
            'triage_level' => $triage,
            'nlp_confidence_score' => $confidenceScore,
            'is_emergency' => $isEmergency,
            'is_out_of_domain' => $isOod,
            'logical_analysis' => "Python NLP Model prediction: {$predictedPoliName} (Confidence: {$confidenceScore}%)"
        ];

        // ==========================================
        // RAG (RETRIEVAL-AUGMENTED GENERATION) LOGIC
        // ==========================================
        if (!$isOod && !$isEmergency && !empty($extractedEntities)) {
            $query = \App\Models\KnowledgeBase::query();
            foreach ($extractedEntities as $entity) {
                $query->orWhere('content', 'LIKE', '%' . $entity . '%');
            }
            
            $journalMatch = $query->first();

            if ($journalMatch) {
                // Potong teks agar tidak terlalu panjang (Maks 300 karakter)
                $snippet = substr($journalMatch->content, 0, 300) . '...';
                
                $ragTitle = $journalMatch->title;
                $ragContext = "\n\n💡 *Berdasarkan pedoman jurnal medis: {$ragTitle}*.\nReferensi Klinis: \"{$snippet}\"";
                
                // Tambahkan konteks RAG ke dalam respons KBot
                $patientResponse .= $ragContext;
                $reasoningMetrics['rag_grounding_source'] = $ragTitle;
                
                // Boost confidence if literature found (Optional logic)
                $confidenceScore = min(100, $confidenceScore + 10);
                $reasoningMetrics['nlp_confidence_score'] = $confidenceScore;
            }
        }

        // Logging untuk audit bias dan metrics
        \Illuminate\Support\Facades\Log::info('kBot ML Analysis (Python NLP Engine)', [
            'user_message' => $request->message,
            'ai_reasoning_metrics' => $reasoningMetrics
        ]);

        // ACTIVE LEARNING: Jika confidence < 65 dan bukan out of domain, simpan ke AiDataset untuk dianotasi oleh dokter
        if ($confidenceScore < 65 && !$isOod) {
            $modelVersion = rand(0, 1) ? 'v1' : 'v2'; // Simulate A/B Testing metadata for KBot too
            \App\Models\AiDataset::create([
                'kunjungan_id' => null, // Bukan dari pendaftaran formal
                'keluhan' => $request->message,
                'kemungkinan_penyakit' => $extractedEntities,
                'tingkat_urgensi' => $triage,
                'rekomendasi_poli_nama' => $predictedPoliId,
                'saran_tindakan' => 'Analisis KBot memiliki confidence rendah.',
                'needs_annotation' => true,
                'is_synthetic' => false,
                'model_version' => $modelVersion,
                'nlp_confidence_score' => $confidenceScore / 100, // Normalized 0-1
            ]);
        }

        return response()->json([
            'status' => 'success',
            'parameter_1' => $patientResponse . "\n\n(Dianalisis menggunakan Python NLP Engine & NER).",
            'parameter_2' => [
                'metrics' => $reasoningMetrics,
                'algorithm' => 'Python NER & Severity Scorer'
            ]
        ]);
    }
}
