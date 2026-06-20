<?php

declare(strict_types=1);

namespace App\Http\Controllers\Farmasi;

use App\Events\KunjunganUpdated;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Resep;
use App\Models\User;
use App\Notifications\ResepSelesaiNotification;
use App\Notifications\StokObatRendahNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Class ResepFarmasiController
 * Handles processing of Electronic Prescriptions by Pharmacists.
 */
class ResepFarmasiController extends Controller
{
    /**
     * Tampilkan detail resep untuk diproses.
     */
    public function showProcessForm(int $id): View
    {
        $resep = Resep::with(['kunjungan.pasien.user', 'detailResep.obat'])->findOrFail($id);
        $obats = \App\Models\Obat::query()->where('is_aktif', true)->orderBy('nama_obat', 'asc')->get();
        return view('farmasi.resep.process', compact('resep', 'obats'));
    }

    /**
     * Tambahkan obat baru ke resep (Farmasi).
     */
    public function addResepItem(Request $request, int $id): RedirectResponse
    {
        $resep = Resep::findOrFail($id);
        
        if ($resep->status === 'selesai') {
            return back()->with('error', 'Resep sudah selesai diproses, tidak bisa diedit.');
        }

        $request->validate([
            'obat_id' => 'required|exists:obat,id',
            'jumlah' => 'required|integer|min:1',
            'dosis' => 'required|string|max:50',
            'aturan_pakai' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $resep->detailResep()->create([
            'obat_id' => $request->obat_id,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
            'aturan_pakai' => $request->aturan_pakai,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('status', 'Obat berhasil ditambahkan ke resep.');
    }

    /**
     * Update detail obat pada resep (Farmasi).
     */
    public function updateResepItem(Request $request, int $id, int $detailId): RedirectResponse
    {
        $resep = Resep::findOrFail($id);
        
        if ($resep->status === 'selesai') {
            return back()->with('error', 'Resep sudah selesai diproses, tidak bisa diedit.');
        }

        $request->validate([
            'obat_id' => 'required|exists:obat,id',
            'jumlah' => 'required|integer|min:1',
            'dosis' => 'required|string|max:50',
            'aturan_pakai' => 'required|string|max:100',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $detail = $resep->detailResep()->findOrFail($detailId);
        $detail->update([
            'obat_id' => $request->obat_id,
            'jumlah' => $request->jumlah,
            'dosis' => $request->dosis,
            'aturan_pakai' => $request->aturan_pakai,
            'keterangan' => $request->keterangan,
        ]);

        return back()->with('status', 'Detail obat berhasil diperbarui.');
    }

    /**
     * Hapus obat dari resep (Farmasi).
     */
    public function deleteResepItem(int $id, int $detailId): RedirectResponse
    {
        $resep = Resep::findOrFail($id);
        
        if ($resep->status === 'selesai') {
            return back()->with('error', 'Resep sudah selesai diproses, tidak bisa diedit.');
        }

        $detail = $resep->detailResep()->findOrFail($detailId);
        $detail->delete();

        return back()->with('status', 'Obat berhasil dihapus dari resep.');
    }

    /**
     * Proses mengubah status resep menjadi 'diproses'.
     */
    public function process(int $id): RedirectResponse
    {
        $resep = Resep::findOrFail($id);
        $resep->update(['status' => 'diproses']);

        return redirect()->route('farmasi.resep.showProcess', $resep->id)
            ->with('status', 'Resep dengan nomor ' . $resep->no_resep . ' sedang diproses.');
    }

    /**
     * Selesaikan pemrosesan resep, kurangi stok, dan kirim notifikasi.
     */
    public function selesai(Request $request, int $id): RedirectResponse
    {
        $resep = Resep::query()->with(['kunjungan.pasien.user', 'detailResep.obat'])->findOrFail($id);

        if ($resep->status === 'selesai') {
            return redirect()->route('farmasi.dashboard')->with('error', 'Resep ini sudah selesai diproses.');
        }

        // Validasi stok obat cukup sebelum proses
        foreach ($resep->detailResep as $detail) {
            if ($detail->obat->stok < $detail->jumlah) {
                return back()->with('error', "Stok obat {$detail->obat->nama_obat} tidak mencukupi (tersisa: {$detail->obat->stok}, dibutuhkan: {$detail->jumlah}). Hubungi Admin untuk restok.");
            }
        }

        // Jalankan transaksi database
        DB::transaction(function () use ($resep) {
            // 1. Kurangi stok obat & kirim peringatan jika stok rendah
            foreach ($resep->detailResep as $detail) {
                $obat = $detail->obat;
                $obat->stok -= $detail->jumlah;
                $obat->save();

                // Cek jika stok obat menyentuh atau di bawah stok_minimum
                if ($obat->stok <= $obat->stok_minimum) {
                    $admins = User::query()->where('role', 'admin')->where('status', 'aktif')->get();
                    foreach ($admins as $admin) {
                        /** @var \App\Models\User $admin */
                        $admin->notify(new StokObatRendahNotification($obat));
                    }
                }
            }

            // 2. Update status resep
            $resep->update([
                'status' => 'selesai',
                'jam_selesai_farmasi' => now(),
            ]);

            // 3. Update status kunjungan terkait
            $kunjungan = $resep->kunjungan;
            $kunjungan->update([
                'status' => 'selesai',
                'jam_selesai' => now(),
            ]);
        });

        // Broadcast KunjunganUpdated event for real-time queue syncing
        event(new KunjunganUpdated($resep->kunjungan));

        // 4. Kirim notifikasi / email ke pasien
        $patientUser = $resep->kunjungan->pasien->user;
        $patientUser->notify(new ResepSelesaiNotification($resep));

        // 5. Redirect ke cetak struk etiket PDF
        return redirect()->route('farmasi.resep.cetak', $resep->id)
            ->with('status', 'Resep berhasil diselesaikan! Mengalihkan ke cetak struk...');
    }

    /**
     * API: Cek interaksi obat menggunakan AI (Groq LLM).
     */
    public function cekInteraksi(Request $request, int $id, \App\Services\AIEngineService $aiEngine)
    {
        $resep = Resep::with(['kunjungan.pasien', 'detailResep.obat'])->findOrFail($id);
        
        $obatList = $resep->detailResep->map(function ($detail) {
            return $detail->obat->nama_obat;
        })->toArray();

        if (count($obatList) === 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Belum ada obat dalam resep ini.'
            ], 400);
        }

        $alergiPasien = $resep->kunjungan->pasien->riwayat_alergi;

        $result = $aiEngine->checkDrugInteraction($obatList, $alergiPasien);

        if (!$result) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memanggil AI Engine. Pastikan API Key valid.'
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data' => $result
        ]);
    }
}
