@extends('layouts.app')

@section('title', 'Dashboard Pasien - SI Puskesmas & Klinik')

@section('styles')
<style>
    .queue-number-box {
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-hover) 100%);
        color: white;
        border-radius: 16px;
        padding: 2rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 8px 20px rgba(16, 185, 129, 0.15);
    }
    .status-badge {
        padding: 0.35rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .status-menunggu { background-color: #fef3c7; color: #d97706; }
    .status-dipanggil { background-color: #ecfdf5; color: #059669; }
    .status-diperiksa { background-color: #e0f2fe; color: #0284c7; }
    .status-resep { background-color: #f3e8ff; color: #7c3aed; }
    .status-selesai { background-color: #d1fae5; color: #065f46; }
    .status-batal { background-color: #fee2e2; color: #991b1b; }
</style>
@endsection

@section('content')
<div x-data="{ loaded: true }">
    <!-- Welcome Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4" x-data="{ shown: false }" x-intersect="shown = true" :class="shown ? 'animated-fade' : 'opacity-0'">
        <div>
            <h2 class="fw-bold mb-1 fs-3 fs-md-2">Selamat Datang, <br class="d-block d-sm-none">{{ $user->name }}</h2>
            <p class="text-muted mb-0 small">Kelola antrian kunjungan puskesmas dan lacak resep obat Anda.</p>
        </div>
        <div class="d-flex gap-2 w-100 w-md-auto">
            <button type="button" class="btn btn-danger text-white pulse-animation" data-bs-toggle="modal" data-bs-target="#ambulansModal">
                <i class="fa-solid fa-truck-medical me-2"></i> Darurat Ambulans
            </button>
            <a href="{{ route('pasien.daftar') }}" class="btn btn-primary text-white flex-grow-1"><i class="fa-solid fa-calendar-plus me-2 icon-bounce-hover"></i> Daftar Baru</a>
        </div>
    </div>

    <!-- Modal Ambulans -->
    <div class="modal fade" id="ambulansModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header bg-danger text-white border-0 pb-3 rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-truck-medical me-2"></i> Panggilan Ambulans Darurat</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('pasien.ambulans.call') }}" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <div class="alert alert-warning mb-4">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i> Gunakan layanan ini hanya dalam kondisi <strong>darurat medis</strong>. Petugas kami akan memprioritaskan panggilan ini.
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Alamat Penjemputan</label>
                            <textarea name="alamat_jemput" class="form-control bg-light" rows="3" required>{{ $pasien->alamat ? $pasien->alamat . ', ' . $pasien->kelurahan . ', ' . $pasien->kecamatan : '' }}</textarea>
                            <div class="form-text">Ubah jika lokasi jemput berbeda dengan alamat terdaftar.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">No. Telepon Aktif</label>
                            <input type="text" name="no_telepon" class="form-control bg-light" value="{{ $user->phone }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Keluhan Darurat (Opsional)</label>
                            <textarea name="keluhan_darurat" class="form-control bg-light" rows="2" placeholder="Contoh: Sesak napas hebat, pendarahan, dll."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0 p-4">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-danger fw-bold"><i class="fa-solid fa-phone-volume me-2"></i> Panggil Ambulans Sekarang</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Patient Info Card -->
    <div class="row g-4 mb-4" x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'animated-fade' : 'opacity-0'">
        <div class="col-lg-8">
            <div class="card card-premium p-4 h-100">
                <div class="row g-3 align-items-center">
                    <div class="col-md-2 text-center d-none d-md-block">
                        <i class="fa-solid fa-address-card text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <div class="col-md-10">
                        <h5 class="fw-bold mb-3">Informasi Pasien</h5>
                        <div class="row g-3">
                            <div class="col-6 col-sm-6">
                                <span class="text-muted small d-block">Nomor NIK</span>
                                <strong class="text-dark">{{ $pasien->nik }}</strong>
                            </div>
                            <div class="col-6 col-sm-6">
                                <span class="text-muted small d-block">Nomor BPJS</span>
                                <strong class="text-dark">{{ $pasien->no_bpjs ?? 'Tidak Ada' }}</strong>
                            </div>
                            <div class="col-6 col-sm-6">
                                <span class="text-muted small d-block">Tipe Pasien</span>
                                <span class="badge {{ $pasien->jenis_pasien === 'bpjs' ? 'bg-primary' : 'bg-secondary' }} px-3 py-1 rounded-pill mt-1">
                                    {{ strtoupper($pasien->jenis_pasien) }}
                                </span>
                            </div>
                            <div class="col-6 col-sm-6">
                                <span class="text-muted small d-block">Golongan Darah</span>
                                <strong class="text-dark">{{ $pasien->golongan_darah ?? 'Tidak Tahu' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Notifications Card -->
        <div class="col-lg-4">
            <div class="card card-premium p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-bell text-warning me-2"></i> Notifikasi</h5>
                    @if($notifikasis->count() > 0)
                        <form action="{{ route('pasien.notifikasi.read') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm text-decoration-none p-0">Tandai Dibaca</button>
                        </form>
                    @endif
                </div>
                <div class="overflow-y-auto" style="max-height: 140px;">
                    @forelse($notifikasis as $notif)
                        <div class="p-2 border-bottom small mb-1">
                            <div class="fw-semibold">{{ $notif->data['message'] ?? 'Pemberitahuan Baru' }}</div>
                            <div class="text-muted mt-1" style="font-size: 0.75rem;">{{ $notif->created_at->diffForHumans() }}</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 small">
                            <i class="fa-solid fa-bell-slash d-block fs-3 mb-2 text-slate-300"></i>
                            Tidak ada notifikasi baru.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Active Queue Status -->
    <div class="card card-premium p-4 mb-4" x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'animated-fade' : 'opacity-0'">
        <h5 class="fw-bold mb-4"><i class="fa-solid fa-ticket text-primary me-2 icon-spin-hover"></i> Antrian Aktif Hari Ini</h5>
        
        @if ($antrianAktif)
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <div class="queue-number-box pulse-animation">
                        <span class="small text-white-50 text-uppercase fw-semibold tracking-wider mb-1">Nomor Antrian</span>
                        <span class="display-3 fw-bold my-1">{{ str_pad((string)$antrianAktif->no_antrian, 3, '0', STR_PAD_LEFT) }}</span>
                        <span class="badge status-badge status-{{ $antrianAktif->status }} bg-white mt-2">{{ $antrianAktif->status }}</span>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <span class="text-muted small">No. Kunjungan</span>
                            <h6 class="fw-bold mb-0">{{ $antrianAktif->no_kunjungan }}</h6>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small">Poli Tujuan</span>
                            <h6 class="fw-bold mb-0">{{ $antrianAktif->poli->nama_poli }}</h6>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small">Waktu Daftar</span>
                            <h6 class="fw-bold mb-0">{{ $antrianAktif->jam_daftar->format('H:i') }} WIB</h6>
                        </div>
                        <div class="col-sm-6">
                            <span class="text-muted small">Tanggal Kunjungan</span>
                            <h6 class="fw-bold mb-0">{{ $antrianAktif->tanggal_kunjungan->format('d-m-Y') }}</h6>
                        </div>
                    </div>
                    <hr class="my-4">
                    <div class="d-flex flex-column flex-sm-row flex-wrap gap-2">
                        <a href="{{ route('pasien.kunjungan', $antrianAktif->id) }}" class="btn btn-outline-primary"><i class="fa-solid fa-circle-info me-2 icon-bounce-hover"></i> Lihat Detail Kunjungan</a>
                        @if($antrianAktif->metode_kunjungan === 'telemedisin' && in_array($antrianAktif->status, ['menunggu', 'dipanggil', 'diperiksa']))
                            <a href="{{ route('pasien.kunjungan.telemedisin', $antrianAktif->id) }}" class="btn btn-success"><i class="fa-solid fa-video me-2 icon-bounce-hover"></i> Buka Ruang Telemedisin</a>
                        @endif
                        @if($antrianAktif->status === 'menunggu')
                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fa-solid fa-ban me-2 icon-bounce-hover"></i> Batalkan Kunjungan
                            </button>
                            
                            <!-- Cancel Modal -->
                            <div class="modal fade" id="cancelModal" tabindex="-1" aria-hidden="true">
                              <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow-lg">
                                  <div class="modal-header border-0 pb-0">
                                    <h5 class="modal-title fw-bold text-danger"><i class="fa-solid fa-triangle-exclamation me-2"></i> Konfirmasi Pembatalan</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                  </div>
                                  <div class="modal-body">
                                    Apakah Anda yakin ingin membatalkan antrian kunjungan ini? Tindakan ini tidak dapat dibatalkan.
                                  </div>
                                  <div class="modal-footer border-0 pt-0">
                                    <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">Tutup</button>
                                    <form action="{{ route('pasien.kunjungan.batal', $antrianAktif->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-danger text-white fw-medium"><i class="fa-solid fa-check me-2"></i> Ya, Batalkan</button>
                                    </form>
                                  </div>
                                </div>
                              </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <div class="text-center py-5">
                <i class="fa-solid fa-folder-open text-muted display-4 mb-3"></i>
                <h5 class="text-muted">Tidak ada antrian aktif untuk hari ini.</h5>
                <p class="text-muted small mb-4">Silakan lakukan pendaftaran kunjungan online baru untuk mendapatkan nomor antrian.</p>
                <a href="{{ route('pasien.daftar') }}" class="btn btn-primary text-white"><i class="fa-solid fa-calendar-plus me-2"></i> Daftar Kunjungan Baru</a>
            </div>
        @endif
    </div>

    <!-- History Table (5 visits) -->
    <div class="card card-premium p-4" x-data="{ shown: false }" x-intersect.once="shown = true" :class="shown ? 'animated-fade' : 'opacity-0'">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2 icon-spin-hover"></i> Riwayat Kunjungan Terakhir</h5>
            <a href="{{ route('pasien.riwayat') }}" class="text-primary text-decoration-none small fw-semibold">Lihat Semua <i class="fa-solid fa-arrow-right ms-1 icon-bounce-hover"></i></a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr class="text-muted small">
                        <th>Tanggal</th>
                        <th>No. Kunjungan</th>
                        <th>Poli Tujuan</th>
                        <th>Dokter Pemeriksa</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayatKunjungan as $kunj)
                        <tr>
                            <td class="fw-medium small">{{ \Carbon\Carbon::parse($kunj->tanggal_kunjungan)->format('d-m-Y') }}</td>
                            <td class="small fw-semibold text-slate-700">{{ $kunj->no_kunjungan }}</td>
                            <td>{{ $kunj->poli->nama_poli }}</td>
                            <td class="small">{{ $kunj->dokter ? $kunj->dokter->user->name : '-' }}</td>
                            <td>
                                <span class="badge status-badge status-{{ $kunj->status }}">{{ $kunj->status }}</span>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailModal{{ $kunj->id }}">
                                    <i class="fa-solid fa-eye icon-bounce-hover"></i> Detail
                                </button>
                                
                                <!-- Detail Modal -->
                                <div class="modal fade" id="detailModal{{ $kunj->id }}" tabindex="-1" aria-hidden="true">
                                  <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content rounded-4 border-0 shadow">
                                      <div class="modal-header border-bottom-0 pb-0">
                                        <h5 class="modal-title fw-bold"><i class="fa-solid fa-notes-medical text-primary me-2"></i> Detail Kunjungan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                      </div>
                                      <div class="modal-body text-start">
                                        <div class="mb-3">
                                            <span class="small text-muted d-block">Nomor Kunjungan</span>
                                            <span class="fw-bold fs-5">{{ $kunj->no_kunjungan }}</span>
                                        </div>
                                        <div class="row g-3 mb-3">
                                            <div class="col-6">
                                                <span class="small text-muted d-block">Poli Tujuan</span>
                                                <span class="fw-semibold">{{ $kunj->poli->nama_poli }}</span>
                                            </div>
                                            <div class="col-6">
                                                <span class="small text-muted d-block">Status</span>
                                                <span class="badge status-badge status-{{ $kunj->status }}">{{ $kunj->status }}</span>
                                            </div>
                                            <div class="col-12">
                                                <span class="small text-muted d-block">Dokter Pemeriksa</span>
                                                <span class="fw-semibold">{{ $kunj->dokter ? $kunj->dokter->user->name : 'Belum Ditentukan' }}</span>
                                            </div>
                                        </div>
                                      </div>
                                      <div class="modal-footer border-top-0 pt-0">
                                        <a href="{{ route('pasien.kunjungan', $kunj->id) }}" class="btn btn-primary w-100 text-white"><i class="fa-solid fa-arrow-up-right-from-square me-2"></i> Lihat Halaman Lengkap</a>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat kunjungan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
</div>
@endsection

@section('scripts')
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // Inisialisasi Reverb (Pusher-compatible) untuk listening realtime update antrian/kunjungan
    const reverbAppKey = "{{ env('REVERB_APP_KEY') }}";
    const reverbHost = "{{ env('REVERB_HOST', 'localhost') }}";
    const reverbPort = {{ env('REVERB_PORT', 8080) }};
    const reverbScheme = "{{ env('REVERB_SCHEME', 'http') }}";
    const activeKunjunganId = "{{ $antrianAktif->id ?? '' }}";
    
    if (reverbAppKey && activeKunjunganId) {
        try {
            const pusher = new Pusher(reverbAppKey, {
                wsHost: reverbHost,
                wsPort: reverbPort,
                wssPort: reverbPort,
                forceTLS: (reverbScheme === 'https'),
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1' // Wajib diisi meskipun Reverb tidak memakainya
            });

            const channel = pusher.subscribe('kunjungan-channel');
            channel.bind('App\\Events\\KunjunganUpdated', function(data) {
                console.log('Update Antrian Real-time:', data);
                
                // Jika ID kunjungan yang diupdate cocok dengan kunjungan aktif pasien
                if (data.id == activeKunjunganId) {
                    // Play sound alert
                    try {
                        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
                        audio.play();
                    } catch(e) { console.log(e); }
                    
                    // Reload dashboard secara otomatis untuk memperbarui status antrian
                    setTimeout(() => {
                        window.location.reload();
                    }, 1200);
                }
            });
        } catch (e) {
            console.error('Gagal memuat Pusher untuk pasien:', e);
        }
    }
</script>
@endsection
