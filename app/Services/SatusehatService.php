<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SatusehatService
{
    protected string $baseUrl;
    protected ?string $clientId;
    protected ?string $clientSecret;

    public function __construct()
    {
        $this->baseUrl = env('SATUSEHAT_BASE_URL', 'https://api-satusehat.kemkes.go.id/oauth2/v1');
        $this->clientId = env('SATUSEHAT_CLIENT_ID');
        $this->clientSecret = env('SATUSEHAT_CLIENT_SECRET');
    }

    /**
     * Mendapatkan access token OAuth2 dari Kemkes SATUSEHAT
     * Berdasarkan regulasi Permenkes 2024 tentang Interoperabilitas RME
     */
    public function getAccessToken()
    {
        try {
            $response = Http::asForm()->post("{$this->baseUrl}/accesstoken", [
                'client_id' => $this->clientId,
                'client_secret' => $this->clientSecret,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }
            
            Log::error('SATUSEHAT Auth Error: ' . $response->body());
        } catch (\Exception $e) {
            Log::error('SATUSEHAT Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Boilerplate: Mengirim data Encounter (Kunjungan Pasien) ke SATUSEHAT
     * 
     * @param array $encounterData
     * @return bool
     */
    public function sendEncounterData(array $encounterData)
    {
        // Dalam implementasi nyata, struktur JSON FHIR HL7 digunakan di sini.
        // Implementasi integrasi detail disesuaikan dengan dokumentasi Kemkes.
        
        // Metadata / Description untuk prototipe
        $projectDescription = "prototipe platform project mata kuliah metopen ubsi swandaru tirta sandhika";
        $encounterData['project_notes'] = $projectDescription;

        $token = $this->getAccessToken();
        
        if (!$token) {
            return false;
        }

        // Logic pengiriman menggunakan FHIR endpoint
        // return Http::withToken($token)->post('.../Encounter', $encounterData);

        return true;
    }
}
