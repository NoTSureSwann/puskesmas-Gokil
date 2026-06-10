<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIEngineService
{
    protected string $baseUrl;

    public function __construct()
    {
        // Secara default menggunakan port 5000 untuk Flask AI API
        $this->baseUrl = env('AI_ENGINE_URL', 'http://127.0.0.1:5000');
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
            $response = Http::post("{$this->baseUrl}/predict/surge", [
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
            $response = Http::post("{$this->baseUrl}/predict/complaint", [
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
            $response = Http::post("{$this->baseUrl}/optimize/queue", [
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
     * @return array|null
     */
    public function analyzeKbot($message)
    {
        // UU PDP: Anonymize data (hapus angka NIK atau deteksi nama simpel)
        $anonymizedMessage = preg_replace('/\b\d{16}\b/', '[NIK_REDACTED]', $message);
        
        $groqApiKey = env('GROQ_API_KEY');

        if (!$groqApiKey) {
            Log::error('AI Engine Error: GROQ_API_KEY is missing in .env');
            return null;
        }

        try {
            $response = Http::withToken($groqApiKey)
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => 'llama-3.1-8b-instant',
                    'messages' => [
                        [
                            'role' => 'system',
                            'content' => 'Anda adalah kBot Enterprise, asisten dokter cerdas. PERINGATAN KERAS: ANDA BUKAN PASIEN. Jangan pernah berbicara seolah-olah Anda yang sakit. Tugas Anda HANYA MENJAWAB pesan user/pasien.' .
                                         'Anda harus merespons murni dalam format JSON. ' .
                                         'Tugas 1: Lakukan "Chain of Thought" di balik layar untuk analisis logis & cek bias. ' .
                                         'Tugas 2: Berikan respons AI asisten yang natural, ramah, dan solutif (menggunakan Markdown) kepada pasien. ' .
                                         'Format JSON WAJIB: ' .
                                         '{ "reasoning_metrics": { "logical_analysis": "...", "bias_check": "...", "nlp_confidence_score": 95, "robustness_check": "..." }, "patient_response": "Halo! Saya kBot, asisten medis Anda. Ada yang bisa saya bantu terkait gejala tersebut? ..." }'
                        ],
                        [
                            'role' => 'user',
                            'content' => $anonymizedMessage
                        ]
                    ],
                    'temperature' => 0.6,
                    'max_tokens' => 1024,
                    'response_format' => ['type' => 'json_object']
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $content = $data['choices'][0]['message']['content'] ?? '{}';
                
                // Parse JSON response dari Llama
                $parsedContent = json_decode($content, true);

                return [
                    'status' => 'success',
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
}
