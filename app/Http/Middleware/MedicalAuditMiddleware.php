<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\AuditLog;

class MedicalAuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Hanya catat jika route name cocok dengan akses medis sensitif
        $routeName = $request->route() ? $request->route()->getName() : null;
        
        $sensitiveRoutes = [
            'dokter.pasien.riwayat',
            'pasien.jurnal.download',
            'telemedicine.room',
        ];

        if ($routeName && in_array($routeName, $sensitiveRoutes) && Auth::check()) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'Accessed Sensitive Medical Record',
                'details' => 'Route: ' . $routeName . ' | URL: ' . $request->fullUrl(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        return $response;
    }
}
