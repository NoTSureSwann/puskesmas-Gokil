<?php

declare(strict_types=1);

namespace App\Http\Controllers\Pasien;

use App\Events\KunjunganUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Pasien\StoreKunjunganRequest;
use App\Models\AiDataset;
use App\Models\Kunjungan;
use App\Models\Poli;
use App\Models\ProfilPasien;
use App\Notifications\AntrianDigitalNotification;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

/**
 * Class PasienDashboardController
 * Handles actions and views for Patient role.
 */
class PasienDashboardController extends Controller
{
    /**
     * Tampilkan halaman utama/dashboard Pasien.
     */
    public function index(): View
    {
        $user = Auth::user();
        $pasien = $user->profilPasien;

        // Cari antrian aktif hari ini
        $antrianAktif = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->whereDate('tanggal_kunjungan', Carbon::today())
            ->whereIn('status', ['menunggu', 'dipanggil', 'diperiksa', 'resep'])
            ->first();

        // Riwayat 5 kunjungan terakhir
        $riwayatKunjungan = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->with(['poli', 'dokter.user'])
            ->latest()
            ->take(5)
            ->get();

        // Notifikasi database
        $notifikasis = $user->unreadNotifications;

        return view('pasien.dashboard', compact('user', 'pasien', 'antrianAktif', 'riwayatKunjungan', 'notifikasis'));
    }

    /**
     * Tampilkan form pendaftaran kunjungan baru.
     */
    public function showDaftarForm(): View|RedirectResponse
    {
        $pasien = Auth::user()->profilPasien;

        // Pastikan profil pasien sudah diisi lengkap
        if (!$pasien) {
            return redirect()->route('pasien.profil')->with('error', 'Silakan lengkapi profil Anda terlebih dahulu.');
        }

        $polis = Poli::query()->where('is_aktif', true)->get();
        $today = Carbon::today()->format('Y-m-d');
        $dokters = \App\Models\User::query()->where('role', 'dokter')
            ->where('status', 'aktif')
            ->with('profilDokter')
            ->get();

        return view('pasien.daftar', compact('pasien', 'polis', 'today', 'dokters'));
    }

    /**
     * Proses submit pendaftaran kunjungan (antrian).
     */
    public function daftar(StoreKunjunganRequest $request): RedirectResponse
    {
        $pasien = Auth::user()->profilPasien;

        // Cek jika sudah terdaftar di poli yang sama pada hari yang sama
        $exist = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->where('poli_id', $request->poli_id)
            ->whereDate('tanggal_kunjungan', Carbon::parse($request->tanggal_kunjungan))
            ->whereIn('status', ['menunggu', 'dipanggil', 'diperiksa', 'resep'])
            ->first();

        if ($exist) {
            return back()->with('error', 'Anda sudah mendaftar di poli ini pada tanggal tersebut. Silakan tunggu giliran atau pilih poli lain.');
        }

        // Simpan kunjungan
        $kunjungan = Kunjungan::create([
            'pasien_id' => $pasien->id,
            'poli_id' => $request->poli_id,
            'dokter_id' => null, // Ditentukan oleh poli/pemeriksa nanti
            'loket_user_id' => null, // Online self-registration
            'tanggal_kunjungan' => $request->tanggal_kunjungan,
            'keluhan' => $request->keluhan,
            'status' => 'menunggu',
            'jenis_kunjungan' => $request->jenis_kunjungan,
            'metode_kunjungan' => $request->metode_kunjungan,
            'telemedisin_room' => $request->metode_kunjungan === 'telemedisin' ? md5(uniqid('room_', true)) : null,
            'jam_daftar' => now(),
        ]);

        // Kirim Notifikasi & Email
        Auth::user()->notify(new AntrianDigitalNotification($kunjungan));

        // Simpan ke ai_datasets jika analisis AI telah dijalankan
        if ($request->input('ai_run') === '1') {
            $kemungkinanPenyakit = json_decode($request->input('ai_kemungkinan_penyakit'), true) ?? [];
            AiDataset::create([
                'kunjungan_id' => $kunjungan->id,
                'keluhan' => $request->keluhan,
                'kemungkinan_penyakit' => $kemungkinanPenyakit,
                'tingkat_urgensi' => $request->input('ai_tingkat_urgensi'),
                'rekomendasi_poli_nama' => $request->input('ai_rekomendasi_poli_nama'),
                'saran_tindakan' => $request->input('ai_saran_tindakan'),
            ]);

            // Sync ke JSON file
            self::syncDatasetToJsonFile();
        }

        return redirect()->route('pasien.kunjungan', $kunjungan->id)
            ->with('status', 'Pendaftaran antrian berhasil! Kartu antrian Anda telah dikirim via email.');
    }

    /**
     * Tampilkan riwayat kunjungan pasien (paginated).
     */
    public function riwayat(Request $request)
    {
        if ($request->ajax()) {
            $pasien = Auth::user()->profilPasien;
            $kunjungans = Kunjungan::query()->where('pasien_id', $pasien->id)
                ->with(['poli', 'dokter.user'])
                ->select('kunjungan.*');

            return app('datatables')->of($kunjungans)
                ->addColumn('tanggal', function ($row) {
                    return Carbon::parse($row->tanggal_kunjungan)->format('d/m/Y');
                })
                ->addColumn('poli_nama', function ($row) {
                    return $row->poli->nama_poli ?? '-';
                })
                ->addColumn('dokter_nama', function ($row) {
                    return $row->dokter ? 'Dr. ' . $row->dokter->user->name : '-';
                })
                ->addColumn('status_badge', function ($row) {
                    $badges = [
                        'menunggu' => 'bg-warning text-dark',
                        'dipanggil' => 'bg-info text-dark',
                        'diperiksa' => 'bg-primary',
                        'resep' => 'bg-success',
                        'selesai' => 'bg-secondary',
                        'batal' => 'bg-danger'
                    ];
                    $class = $badges[$row->status] ?? 'bg-secondary';
                    return '<span class="badge ' . $class . '">' . ucfirst($row->status) . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('pasien.kunjungan', $row->id) . '" class="btn btn-sm btn-outline-primary mb-1"><i class="fa-solid fa-eye"></i> Detail</a>';
                    if (in_array($row->status, ['diperiksa', 'resep', 'selesai'])) {
                        $btn .= ' <a href="' . route('jurnal.download', $row->id) . '" class="btn btn-sm btn-outline-success mb-1" target="_blank"><i class="fa-solid fa-file-pdf"></i> Jurnal PDF</a>';
                    }
                    return '<div class="d-flex gap-1 flex-wrap">' . $btn . '</div>';
                })
                ->rawColumns(['status_badge', 'action'])
                ->make(true);
        }

        return view('pasien.riwayat');
    }

    /**
     * Tampilkan detail kunjungan (dan resep jika ada).
     */
    public function showKunjungan(int $id): View
    {
        $pasien = Auth::user()->profilPasien;
        $kunjungan = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->with(['poli', 'dokter.user', 'resep.detailResep.obat'])
            ->findOrFail($id);

        return view('pasien.kunjungan', compact('kunjungan'));
    }

    /**
     * Akses halaman ruang Telemedisin
     */
    public function telemedisinRoom(int $id): View|RedirectResponse
    {
        $pasien = Auth::user()->profilPasien;
        $kunjungan = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->where('metode_kunjungan', 'telemedisin')
            ->findOrFail($id);

        if (!$kunjungan->telemedisin_room) {
            return redirect()->route('pasien.kunjungan', $id)->with('error', 'Ruang telemedisin belum tersedia.');
        }

        return view('telemedicine.room', compact('kunjungan'));
    }

    /**
     * Proses membatalkan kunjungan antrian.
     */
    public function batalKunjungan(int $id): RedirectResponse
    {
        $pasien = Auth::user()->profilPasien;
        $kunjungan = Kunjungan::query()->where('pasien_id', $pasien->id)
            ->where('status', 'menunggu')
            ->findOrFail($id);

        $kunjungan->update(['status' => 'batal']);

        // Broadcast KunjunganUpdated event for real-time queue syncing
        event(new KunjunganUpdated($kunjungan));

        return redirect()->route('pasien.dashboard')
            ->with('status', 'Kunjungan nomor ' . $kunjungan->no_kunjungan . ' berhasil dibatalkan.');
    }

    /**
     * Tampilkan halaman edit profil pasien.
     */
    public function showProfil(): View
    {
        $user = Auth::user();
        $pasien = $user->profilPasien;

        return view('pasien.profil', compact('user', 'pasien'));
    }

    /**
     * Proses update profil pasien.
     */
    public function updateProfil(Request $request): RedirectResponse
    {
        $user = Auth::user();
        $pasien = $user->profilPasien;

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^08[0-9]{8,13}$/'],
            'nik' => ['required', 'string', 'numeric', 'digits:16', 'unique:profil_pasien,nik,' . $pasien->id],
            'no_bpjs' => ['nullable', 'string', 'numeric', 'digits:13', 'unique:profil_pasien,no_bpjs,' . $pasien->id],
            'no_kk' => ['nullable', 'string', 'numeric', 'digits:16'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'tempat_lahir' => ['required', 'string', 'max:100'],
            'alamat' => ['required', 'string'],
            'kelurahan' => ['required', 'string', 'max:100'],
            'kecamatan' => ['required', 'string', 'max:100'],
            'jenis_pasien' => ['required', 'in:umum,bpjs'],
            'riwayat_alergi' => ['nullable', 'string'],
            'golongan_darah' => ['nullable', 'in:A,B,AB,O,Tidak Tahu'],
            'tinggi_badan' => ['nullable', 'integer', 'min:10', 'max:300'],
            'berat_badan' => ['nullable', 'integer', 'min:1', 'max:500'],
        ]);

        // Update User
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        // Update ProfilPasien
        $pasien->update([
            'nik' => $request->nik,
            'no_bpjs' => $request->no_bpjs,
            'no_kk' => $request->no_kk,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
            'tempat_lahir' => $request->tempat_lahir,
            'alamat' => $request->alamat,
            'kelurahan' => $request->kelurahan,
            'kecamatan' => $request->kecamatan,
            'jenis_pasien' => $request->jenis_pasien,
            'riwayat_alergi' => $request->riwayat_alergi,
            'golongan_darah' => $request->golongan_darah,
            'tinggi_badan' => $request->tinggi_badan,
            'berat_badan' => $request->berat_badan,
        ]);

        return redirect()->route('pasien.profil')
            ->with('status', 'Profil Anda berhasil diperbarui.');
    }

    /**
     * Bersihkan notifikasi pasien.
     */
    public function markNotificationsAsRead(): RedirectResponse
    {
        Auth::user()->unreadNotifications->markAsRead();
        return back()->with('status', 'Semua notifikasi ditandai telah dibaca.');
    }

    /**
     * Analisis Keluhan Penyakit (menggunakan Groq API - LLaMA)
     */
    public function analyzeSymptoms(Request $request)
    {
        $request->validate([
            'keluhan' => 'required|string'
        ]);

        $keluhan = $request->keluhan;
        
        $apiKey = env('GROQ_API_KEY');
        $model = env('GROQ_MODEL', 'llama-3.3-70b-versatile');

        if (!$apiKey) {
            return response()->json([
                'status' => 'error',
                'message' => 'API Key Groq belum dikonfigurasi.'
            ], 500);
        }

        $systemPrompt = "Anda adalah AI Asisten Dokter di Puskesmas. Tugas Anda menganalisis keluhan pasien dan memberikan saran terstruktur dalam format JSON.
Format JSON yang diharapkan:
{
  \"kemungkinan_penyakit\": [\"Nama Penyakit 1\", \"Nama Penyakit 2\"],
  \"kode_poli\": \"PL-UMM\",
  \"tingkat_urgensi\": \"Rendah\" | \"Sedang\" | \"Tinggi\",
  \"saran_tindakan\": \"Saran medis awal untuk pasien di rumah\"
}
Daftar kode_poli yang tersedia: 
PL-UMM (Poli Umum), PL-GGI (Poli Gigi), PL-DLM (Poli Penyakit Dalam), PL-ANK (Poli Anak), PL-OBG (Poli Kandungan), PL-BDH (Poli Bedah), PL-SRF (Poli Saraf).
Pilih SATU kode_poli yang paling tepat. Jangan memberikan penjelasan tambahan, HANYA kembalikan JSON valid.";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $systemPrompt],
                    ['role' => 'user', 'content' => "Keluhan: " . $keluhan]
                ],
                'temperature' => 0.2,
                'response_format' => ['type' => 'json_object']
            ]);

            if ($response->successful()) {
                $content = $response->json('choices.0.message.content');
                $result = json_decode($content, true);

                if (json_last_error() === JSON_ERROR_NONE && isset($result['kode_poli'])) {
                    $poli = Poli::query()->where('kode_poli', '=', $result['kode_poli'], 'and')->first();
                    
                    return response()->json([
                        'status' => 'success',
                        'data' => [
                            'kemungkinan_penyakit' => $result['kemungkinan_penyakit'] ?? ['Gejala Non-Spesifik'],
                            'rekomendasi_poli_id' => $poli ? $poli->id : null,
                            'rekomendasi_poli_nama' => $poli ? $poli->nama_poli : 'Poli Umum',
                            'tingkat_urgensi' => $result['tingkat_urgensi'] ?? 'Rendah',
                            'saran_tindakan' => $result['saran_tindakan'] ?? 'Segera periksakan ke dokter.'
                        ]
                    ], 200, [], JSON_UNESCAPED_UNICODE);
                }
            } else {
                Log::error('Groq API Error: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Groq Exception: ' . $e->getMessage());
        }

        // Fallback jika API gagal
        $poliFallback = Poli::query()->where('kode_poli', '=', 'PL-UMM', 'and')->first();
        return response()->json([
            'status' => 'success',
            'data' => [
                'kemungkinan_penyakit' => ['Gejala Spesifik Belum Dapat Dianalisis'],
                'rekomendasi_poli_id' => $poliFallback ? $poliFallback->id : null,
                'rekomendasi_poli_nama' => 'Poli Umum',
                'tingkat_urgensi' => 'Sedang',
                'saran_tindakan' => 'Sistem AI sedang sibuk. Silakan lanjutkan pendaftaran ke Poli Umum untuk pemeriksaan langsung.'
            ]
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Sinkronisasi database ai_datasets ke file storage JSON
     */
    public static function syncDatasetToJsonFile(): void
    {
        try {
            $data = AiDataset::with('kunjungan.pasien.user')
                ->latest()
                ->get()
                ->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'kunjungan_no' => $item->kunjungan ? $item->kunjungan->no_kunjungan : null,
                        'tanggal_kunjungan' => $item->kunjungan ? $item->kunjungan->tanggal_kunjungan : null,
                        'pasien_nama' => $item->kunjungan && $item->kunjungan->pasien && $item->kunjungan->pasien->user ? $item->kunjungan->pasien->user->name : 'Anonim',
                        'keluhan' => $item->keluhan,
                        'kemungkinan_penyakit' => $item->kemungkinan_penyakit,
                        'tingkat_urgensi' => $item->tingkat_urgensi,
                        'rekomendasi_poli_nama' => $item->rekomendasi_poli_nama,
                        'saran_tindakan' => $item->saran_tindakan,
                        'is_printed' => $item->is_printed,
                        'dicetak_pada' => $item->dicetak_pada ? $item->dicetak_pada->toIso8601String() : null,
                        'created_at' => $item->created_at ? $item->created_at->toIso8601String() : null,
                    ];
                });

            \Illuminate\Support\Facades\Storage::put('ai_dataset.json', json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Exception $e) {
            Log::error('Sync AI Dataset to JSON error: ' . $e->getMessage());
        }
    }
}
