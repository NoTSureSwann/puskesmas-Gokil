<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pasien;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TagihanController extends Controller
{
    /**
     * Tampilkan riwayat tagihan pasien.
     */
    public function index(): View
    {
        $pasien = Auth::user()->pasien;
        if (!$pasien) {
            abort(403, 'Profil pasien tidak ditemukan. Silakan hubungi admin.');
        }
        $pasienId = $pasien->id;
        
        // Optimasi: O(1) Cache retrieval, mencegah O(N) re-query ke database setiap reload
        $page = request()->query('page', 1);
        $cacheKey = "tagihan_pasien_{$pasienId}_page_{$page}";
        
        $tagihans = \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(10), function() use ($pasienId) {
            return Pembayaran::query()
                ->whereHas('kunjungan', function($q) use ($pasienId) {
                    $q->where('pasien_id', $pasienId);
                })
                // Optimasi: Eager Loading untuk mencegah N+1 Query Problem O(N^2)
                ->with(['kunjungan.poli', 'kunjungan.dokter.user'])
                ->latest()
                ->paginate(10);
        });

        return view('pasien.tagihan.index', compact('tagihans'));
    }

    /**
     * Tampilkan detail invoice dan metode pembayaran.
     */
    public function show($id): View
    {
        $pasien = Auth::user()->pasien;
        if (!$pasien) {
            abort(403, 'Profil pasien tidak ditemukan. Silakan hubungi admin.');
        }
        $pasienId = $pasien->id;

        $tagihan = Pembayaran::query()
            ->whereHas('kunjungan', function($q) use ($pasienId) {
                $q->where('pasien_id', $pasienId);
            })
            ->with(['kunjungan.poli', 'kunjungan.resep.detailResep.obat'])
            ->findOrFail($id);

        return view('pasien.tagihan.show', compact('tagihan'));
    }

    /**
     * Simulasi pelunasan (Mock Payment Gateway).
     */
    public function simulatePayment(Request $request, $id)
    {
        $pasien = Auth::user()->pasien;
        if (!$pasien) {
            abort(403, 'Profil pasien tidak ditemukan. Silakan hubungi admin.');
        }
        $pasienId = $pasien->id;

        $tagihan = Pembayaran::query()
            ->whereHas('kunjungan', function($q) use ($pasienId) {
                $q->where('pasien_id', $pasienId);
            })
            ->findOrFail($id);

        if ($tagihan->status_pembayaran === 'paid') {
            return back()->with('error', 'Tagihan ini sudah lunas.');
        }

        // Mock payment process
        $tagihan->update([
            'status_pembayaran' => 'paid',
            'metode_pembayaran' => $request->metode_pembayaran ?? 'transfer_bank',
            'waktu_pembayaran' => now(),
        ]);

        return redirect()->route('pasien.tagihan.index')->with('status', 'Pembayaran berhasil dikonfirmasi. Terima kasih!');
    }
}
