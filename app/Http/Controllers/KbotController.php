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
            'history' => 'nullable|array',
        ]);

        $message = strtolower($request->message);

        // Generate Unified Message ID — satu UUID untuk seluruh pipeline
        $messageId = \Illuminate\Support\Str::uuid()->toString();

        // Call Python AI Engine via Flask Proxy
        $complaintAnalysis = $this->aiEngine->analyzeKbotFlask($request->message);

        // Fallback jika AI mati atau error
        if (!$complaintAnalysis || !isset($complaintAnalysis['status']) || $complaintAnalysis['status'] !== 'success') {
            return response()->json([
                'status' => 'error',
                'parameter_1' => 'Maaf, sistem AI sedang offline atau tidak dapat memproses keluhan Anda saat ini. Silakan hubungi petugas secara manual.',
                'parameter_2' => []
            ]);
        }

        $parameter_1 = $complaintAnalysis['parameter_1'];
        $parameter_2 = $complaintAnalysis['parameter_2'];

        // Jika ada GROQ_API_KEY, gunakan Groq API untuk memperkaya respons dengan LLM yang lebih natural
        // Inject Flask context agar Groq & Flask AI Engine SELARAS (tidak kontradiktif)
        if (config('services.ai_engine.groq_api_key')) {
            $history = $request->input('history', []);

            // Kirim konteks Flask ke Groq agar triase konsisten
            $flaskContext = [
                'flask_severity' => $parameter_2['statistical_quartiles'] ?? [],
                'flask_triage' => $parameter_2['statistical_quartiles']['cdc_triage'] ?? 'Unknown',
                'flask_icd10' => $parameter_2['international_standards']['who_icd10'] ?? '',
                'flask_doctor' => $parameter_2['nlp_classification']['doctor'] ?? 'Umum',
            ];

            $groqResult = $this->aiEngine->analyzeKbot($request->message, $history, $flaskContext);
            if ($groqResult && isset($groqResult['status']) && $groqResult['status'] === 'success') {
                $parameter_1 = $groqResult['parameter_1'];
                if (isset($groqResult['parameter_2']['metrics'])) {
                    $parameter_2['metrics'] = array_merge($parameter_2['metrics'] ?? [], $groqResult['parameter_2']['metrics']);
                }
                // Pertahankan message_id dari unified source
                $messageId = $groqResult['message_id'] ?? $messageId;
            }
        }

        $nlpClassification = $parameter_2['nlp_classification'] ?? [];
        $confidenceScore = ($nlpClassification['confidence'] ?? 0.5) * 100;
        $isOod = $nlpClassification['is_out_of_domain'] ?? false;
        $extractedEntities = $parameter_2['extracted_entities'] ?? [];
        
        $triage = $parameter_2['statistical_quartiles']['cdc_triage'] ?? 'Unknown';
        $predictedPoliId = $nlpClassification['poli_id'] ?? 1;

        // ==========================================
        // RAG (RETRIEVAL-AUGMENTED GENERATION) LOGIC
        // ==========================================
        if (!$isOod && !empty($extractedEntities)) {
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
                $parameter_1 .= $ragContext;
                if (isset($parameter_2['metrics'])) {
                    $parameter_2['metrics']['rag_grounding_source'] = $ragTitle;
                } else {
                    $parameter_2['metrics'] = ['rag_grounding_source' => $ragTitle];
                }
                
                // Boost confidence if literature found (Optional logic)
                $confidenceScore = min(100, $confidenceScore + 10);
                if (isset($parameter_2['nlp_classification'])) {
                    $parameter_2['nlp_classification']['confidence'] = $confidenceScore / 100;
                }
            }
        }

        // Logging untuk audit bias dan metrics
        \Illuminate\Support\Facades\Log::info('kBot ML Analysis (Python NLP Engine Proxy)', [
            'user_message' => $request->message,
            'ai_reasoning_metrics' => $parameter_2
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
            'message_id' => $messageId,
            'parameter_1' => $parameter_1,
            'parameter_2' => $parameter_2
        ]);
    }

    /**
     * Endpoint untuk mem-proxy feedback dari kbot.js ke Flask API
     */
    public function feedback(Request $request)
    {
        $request->validate([
            'message_id' => 'nullable|string',
            'rating' => 'required|integer|in:0,1',
            'original_input' => 'required|string',
        ]);

        $feedbackResponse = $this->aiEngine->feedbackKbotFlask(
            $request->message_id,
            (int)$request->rating,
            $request->original_input
        );

        if (!$feedbackResponse || !isset($feedbackResponse['status']) || $feedbackResponse['status'] !== 'success') {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirimkan feedback ke server AI.'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Feedback berhasil disimpan.'
        ]);
    }

    /**
     * Booking antrean langsung dari kBot
     */
    public function bookAppointment(Request $request)
    {
        $request->validate([
            'poli_name' => 'required|string',
            'keluhan' => 'nullable|string'
        ]);

        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user || $user->role !== 'pasien' || !$user->profilPasien) {
            return response()->json(['status' => 'error', 'message' => 'Anda harus login sebagai pasien untuk menggunakan fitur ini.'], 401);
        }

        $poliName = trim(str_replace('Poli', '', $request->poli_name));
        $poli = \App\Models\Poli::query()->where('nama_poli', 'LIKE', '%' . $poliName . '%')->first();
        if (!$poli) {
            $poli = \App\Models\Poli::query()->where('is_aktif', true)->first(); // Fallback to Poli Umum
        }

        // Get Doctor for this poli
        $dokter = \App\Models\User::query()->whereHas('profilDokter', function($q) use ($poli) {
            $q->where('spesialisasi', 'LIKE', '%' . str_replace('Poli', '', $poli->nama_poli) . '%');
        })->first();
        
        if (!$dokter) {
            $dokter = \App\Models\User::query()->where('role', 'dokter')->first(); // Fallback
        }

        $today = \Carbon\Carbon::today();

        // Cek jika sudah terdaftar di poli yang sama pada hari yang sama
        $exist = \App\Models\Kunjungan::query()->where('pasien_id', $user->profilPasien->id)
            ->where('poli_id', $poli->id)
            ->whereDate('tanggal_kunjungan', '=', $today, 'and')
            ->whereIn('status', ['menunggu', 'dipanggil', 'diperiksa', 'resep'])
            ->first();

        if ($exist) {
            return response()->json([
                'status' => 'error', 
                'message' => 'Anda sudah terdaftar di ' . $poli->nama_poli . ' hari ini. No Antrean: ' . $exist->no_antrian
            ], 400);
        }

        $noAntrian = \App\Models\Kunjungan::query()->whereDate('tanggal_kunjungan', '=', $today, 'and')
            ->where('dokter_id', $dokter->id)
            ->max('no_antrian');
        $noAntrian = $noAntrian ? $noAntrian + 1 : 1;

        $kunjungan = \App\Models\Kunjungan::create([
            'pasien_id' => $user->profilPasien->id,
            'dokter_id' => $dokter->id,
            'poli_id' => $poli->id,
            'tanggal_kunjungan' => $today,
            'keluhan' => $request->keluhan ?? 'Pendaftaran via KBot',
            'no_antrian' => $noAntrian,
            'status' => 'menunggu',
            'jenis_kunjungan' => 'baru',
            'metode_kunjungan' => 'tatap_muka',
            'no_kunjungan' => 'K-' . date('Ymd') . '-' . str_pad((string)$noAntrian, 3, '0', STR_PAD_LEFT),
            'jam_daftar' => now()
        ]);

        // Integrate WhatsAppService here
        if (class_exists(\App\Services\WhatsAppService::class)) {
            \App\Services\WhatsAppService::send(
                $user->phone ?? '08000000', 
                "Halo {$user->name}, pendaftaran antrean Anda di {$poli->nama_poli} berhasil. No Antrean: {$noAntrian}. Tunjukkan pesan ini ke petugas."
            );
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Pendaftaran berhasil! No Antrean Anda: ' . $noAntrian,
            'redirect' => route('pasien.kunjungan', $kunjungan->id)
        ]);
    }
}
