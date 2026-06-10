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
     */
    public function analyze(Request $request)
    {
        $request->validate([
            'message' => 'required|string',
        ]);

        $result = $this->aiEngine->analyzeKbot($request->message);

        if ($result && isset($result['status']) && $result['status'] === 'success') {
            // Log reasoning AI di backend untuk keperluan audit dan monitoring bias
            \Illuminate\Support\Facades\Log::info('kBot Enterprise Analysis', [
                'user_message' => $request->message,
                'ai_reasoning_metrics' => $result['parameter_2'] ?? []
            ]);

            return response()->json([
                'status' => 'success',
                'parameter_1' => $result['parameter_1'],
            ]);
        }

        return response()->json([
            'status' => 'error',
            'message' => 'Gagal terhubung ke Enterprise AI Engine'
        ], 500);
    }
}
