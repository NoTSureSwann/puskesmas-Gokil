<?php

declare(strict_types=1);

namespace App\Http\Controllers\Dokter;

use App\Events\KunjunganUpdated;
use App\Http\Controllers\Controller;
use App\Models\Kunjungan;
use App\Models\Poli;
use App\Models\ProfilPasien;
use App\Models\Resep;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Class DokterDashboardController
 * Handles actions and views for Doctor role.
 */
class DokterDashboardController extends Controller
{
    /**
     * Tampilkan halaman utama/dashboard Dokter.
     */
    public function index(): View|RedirectResponse
    {
        $user = Auth::user();
        $dokter = $user->profilDokter;

        if (!$dokter) {
            return redirect()->route('home')->with('error', 'Profil Dokter Anda belum terdaftar.');
        }

        $today = Carbon::today();

        // 1. Stats Bar
        $totalPasienHariIni = Kunjungan::query()
            ->where('dokter_id', $dokter->id)
            ->whereDate('tanggal_kunjungan', $today)->count();

        $menungguCount = Kunjungan::query()
            ->where('dokter_id', $dokter->id)
            ->whereDate('tanggal_kunjungan', $today)->where('status', 'menunggu')->count();

        $selesaiCount = Kunjungan::query()
            ->where('dokter_id', $dokter->id)
            ->whereDate('tanggal_kunjungan', $today)->where('status', 'selesai')->count();

        // 2. Daftar Antrian Pasien Hari Ini (Menunggu, Dipanggil, Diperiksa, Resep)
        $antrians = Kunjungan::query()
            ->where('dokter_id', $dokter->id)
            ->whereDate('tanggal_kunjungan', $today)
            ->whereIn('status', ['menunggu', 'dipanggil', 'diperiksa', 'resep'])
            ->with('pasien.user')
            ->orderBy('no_antrian', 'asc')
            ->get();

        // 3. Resep yang Dibuat Hari Ini oleh Dokter Ini (Limit 5)
        $resepsHariIni = Resep::query()->where('dokter_id', $dokter->id)
            ->whereDate('created_at', $today)
            ->with(['kunjungan.pasien.user'])
            ->latest()
            ->take(5)
            ->get();

        return view('dokter.dashboard', compact(
            'user', 'dokter', 'totalPasienHariIni', 'menungguCount', 'selesaiCount', 'antrians', 'resepsHariIni'
        ));
    }

    /**
     * Aksi memanggil pasien ke ruangan.
     */
    public function panggil(int $id): RedirectResponse
    {
        $user = Auth::user();
        $dokter = $user->profilDokter;
        $kunjungan = Kunjungan::query()->where('status', 'menunggu')->findOrFail($id);

        // Validasi ownership: kunjungan harus milik dokter ini
        if ($kunjungan->dokter_id !== $dokter->id) {
            abort(403, 'Anda tidak memiliki akses ke kunjungan ini.');
        }

        $kunjungan->update([
            'status' => 'dipanggil',
            'jam_panggil' => now(),
        ]);

        // Dispatch Event untuk sinkronisasi real-time
        try {
            event(new KunjunganUpdated($kunjungan));
        } catch (\Exception $e) {
            // Ignore broadcasting errors (e.g. Pusher not configured)
            \Illuminate\Support\Facades\Log::warning('Pusher Broadcast Error: ' . $e->getMessage());
        }

        return redirect()->route('dokter.dashboard')
            ->with('status', 'Pasien nomor antrian ' . $kunjungan->no_antrian . ' dipanggil.');
    }

    /**
     * Aksi memulai pemeriksaan pasien.
     */
    public function periksa(int $id): RedirectResponse
    {
        $user = Auth::user();
        $dokter = $user->profilDokter;
        $kunjungan = Kunjungan::query()->where('status', 'dipanggil')->findOrFail($id);

        // Validasi ownership: kunjungan harus milik dokter ini
        if ($kunjungan->dokter_id !== $dokter->id) {
            abort(403, 'Anda tidak memiliki akses ke kunjungan ini.');
        }

        $kunjungan->update(['status' => 'diperiksa']);

        // Dispatch Event untuk sinkronisasi real-time
        try {
            event(new KunjunganUpdated($kunjungan));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::warning('Pusher Broadcast Error: ' . $e->getMessage());
        }

        return redirect()->route('dokter.dashboard')
            ->with('status', 'Pemeriksaan dimulai untuk pasien ' . $kunjungan->pasien->user->name . '.');
    }

    /**
     * Tampilkan riwayat lengkap pemeriksaan pasien berdasarkan NIK.
     */
    public function showPasienHistory(string $nik): View
    {
        $pasien = ProfilPasien::query()->where('nik', $nik)->firstOrFail();
        
        $riwayats = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->whereIn('status', ['selesai'])
            ->with(['poli', 'dokter.user', 'resep.detailResep.obat'])
            ->latest()
            ->get();

        $aiSummary = null;
        if ($riwayats->isNotEmpty() && env('GROQ_API_KEY')) {
            $aiEngine = app(\App\Services\AIEngineService::class);
            $aiSummary = $aiEngine->summarizePatientHistory($pasien, $riwayats);
        }

        return view('dokter.pasien.riwayat', compact('pasien', 'riwayats', 'aiSummary'));
    }

    /**
     * Akses halaman ruang Telemedisin untuk Dokter
     */
    public function telemedisinRoom(int $id): View|RedirectResponse
    {
        $dokter = Auth::user()->profilDokter;
        $kunjungan = Kunjungan::query()->where('dokter_id', $dokter->id)
            ->where('metode_kunjungan', 'telemedisin')
            ->findOrFail($id);

        if (!$kunjungan->telemedisin_room) {
            return redirect()->route('dokter.dashboard')->with('error', 'Ruang telemedisin belum tersedia.');
        }

        return view('telemedicine.room', compact('kunjungan'));
    }

    /**
     * Tampilkan form profil dokter.
     */
    public function showProfil(): View
    {
        $user = Auth::user();
        $dokter = $user->profilDokter;

        return view('dokter.profil', compact('user', 'dokter'));
    }

    /**
     * Update profil dokter.
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $dokter = $user->profilDokter;

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^[0-9]{10,15}$/'],
            'nip' => ['nullable', 'string', 'numeric', 'unique:profil_dokter,nip,' . $dokter->id],
            'sip' => ['nullable', 'string', 'max:50'],
            'spesialisasi' => ['required', 'string', 'max:100'],
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        $dokter->update([
            'nip' => $request->nip,
            'sip' => $request->sip,
            'spesialisasi' => $request->spesialisasi,
        ]);

        return redirect()->route('dokter.profil')
            ->with('status', 'Profil Dokter berhasil diperbarui.');
    }
}
