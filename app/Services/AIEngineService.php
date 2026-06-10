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
     * Memanggil model Enterprise kBot untuk NLP dan Matematika Lanjut
     * 
     * @param string $message
     * @return array|null
     */
    public function analyzeKbot($message)
    {
        // UU PDP: Anonymize data (hapus angka NIK atau deteksi nama simpel)
        $anonymizedMessage = preg_replace('/\b\d{16}\b/', '[NIK_REDACTED]', $message);
        
        try {
            $response = Http::post("{$this->baseUrl}/kbot/analyze", [
                'message' => $anonymizedMessage,
            ]);

            if ($response->successful()) {
                return $response->json();
            }
            
            Log::error('AI Engine Error (analyzeKbot): ' . $response->body());
        } catch (\Exception $e) {
            Log::error('AI Engine Exception (analyzeKbot): ' . $e->getMessage());
        }

        return null;
    }
}
