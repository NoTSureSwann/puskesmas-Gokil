<?php

namespace App\Http\Controllers\Farmasi;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DraftResepController extends Controller
{
    public function create()
    {
        // Get active visits that don't have a final Resep or Pembayaran yet
        $kunjungans = \App\Models\Kunjungan::query()
            ->whereIn('status', ['menunggu', 'dipanggil', 'diperiksa'])
            ->doesntHave('resep')
            ->doesntHave('draftResep')
            ->with(['pasien.user', 'poli'])
            ->get();
            
        $obats = \App\Models\Obat::query()->where('is_aktif', true)->orderBy('nama_obat', 'asc')->get();

        return view('farmasi.draft-resep.create', compact('kunjungans', 'obats'));
    }

    public function store(\Illuminate\Http\Request $request)
    {
        $request->validate([
            'kunjungan_id' => 'required|exists:kunjungan,id',
            'obat' => 'required|array|min:1',
            'obat.*.obat_id' => 'required|exists:obat,id',
            'obat.*.jumlah' => 'required|integer|min:1',
            'obat.*.dosis' => 'required|string',
            'obat.*.aturan_pakai' => 'required|string',
            'catatan_farmasi' => 'nullable|string',
        ]);

        $farmasi = \Illuminate\Support\Facades\Auth::user()->profilFarmasi;

        $draft = \Illuminate\Support\Facades\DB::transaction(function () use ($request, $farmasi) {
            $draft = \App\Models\DraftResep::create([
                'kunjungan_id' => $request->kunjungan_id,
                'farmasi_id' => $farmasi->id,
                'catatan_farmasi' => $request->catatan_farmasi,
                'status' => 'draft'
            ]);

            foreach ($request->obat as $item) {
                \App\Models\DetailDraftResep::create([
                    'draft_resep_id' => $draft->id,
                    'obat_id' => $item['obat_id'],
                    'jumlah' => $item['jumlah'],
                    'dosis' => $item['dosis'],
                    'aturan_pakai' => $item['aturan_pakai'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            return $draft;
        });

        return redirect()->route('farmasi.dashboard')->with('status', 'Rekomendasi obat (Draft) berhasil dikirim untuk divalidasi oleh Dokter.');
    }
}
