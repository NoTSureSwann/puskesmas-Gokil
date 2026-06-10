<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogRmeAccess
{
    /**
     * Handle an incoming request.
     * Mencatat setiap akses BACA ke rute terkait rekam medis elektronik (RME)
     * sesuai standar Permenkes 2024.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Lanjutkan request dulu
        $response = $next($request);

        // Jika user login dan request GET (akses baca)
        if (Auth::check() && $request->isMethod('get')) {
            // Catat log
            DB::table('audit_logs')->insert([
                'user_id' => Auth::id(),
                'event' => 'read_rme',
                'auditable_type' => 'App\Models\RekamMedis', // Asumsi general
                'auditable_id' => 0, // 0 jika list, ID jika spesifik
                'old_values' => null,
                'new_values' => json_encode(['path' => $request->path()]),
                'url' => $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return $response;
    }
}
