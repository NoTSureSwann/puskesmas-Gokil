<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send WhatsApp notification (Mock implementation)
     *
     * @param string $phoneNumber
     * @param string $message
     * @return bool
     */
    public static function send(string $phoneNumber, string $message): bool
    {
        // Untuk production, ganti dengan pemanggilan API Fonnte / Watzap
        // Http::post('https://api.fonnte.com/send', ['target' => $phoneNumber, 'message' => $message]);
        
        Log::info("WHATSAPP MOCK [To: {$phoneNumber}]: {$message}");
        
        return true;
    }
}
