<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreObatRequest;
use App\Http\Requests\Admin\StorePoliRequest;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateObatRequest;
use App\Http\Requests\Admin\UpdatePoliRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Kunjungan;
use App\Models\LogCetak;
use App\Models\Obat;
use App\Models\Poli;
use App\Models\ProfilDokter;
use App\Models\ProfilFarmasi;
use App\Models\Resep;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

/**
 * Class AdminDashboardController
 * Handles administration panel logic (Poli, Obat, Users, SQLite logs, reports).
 */
class AdminDashboardController extends Controller
{
    /**
     * Tampilkan Dashboard Ringkasan Admin.
     */
    public function index(): View
    {
        $today = Carbon::today();

        $stats = [
            'total_pasien' => User::query()->where('role', 'pasien')->count(),
            'total_dokter' => User::query()->where('role', 'dokter')->count(),
            'total_farmasi' => User::query()->where('role', 'farmasi')->count(),
            'total_poli' => Poli::query()->count(),
            'total_obat' => Obat::query()->count(),
            'kunjungan_hari_ini' => Kunjungan::query()->whereDate('tanggal_kunjungan', $today)->count(),
            'resep_selesai' => Resep::query()->whereDate('created_at', $today)->where('status', 'selesai')->count(),
            'log_cetak_count' => LogCetak::query()->count(), // SQLite Log Count
        ];

        // Obat dengan stok rendah
        $lowStockObats = Obat::stokRendah()->get();

        // Kunjungan hari ini berdasarkan status
        $kunjunganStatus = Kunjungan::query()->whereDate('tanggal_kunjungan', $today)
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status')
            ->all();

        return view('admin.dashboard', compact('stats', 'lowStockObats', 'kunjunganStatus'));
    }

    /**
     * Manajemen Pengguna (Users Index).
     */
    public function usersIndex(Request $request): View
    {
        $role = $request->query('role');
        $query = User::with(['profilDokter', 'profilFarmasi', 'profilPasien']);

        if ($role && in_array($role, ['pasien', 'dokter', 'farmasi', 'admin'])) {
            $query->where('role', $role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);
        return view('admin.users.index', compact('users', 'role'));
    }

    /**
     * Form Tambah Pengguna.
     */
    public function usersCreate(): View
    {
        $polis = Poli::aktif()->get();
        return view('admin.users.create', compact('polis'));
    }

    /**
     * Simpan Pengguna Baru.
     */
    public function usersStore(StoreUserRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'phone' => $request->phone,
                'status' => 'aktif',
                'email_verified_at' => now(), // Verifikasi otomatis oleh admin
            ]);

            if ($user->role === 'dokter') {
                ProfilDokter::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'sip' => $request->sip,
                    'spesialisasi' => $request->spesialisasi,
                    'poli' => $request->poli,
                    'harga_konsultasi' => $request->harga_konsultasi,
                    'jam_kerja' => $request->jam_kerja,
                ]);
            } elseif ($user->role === 'farmasi') {
                ProfilFarmasi::create([
                    'user_id' => $user->id,
                    'nip' => $request->nip,
                    'jabatan' => $request->jabatan,
                ]);
            }
        });

        return redirect()->route('admin.users.index')->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Form Edit Pengguna.
     */
    public function usersEdit(int $id): View
    {
        $user = User::with(['profilDokter', 'profilFarmasi'])->findOrFail($id);
        $polis = Poli::aktif()->get();
        return view('admin.users.edit', compact('user', 'polis'));
    }

    /**
     * Update Pengguna.
     */
    public function usersUpdate(UpdateUserRequest $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        DB::transaction(function () use ($request, $user) {
            $userData = [
                'name' => $request->name,
                'email' => $request->email,
                'phone' => $request->phone,
            ];

            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            if ($user->role === 'dokter') {
                $user->profilDokter()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip' => $request->nip,
                        'sip' => $request->sip,
                        'spesialisasi' => $request->spesialisasi,
                        'poli' => $request->poli,
                        'harga_konsultasi' => $request->harga_konsultasi,
                        'jam_kerja' => $request->jam_kerja,
                    ]
                );
            } elseif ($user->role === 'farmasi') {
                $user->profilFarmasi()->updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'nip' => $request->nip,
                        'jabatan' => $request->jabatan,
                    ]
                );
            }
        });

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil diperbarui.');
    }

    /**
     * Ubah status pengguna (aktif/nonaktif).
     */
    public function usersToggle(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);
        
        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak bisa menonaktifkan akun sendiri.');
        }

        $newStatus = $user->status === 'aktif' ? 'nonaktif' : 'aktif';
        $user->update(['status' => $newStatus]);

        return back()->with('status', "Status pengguna {$user->name} diubah menjadi {$newStatus}.");
    }

    /**
     * Manajemen Poli (Clinic List).
     */
    public function poliIndex(): View
    {
        $polis = Poli::orderBy('kode_poli', 'asc')->paginate(10);
        return view('admin.poli.index', compact('polis'));
    }

    /**
     * Tambah Poli Baru.
     */
    public function poliStore(StorePoliRequest $request): RedirectResponse
    {
        Poli::create([
            'kode_poli' => $request->kode_poli,
            'nama_poli' => $request->nama_poli,
            'deskripsi' => $request->deskripsi,
            'is_aktif' => true,
        ]);

        return redirect()->route('admin.poli.index')->with('status', 'Poli berhasil ditambahkan.');
    }

    /**
     * Update Poli.
     */
    public function poliUpdate(UpdatePoliRequest $request, int $id): RedirectResponse
    {
        $poli = Poli::findOrFail($id);

        $poli->update([
            'nama_poli' => $request->nama_poli,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('admin.poli.index')->with('status', 'Poli berhasil diperbarui.');
    }

    /**
     * Toggle status aktif Poli.
     */
    public function poliToggle(int $id): RedirectResponse
    {
        $poli = Poli::findOrFail($id);
        $poli->update(['is_aktif' => !$poli->is_aktif]);

        return back()->with('status', "Status Poli {$poli->nama_poli} diubah.");
    }

    /**
     * Manajemen Obat (Inventory List).
     */
    public function obatIndex(): View
    {
        $obats = Obat::orderBy('nama_obat', 'asc')->paginate(10);
        return view('admin.obat.index', compact('obats'));
    }

    /**
     * Tambah Obat Baru.
     */
    public function obatStore(StoreObatRequest $request): RedirectResponse
    {
        Obat::create([
            'kode_obat' => $request->kode_obat,
            'nama_obat' => $request->nama_obat,
            'satuan' => $request->satuan,
            'kategori' => $request->kategori,
            'stok' => $request->stok,
            'stok_minimum' => $request->stok_minimum,
            'harga_satuan' => $request->harga_satuan,
            'deskripsi' => $request->deskripsi,
            'is_aktif' => true,
        ]);

        return redirect()->route('admin.obat.index')->with('status', 'Obat berhasil ditambahkan.');
    }

    /**
     * Update Obat.
     */
    public function obatUpdate(UpdateObatRequest $request, int $id): RedirectResponse
    {
        $obat = Obat::findOrFail($id);

        $obat->update([
            'nama_obat' => $request->nama_obat,
            'satuan' => $request->satuan,
            'kategori' => $request->kategori,
            'stok' => $request->stok,
            'stok_minimum' => $request->stok_minimum,
            'harga_satuan' => $request->harga_satuan,
            'deskripsi' => $request->deskripsi,
        ]);

        return redirect()->route('admin.obat.index')->with('status', 'Obat berhasil diperbarui.');
    }

    /**
     * Toggle status aktif Obat.
     */
    public function obatToggle(int $id): RedirectResponse
    {
        $obat = Obat::findOrFail($id);
        $obat->update(['is_aktif' => !$obat->is_aktif]);

        return back()->with('status', "Status Obat {$obat->nama_obat} diubah.");
    }

    /**
     * Laporan Kunjungan.
     */
    public function laporanKunjungan(Request $request): View
    {
        $startDate = $request->query('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->query('end_date', Carbon::today()->format('Y-m-d'));
        $poliId = $request->query('poli_id');

        $query = Kunjungan::with(['pasien.user', 'poli', 'dokter.user'])
            ->whereBetween('tanggal_kunjungan', [$startDate, $endDate]);

        if ($poliId) {
            $query->where('poli_id', $poliId);
        }

        // Hitung total sebelum paginasi
        $totalCount = (clone $query)->count();
        $totalBpjs = (clone $query)->where('jenis_kunjungan', 'bpjs')->count();
        $totalUmum = (clone $query)->where('jenis_kunjungan', 'umum')->count();

        $kunjungans = $query->orderBy('tanggal_kunjungan', 'desc')->orderBy('no_antrian', 'asc')->paginate(15);
        $polis = Poli::aktif()->get();

        return view('admin.laporan.kunjungan', compact(
            'kunjungans', 'polis', 'startDate', 'endDate', 'poliId',
            'totalCount', 'totalBpjs', 'totalUmum'
        ));
    }

    /**
     * Log Cetak Resep (SQLite Audit trail).
     */
    public function laporanCetak(): View
    {
        $logs = LogCetak::orderBy('dicetak_pada', 'desc')->paginate(15);
        return view('admin.laporan.cetak', compact('logs'));
    }

    /**
     * Hapus Pengguna (Soft Delete).
     */
    public function usersDestroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('status', "Pengguna {$user->name} berhasil dihapus.");
    }

    /**
     * Hapus Poli.
     */
    public function poliDestroy(int $id): RedirectResponse
    {
        $poli = Poli::findOrFail($id);

        if ($poli->kunjungan()->exists()) {
            return back()->with('error', "Poli {$poli->nama_poli} tidak dapat dihapus karena sudah memiliki data kunjungan. Silakan nonaktifkan saja.");
        }

        $poli->delete();

        return redirect()->route('admin.poli.index')->with('status', "Poli {$poli->nama_poli} berhasil dihapus.");
    }

    /**
     * Hapus Obat (Soft Delete).
     */
    public function obatDestroy(int $id): RedirectResponse
    {
        $obat = Obat::findOrFail($id);

        // Proteksi: tidak bisa hapus obat yang sudah digunakan dalam resep
        if ($obat->detailResep()->exists()) {
            return back()->with('error', "Obat {$obat->nama_obat} tidak dapat dihapus karena sudah digunakan dalam resep. Silakan nonaktifkan saja.");
        }

        $obat->delete();

        return redirect()->route('admin.obat.index')->with('status', "Obat {$obat->nama_obat} berhasil dihapus.");
    }

    /**
     * Tampilkan daftar dataset keluhan & analisis AI.
     */
    public function laporanAiDataset(Request $request): View
    {
        $datasets = \App\Models\AiDataset::with('kunjungan.pasien.user')
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return view('admin.laporan.ai_dataset', compact('datasets'));
    }

    /**
     * Ekspor dataset AI dalam format json atau csv (log export).
     */
    public function exportAiDataset(string $format)
    {
        $datasets = \App\Models\AiDataset::with('kunjungan.pasien.user')
            ->orderBy('created_at', 'desc')
            ->get();

        if ($format === 'json') {
            $data = $datasets->map(function ($item) {
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

            return response()->json($data, 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
                ->header('Content-Disposition', 'attachment; filename="ai_symptom_dataset.json"');
        } elseif ($format === 'csv') {
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="ai_symptom_dataset.csv"',
            ];

            $callback = function () use ($datasets) {
                $file = fopen('php://output', 'w');
                // CSV header
                fputcsv($file, [
                    'ID', 'No Kunjungan', 'Tanggal Kunjungan', 'Nama Pasien', 
                    'Keluhan', 'Kemungkinan Penyakit', 'Tingkat Urgensi', 
                    'Rekomendasi Poli', 'Saran Tindakan', 'Sudah Dicetak', 'Waktu Cetak', 'Tanggal Dibuat'
                ]);

                foreach ($datasets as $item) {
                    fputcsv($file, [
                        $item->id,
                        $item->kunjungan ? $item->kunjungan->no_kunjungan : '',
                        $item->kunjungan ? $item->kunjungan->tanggal_kunjungan : '',
                        $item->kunjungan && $item->kunjungan->pasien && $item->kunjungan->pasien->user ? $item->kunjungan->pasien->user->name : 'Anonim',
                        $item->keluhan,
                        implode(', ', $item->kemungkinan_penyakit ?? []),
                        $item->tingkat_urgensi,
                        $item->rekomendasi_poli_nama,
                        $item->saran_tindakan,
                        $item->is_printed ? 'Ya' : 'Tidak',
                        $item->dicetak_pada ? $item->dicetak_pada->toDateTimeString() : '',
                        $item->created_at ? $item->created_at->toDateTimeString() : '',
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }

        return abort(404);
    }
}
