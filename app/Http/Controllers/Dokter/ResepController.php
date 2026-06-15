<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dokter;

use App\Events\KunjunganUpdated;
use App\Events\ResepBaru;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dokter\StoreResepRequest;
use App\Mail\NotifikasiResepUrgenMail;
use App\Models\DetailResep;
use App\Models\Kunjungan;
use App\Models\Obat;
use App\Models\Resep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Class ResepController
 * Handles electronic prescriptions (E-Prescription) for Doctors.
 */
class ResepController extends Controller
{
    /**
     * Tampilkan form pembuatan resep obat baru.
     */
    public function create(int $kunjunganId): View|RedirectResponse
    {
        $dokter = Auth::user()->profilDokter;
        
        $kunjungan = Kunjungan::query()->where('status', 'diperiksa')
            ->with(['pasien.user'])
            ->findOrFail($kunjunganId);

        // Validasi ownership: kunjungan harus di poli milik dokter ini
        $poliDokter = \App\Models\Poli::query()->where('nama_poli', $dokter->poli)->first();
        if (!$poliDokter || $kunjungan->poli_id !== $poliDokter->id) {
            abort(403, 'Anda tidak memiliki akses ke kunjungan di poli ini.');
        }

        // Mengambil obat aktif untuk pilihan input
        $obats = Obat::aktif()->get();

        return view('dokter.resep.create', compact('kunjungan', 'obats'));
    }

    /**
     * Simpan resep obat baru ke database (dengan database transaction).
     */
    public function store(StoreResepRequest $request): RedirectResponse
    {
        $dokter = Auth::user()->profilDokter;

        $kunjungan = Kunjungan::query()->where('status', 'diperiksa')->findOrFail($request->kunjungan_id);

        // Security: Mismatch / Double Submission Data Prevention
        if (Resep::query()->where('kunjungan_id', $kunjungan->id)->exists() || \App\Models\Pembayaran::query()->where('kunjungan_id', $kunjungan->id)->exists()) {
            return back()->withInput()->with('error', 'Security Alert: Resep atau Tagihan untuk kunjungan ini sudah diterbitkan sebelumnya. Mencegah duplikasi data (Mismatch).');
        }

        // Validasi stok obat cukup sebelum simpan
        foreach ($request->obat as $item) {
            $obat = Obat::findOrFail($item['obat_id']);
            if ($obat->stok < $item['jumlah']) {
                return back()->withInput()->with('error', "Stok obat {$obat->nama_obat} tidak mencukupi (tersisa: {$obat->stok}, diminta: {$item['jumlah']}).");
            }
        }

        $resep = DB::transaction(function () use ($request, $dokter, $kunjungan) {
            // 1. Generate no_resep (Format: RSP-YYYYMMDD-XXXX)
            $today = Carbon::today();
            $dateStr = $today->format('Ymd');
            $dateStart = $today->copy()->startOfDay();
            $dateEnd = $today->copy()->endOfDay();

            $countToday = Resep::query()
                ->where('created_at', '>=', $dateStart)
                ->where('created_at', '<=', $dateEnd)
                ->count();
            $sequence = str_pad((string)($countToday + 1), 4, '0', STR_PAD_LEFT);
            $noResep = "RSP-{$dateStr}-{$sequence}";

            // 2. Simpan Resep
            $resep = Resep::create([
                'kunjungan_id' => $kunjungan->id,
                'dokter_id' => $dokter->id,
                'no_resep' => $noResep,
                'catatan_dokter' => $request->catatan_dokter,
                'prioritas' => $request->prioritas,
                'status' => 'menunggu',
                'jam_input_resep' => now(),
            ]);

            // 3. Simpan Detail Resep & Hitung Biaya Obat
            $biayaObat = 0;
            foreach ($request->obat as $item) {
                $obatModel = Obat::query()->where('id', $item['obat_id'])->first();
                if ($obatModel) {
                    $biayaObat += ($obatModel->harga_satuan * $item['jumlah']);
                }

                DetailResep::create([
                    'resep_id' => $resep->id,
                    'obat_id' => $item['obat_id'],
                    'jumlah' => $item['jumlah'],
                    'dosis' => $item['dosis'],
                    'aturan_pakai' => $item['aturan_pakai'],
                    'keterangan' => $item['keterangan'] ?? null,
                ]);
            }

            // 4. Generate Tagihan Pembayaran Otomatis
            $biayaKonsultasi = $dokter->harga_konsultasi ?? 0;
            \App\Models\Pembayaran::create([
                'kunjungan_id' => $kunjungan->id,
                'kode_pembayaran' => 'PAY-' . strtoupper(uniqid()),
                'biaya_konsultasi' => $biayaKonsultasi,
                'biaya_obat' => $biayaObat,
                'metode_pembayaran' => 'kasir',
                'status_pembayaran' => 'pending',
            ]);

            // 4. Update status kunjungan menjadi 'resep'
            $kunjungan->update(['status' => 'resep']);

            return $resep;
        });

        // 5. Broadcast Event ResepBaru via Pusher/WebSockets
        broadcast(new ResepBaru($resep))->toOthers();

        // Broadcast KunjunganUpdated event for real-time queue syncing
        event(new KunjunganUpdated($kunjungan));

        // 6. Jika prioritas Urgen, kirim email notifikasi ke admin (Head of Clinic)
        if ($resep->prioritas === 'urgen') {
            $admins = User::query()->where('role', 'admin')->where('status', 'aktif')->pluck('email')->toArray();
            if (!empty($admins)) {
                Mail::to($admins)->send(new NotifikasiResepUrgenMail($resep));
            }
        }

        return redirect()->route('dokter.dashboard')
            ->with('status', 'Resep dengan nomor ' . $resep->no_resep . ' telah berhasil dikirim ke Farmasi.');
    }

    /**
     * Tampilkan detail resep yang dibuat.
     */
    public function show(int $id): View
    {
        $dokter = Auth::user()->profilDokter;
        $resep = Resep::query()->where('dokter_id', $dokter->id)
            ->with(['kunjungan.pasien.user', 'detailResep.obat'])
            ->findOrFail($id);

        return view('dokter.resep.show', compact('resep'));
    }

    /**
     * Tampilkan riwayat lengkap semua resep buatan dokter ini.
     */
    public function riwayatResep(): View
    {
        $dokter = Auth::user()->profilDokter;
        $reseps = Resep::query()->where('dokter_id', $dokter->id)
            ->with(['kunjungan.pasien.user'])
            ->latest()
            ->paginate(15);

        return view('dokter.resep.index', compact('reseps'));
    }

    /**
     * API Endpoint untuk autocomplete pencarian obat.
     */
    public function searchObat(Request $request): \Illuminate\Http\JsonResponse
    {
        $query = $request->get('q', '');
        $obats = Obat::aktif()
            ->where(function($q) use ($query) {
                $q->where('nama_obat', 'LIKE', '%' . $query . '%')
                  ->orWhere('kode_obat', 'LIKE', '%' . $query . '%');
            })
            ->take(10)
            ->get(['id', 'kode_obat', 'nama_obat', 'satuan', 'stok', 'harga_satuan']);

        return response()->json($obats);
    }
}
