<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\PanggilanAmbulans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmbulansController extends Controller
{
    /**
     * Store a new ambulance call request.
     */
    public function call(Request $request)
    {
        $request->validate([
            'alamat_jemput' => ['required', 'string', 'max:500'],
            'no_telepon' => ['required', 'string', 'max:20'],
            'keluhan_darurat' => ['nullable', 'string', 'max:1000'],
        ]);

        $pasien = Auth::user()->profilPasien;
        
        if (!$pasien) {
            return back()->with('error', 'Profil pasien tidak ditemukan. Silakan lengkapi profil terlebih dahulu.');
        }

        // Cek apakah ada panggilan yang masih aktif
        $activeCall = PanggilanAmbulans::where('pasien_id', $pasien->id)
            ->whereIn('status', ['menunggu', 'dijemput'])
            ->first();

        if ($activeCall) {
            return back()->with('error', 'Anda sudah memiliki permintaan ambulans yang sedang diproses.');
        }

        PanggilanAmbulans::create([
            'pasien_id' => $pasien->id,
            'alamat_jemput' => $request->alamat_jemput,
            'no_telepon' => $request->no_telepon,
            'keluhan_darurat' => $request->keluhan_darurat,
            'status' => 'menunggu',
        ]);

        return back()->with('status', 'Permintaan ambulans darurat berhasil dikirim! Petugas kami akan segera menghubungi Anda.');
    }
}
