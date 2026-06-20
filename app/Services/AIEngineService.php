<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIEngineService
{
    protected string $baseUrl;
    protected string $flaskSecret;

    public function __construct()
    {
        // Menggunakan config() agar kompatibel dengan config:cache
        $this->baseUrl = (string) config('services.ai_engine.flask_url', 'http://127.0.0.1:5000');
        $this->flaskSecret = (string) config('services.ai_engine.flask_secret', '');
    }

    /**
     * Membuat HTTP client dengan API secret header untuk Flask AI Engine.
     */
    protected function flaskRequest(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'X-API-Secret' => $this->flaskSecret,
        ]);
    }

    /**
     * Memanggil model ML untuk memprediksi lonjakan pasien
     * 
     * @param int $poliId
     * @param int $dayOfWeek (0 = Monday, 6 = Sunday)
     * @param int $hour
     * @return array|null
     */
    public function predictSurge($poliId, $dayOfWeek, $hour)
    {
        try {
            $response = $this->flaskRequest()->post("{$this->baseUrl}/predict/surge", [
                'poli_id' => $poliId,
                'day_of_week' => $dayOfWeek,
                'hour' => $hour,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('AI Engine Error (predictSurge): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (predictSurge): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Memanggil model DL untuk memprediksi poli tujuan berdasarkan keluhan
     * 
     * @param string $complaintText
     * @return array|null
     */
    public function predictComplaint($complaintText)
    {
        try {
            $response = $this->flaskRequest()->post("{$this->baseUrl}/predict/complaint", [
                'text' => $complaintText,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('AI Engine Error (predictComplaint): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (predictComplaint): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Memanggil model RL untuk optimasi antrian
     * 
     * @param int $queueLength
     * @return array|null
     */
    public function optimizeQueue($queueLength)
    {
        try {
            $response = $this->flaskRequest()->post("{$this->baseUrl}/optimize/queue", [
                'queue_length' => $queueLength,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('AI Engine Error (optimizeQueue): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (optimizeQueue): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Memanggil model Enterprise kBot (Groq API - Llama model)
     * 
     * @param string $message
     * @param array $history
     * @param array $flaskContext Konteks dari Flask AI Engine untuk alignment
     * @return array|null
     */
    public function analyzeKbot($message, $history = [], $flaskContext = [])
    {
        // UU PDP: Anonymize data (hapus angka NIK atau deteksi nama simpel)
        $anonymizedMessage = preg_replace('/\b\d{16}\b/', '[NIK_REDACTED]', $message);
        
        $groqApiKey = config('services.ai_engine.groq_api_key');

        if (!$groqApiKey) {
            Log::error('AI Engine Error: GROQ_API_KEY is missing in .env');
            return null;
        }

        try {
            $messages = [
                [
                    'role' => 'system',
                    'content' => <<<EOT
================================================================
SYSTEM PROMPT — kBot (Asisten Kesehatan AI)
================================================================
1. IDENTITAS & PERAN
Kamu adalah kBot, asisten kesehatan AI yang ramah, hangat, dan suportif. Tugasmu membantu user memahami kondisi kesehatan mereka secara awal, memberi triase ringan, dan mengarahkan ke tindakan/dokter yang tepat — BUKAN memberi diagnosis pasti. Gaya bahasa: santai tapi profesional, empatik, tidak menggurui.

2. ATURAN UTAMA (NON-NEGOTIABLE)
ATURAN #1 — Kartu Analisis (Triase CDC, Skor Keparahan, dll.) HANYA boleh ditampilkan jika user sudah menyebutkan keluhan/gejala kesehatan yang konkret.
ATURAN #2 — Sapaan seperti "halo", "hai", "pagi", "test", "p" TANPA disertai keluhan kesehatan WAJIB dijawab dengan teks percakapan biasa (STATE 1).
ATURAN #3 — Jangan pernah melompat ke kesimpulan triase dari informasi yang tidak ada. Jika ragu apakah informasi cukup, ANGGAP BELUM CUKUP dan tanyakan dulu (STATE 2).

3. STATE MACHINE PERCAKAPAN
Untuk setiap pesan user, tentukan dulu state-nya SEBELUM membalas:
STATE 1 — SAPAAN / SMALL TALK (Ciri: tidak ada informasi gejala. Aksi: balas hangat, perkenalan singkat, tanya keluhan.)
STATE 2 — KELUHAN AMBIGU / INFO KURANG (Ciri: ada kata sakit tapi tanpa detail. Aksi: ajukan maks 2 pertanyaan klarifikasi.)
STATE 3 — KELUHAN JELAS / INFO CUKUP (Ciri: ada gejala + min 2 dari durasi/lokasi/intensitas. Aksi: lakukan triase lengkap.)
STATE 4 — TANDA BAHAYA / DARURAT (Ciri: red-flag. Aksi: arahkan ke 119/IGD terdekat segera tanpa menunggu info lain.)

4. KRITERIA TRIASE (CDC)
GREEN TAG (Non-Urgent/Aman) : gejala ringan, stabil (skor 0.0-3.0)
YELLOW TAG (Urgent) : gejala sedang, perlu periksa <24 jam (skor 3.1-6.9)
RED TAG (Emergency) : gejala berat, penanganan segera (skor 7.0-10.0)

5. FORMAT OUTPUT
Anda WAJIB memberikan respons murni dalam JSON (tanpa tag markdown tambahan) dengan struktur ini:
{
  "reasoning_metrics": {
    "state": 1, // isi dengan 1, 2, 3, atau 4
    "triase_tag": "GREEN TAG", // (kosongkan jika state 1/2)
    "skor": "2.5/10.0 (Ringan)", // (kosongkan jika state 1/2)
    "rekomendasi_aksi": "...", // (kosongkan jika state 1/2)
    "klasifikasi_poli": "Poli Umum", // (kosongkan jika state 1/2)
    "rekomendasi_dokter": "...", // (kosongkan jika state 1/2)
    "tips_kesehatan": "...",
    "makanan_buah": "...",
    "pola_hidup": "..."
  },
  "patient_response": "Halo! Saya kBot... (Jangan sertakan markdown kartu triase/analisis di sini. Frontend akan merender kartu analisis secara terpisah. Cukup sapaan, respons empatik, atau pertanyaan klarifikasi)"
}

6. PEMBATASAN & SAFETY GUARDRAILS
- Jangan memberi diagnosis pasti/nama penyakit definitif.
- Selalu sertakan disclaimer "bukan pengganti diagnosis dokter" pada patient_response jika di State 3 atau 4.
EOT
                ]
            ];

            foreach ($history as $chat) {
                if (isset($chat['role']) && isset($chat['content'])) {
                    $messages[] = [
                        'role' => $chat['role'] === 'user' ? 'user' : 'assistant',
                        'content' => $chat['content']
                    ];
                }
            }

            // Inject konteks Flask AI Engine agar Groq selaras
            if (!empty($flaskContext)) {
                $contextMsg = "[KONTEKS AI ENGINE LOKAL] Sistem NLP lokal telah menganalisis keluhan ini dengan hasil: "
                    . "Triase: " . ($flaskContext['flask_triage'] ?? 'N/A')
                    . ", ICD-10: " . ($flaskContext['flask_icd10'] ?? 'N/A')
                    . ", Dokter Rekomendasi: " . ($flaskContext['flask_doctor'] ?? 'Umum')
                    . ". Gunakan informasi ini sebagai referensi untuk konsistensi, tapi tetap evaluasi independen.";

                $messages[] = [
                    'role' => 'system',
                    'content' => $contextMsg
                ];
            }

            $messages[] = [
                'role' => 'user',
                'content' => $anonymizedMessage
            ];

            $response = Http::withToken($groqApiKey)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.ai_engine.groq_model', 'llama-3.3-70b-versatile'),
                    'messages' => $messages,
                    'temperature' => 0.5,
                    'max_tokens' => 700,
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '{}';
                
                // Parse JSON response dari Llama
                $parsedContent = json_decode($content, true);

                // Generate UUID for RLHF tracing
                $messageId = \Illuminate\Support\Str::uuid()->toString();

                // Build Log Entry for DPO/RLHF dataset
                $logEntry = [
                    'message_id' => $messageId,
                    'timestamp' => now()->toIso8601String(),
                    'prompt' => $messages,
                    'response' => $parsedContent
                ];

                // Ensure directory exists and append to jsonl
                $logDir = storage_path('app/ai_engine_data');
                if (!\Illuminate\Support\Facades\File::exists($logDir)) {
                    \Illuminate\Support\Facades\File::makeDirectory($logDir, 0755, true);
                }
                \Illuminate\Support\Facades\File::append(
                    $logDir . '/interaction_logs.jsonl', 
                    json_encode($logEntry) . "\n"
                );

                return [
                    'status' => 'success',
                    'message_id' => $messageId,
                    'parameter_1' => $parsedContent['patient_response'] ?? 'Maaf, saya tidak mengerti.',
                    'parameter_2' => [
                        'metrics' => $parsedContent['reasoning_metrics'] ?? [],
                        'model_usage' => $data['usage'] ?? []
                    ]
                ];
            }
            
            Log::error('AI Engine Error (analyzeKbot Groq): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (analyzeKbot Groq): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Memanggil Flask AI Engine /kbot/analyze untuk analisis terpadu
     * 
     * @param string $message
     * @return array|null
     */
    public function analyzeKbotFlask(string $message): ?array
    {
        try {
            $response = $this->flaskRequest()->post("{$this->baseUrl}/kbot/analyze", [
                'message' => $message,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('AI Engine Error (analyzeKbotFlask): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (analyzeKbotFlask): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Mengirimkan rating feedback kBot ke sistem (Untuk RLHF DPO)
     * 
     * @param string|null $messageId
     * @param int $rating
     * @param string $originalInput
     * @return array|null
     */
    public function feedbackKbotFlask(?string $messageId, int $rating, string $originalInput): ?array
    {
        try {
            $logDir = storage_path('app/ai_engine_data');
            if (!\Illuminate\Support\Facades\File::exists($logDir)) {
                \Illuminate\Support\Facades\File::makeDirectory($logDir, 0755, true);
            }

            $feedbackEntry = [
                'message_id' => $messageId,
                'rating' => $rating,
                'original_input' => $originalInput,
                'timestamp' => now()->toIso8601String()
            ];

            \Illuminate\Support\Facades\File::append(
                $logDir . '/feedback_labels.jsonl', 
                json_encode($feedbackEntry) . "\n"
            );

            // Jika masih perlu ke Flask lama:
            // Http::post("{$this->baseUrl}/kbot/feedback", ...);

            return ['status' => 'success'];
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (feedbackKbotFlask): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Merangkum riwayat pasien menggunakan Groq API.
     * 
     * @param \App\Models\ProfilPasien $pasien
     * @param \Illuminate\Database\Eloquent\Collection $riwayats
     * @return string|null
     */
    public function summarizePatientHistory($pasien, $riwayats)
    {
        $groqApiKey = config('services.ai_engine.groq_api_key');
        if (!$groqApiKey || $riwayats->isEmpty()) {
            return null;
        }

        // Susun data riwayat menjadi format teks
        $historyText = "Data Pasien: Usia " . ($pasien->tanggal_lahir ? \Carbon\Carbon::parse($pasien->tanggal_lahir)->age : 'Unknown') . " tahun, Gol Darah: " . ($pasien->golongan_darah ?? '-') . ".\n\nRiwayat Kunjungan:\n";
        
        foreach ($riwayats->take(5) as $r) { // Ambil 5 terakhir agar tidak terlalu panjang
            $historyText .= "- Tanggal: " . \Carbon\Carbon::parse($r->tanggal_kunjungan)->format('d/m/Y') . "\n";
            $historyText .= "  Poli: " . ($r->poli->nama ?? 'Umum') . "\n";
            $historyText .= "  Keluhan: " . ($r->keluhan ?? '-') . "\n";
            $historyText .= "  Diagnosis: " . ($r->diagnosis ?? '-') . "\n";
            if ($r->resep && $r->resep->detailResep->count() > 0) {
                $obat = $r->resep->detailResep->map(fn($d) => $d->obat->nama_obat ?? '')->filter()->implode(', ');
                $historyText .= "  Obat Diberikan: " . $obat . "\n";
            }
        }

        try {
            $response = Http::withToken($groqApiKey)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.ai_engine.groq_model', 'llama-3.3-70b-versatile'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah asisten medis profesional. Tugas Anda adalah memberikan SATU PARAGRAF ringkas (maksimal 4 kalimat) yang merangkum riwayat pasien di bawah ini untuk dibaca dengan cepat oleh dokter sebelum pemeriksaan. Fokus pada keluhan berulang, diagnosis utama, dan pola obat.'
                        ],
                        [
                            'role' => 'user',
                            'content' => $historyText
                        ]
                    ],
                    'temperature' => 0.4,
                    'max_tokens' => 300,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return $data['choices'][0]['message']['content'] ?? null;
            }
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (summarizePatientHistory): ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Mengecek interaksi antar obat dan kontraindikasi dengan alergi pasien menggunakan Groq LLM.
     * 
     * @param array $obatList Array nama-nama obat
     * @param string|null $alergiPasien Riwayat alergi pasien
     * @return array|null Respons JSON terstruktur dari LLM
     */
    public function checkDrugInteraction(array $obatList, ?string $alergiPasien = null): ?array
    {
        $groqApiKey = config('services.ai_engine.groq_api_key');
        if (!$groqApiKey || count($obatList) === 0) {
            return null;
        }

        $obatString = implode(', ', $obatList);
        $alergiString = $alergiPasien ? $alergiPasien : 'Tidak ada/Tidak diketahui';

        $systemPrompt = <<<EOT
Anda adalah Apoteker Klinis AI profesional. Tugas Anda adalah mengecek potensi interaksi obat (Drug Interaction) dan kontraindikasi dengan riwayat alergi pasien.

ATURAN OUTPUT WAJIB BERFORMAT JSON MURNI TANPA TAG MARKDOWN:
{
  "risk_level": "Aman" | "Sedang" | "Tinggi",
  "description": "Deskripsi singkat tentang interaksi yang mungkin terjadi (jika ada).",
  "recommendation": "Rekomendasi untuk apoteker/dokter."
}

Jika ada alergi yang berbenturan dengan salah satu obat, set risk_level menjadi "Tinggi".
Jika tidak ada interaksi signifikan, set risk_level "Aman".
EOT;

        $userPrompt = "Daftar Obat: $obatString\nRiwayat Alergi Pasien: $alergiString\n\nLakukan analisis sekarang.";

        try {
            $response = Http::withToken($groqApiKey)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => config('services.ai_engine.groq_model', 'llama-3.3-70b-versatile'),
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => $systemPrompt
                        ],
                        [
                            'role' => 'user',
                            'content' => $userPrompt
                        ]
                    ],
                    'temperature' => 0.1, // Suhu rendah agar respons deterministik/konsisten
                    'max_tokens' => 400,
                    'response_format' => ['type' => 'json_object'] // Force JSON output for Llama 3 models
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '{}';
                return json_decode($content, true);
            }
            
            Log::error('AI Engine Error (checkDrugInteraction): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (checkDrugInteraction): ' . $e->getMessage());
        }

        return null;
    }
}
