@extends('layouts.app')

@section('title', 'Detail Kunjungan - SI Puskesmas & Klinik')

@section('styles')
<style>
    .ticket-badge {
        font-family: var(--font-display);
        font-size: 2.2rem;
        font-weight: 800;
        background: var(--primary-light);
        color: var(--primary);
        width: 100px;
        height: 100px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1rem;
        border: 4px solid white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .status-timeline {
        position: relative;
        padding-left: 2.5rem;
    }
    .status-timeline::before {
        content: '';
        position: absolute;
        top: 0;
        bottom: 0;
        left: 1rem;
        width: 2px;
        background: #e2e8f0;
    }
    .status-step {
        position: relative;
        margin-bottom: 2rem;
    }
    .status-step::before {
        content: '';
        position: absolute;
        left: -2rem;
        top: 4px;
        width: 1rem;
        height: 1rem;
        border-radius: 50%;
        background: #cbd5e1;
        border: 3px solid white;
        box-shadow: 0 0 0 2px #cbd5e1;
    }
    .status-step.active::before {
        background: var(--primary);
        box-shadow: 0 0 0 2px var(--primary);
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
<div class="row g-4 animated-fade my-4">
    <!-- Main Info Column -->
    <div class="col-lg-8">
        <div class="card card-premium p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Kunjungan</h4>
                <a href="{{ route('pasien.riwayat') }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-arrow-left"></i> Riwayat</a>
            </div>

            <!-- Visiting Details Info Panel -->
            <div class="row g-4 mb-4">
                <div class="col-md-6">
                    <span class="text-muted small">Nomor Kunjungan</span>
                    <h5 class="fw-bold text-dark">{{ $kunjungan->no_kunjungan }}</h5>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Poli Tujuan</span>
                    <h5 class="fw-bold text-dark">{{ $kunjungan->poli->nama_poli }}</h5>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Tanggal & Jam Daftar</span>
                    <h6 class="fw-bold text-dark">
                        {{ \Carbon\Carbon::parse($kunjungan->tanggal_kunjungan)->format('d-m-Y') }} 
                        ({{ $kunjungan->jam_daftar->format('H:i') }} WIB)
                    </h6>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Jenis Layanan & Pembayaran</span>
                    <h6 class="fw-bold text-dark text-uppercase">{{ $kunjungan->jenis_kunjungan }}</h6>
                </div>
                <div class="col-12">
                    <span class="text-muted small">Keluhan Utama</span>
                    <p class="bg-light p-3 rounded-3 mt-1 small">{{ $kunjungan->keluhan }}</p>
                </div>
                @if($kunjungan->dokter)
                    <div class="col-md-12">
                        <span class="text-muted small">Dokter Pemeriksa</span>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <i class="fa-solid fa-user-doctor text-primary"></i>
                            <h6 class="fw-bold mb-0">{{ $kunjungan->dokter->user->name }}</h6>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Prescription Details Section -->
            @if ($kunjungan->resep)
                <hr class="my-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-prescription-bottle-medical text-primary me-2"></i> Resep Elektronik</h5>
                    <span class="badge status-badge status-{{ $kunjungan->resep->status }}">{{ $kunjungan->resep->status }}</span>
                </div>
                <p class="small text-muted mb-2">No. Resep: <strong>{{ $kunjungan->resep->no_resep }}</strong></p>
                
                @if($kunjungan->resep->catatan_dokter)
                    <div class="mb-3">
                        <span class="text-muted small">Catatan Dokter:</span>
                        <div class="bg-light p-2 rounded-3 small"><em>"{{ $kunjungan->resep->catatan_dokter }}"</em></div>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle small">
                        <thead class="table-light">
                            <tr>
                                <th>Nama Obat</th>
                                <th class="text-center">Jumlah</th>
                                <th>Aturan Pakai & Dosis</th>
                                <th>Keterangan Tambahan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($kunjungan->resep->detailResep as $detail)
                                <tr>
                                    <td class="fw-semibold">{{ $detail->obat->nama_obat }}</td>
                                    <td class="text-center">{{ $detail->jumlah }} {{ $detail->obat->satuan }}</td>
                                    <td>
                                        <span class="badge bg-success bg-opacity-10 text-success">{{ $detail->dosis }}</span>
                                    </td>
                                    <td>{{ $detail->keterangan ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($kunjungan->resep->status === 'selesai')
                    <div class="alert alert-success alert-premium d-flex gap-3 align-items-center mt-4">
                        <i class="fa-solid fa-circle-check fs-2 text-success"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Obat Siap Diambil!</h6>
                            <span class="small">Silakan tunjukkan nomor resep <strong>{{ $kunjungan->resep->no_resep }}</strong> ke bagian Apotek Puskesmas & Klinik.</span>
                        </div>
                    </div>
                @elseif($kunjungan->resep->status === 'diproses')
                    <div class="alert alert-warning alert-premium d-flex gap-3 align-items-center mt-4">
                        <i class="fa-solid fa-spinner fa-spin fs-2 text-warning"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Resep Sedang Diproses</h6>
                            <span class="small">Petugas apotek sedang meracik dan menyiapkan obat Anda. Mohon tunggu notifikasi selanjutnya.</span>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <!-- Ticket & Status Column -->
    <div class="col-lg-4">
        <!-- Ticket Box -->
        <div class="card card-premium p-4 text-center mb-4">
            <span class="small text-muted text-uppercase fw-semibold mb-2 d-block">Nomor Antrian</span>
            <div class="ticket-badge">{{ str_pad((string)$kunjungan->no_antrian, 3, '0', STR_PAD_LEFT) }}</div>
            <span class="badge status-badge status-{{ $kunjungan->status }} my-2">{{ $kunjungan->status }}</span>
            <span class="text-muted small d-block mt-2">Poli: <strong>{{ $kunjungan->poli->nama_poli }}</strong></span>
            
            <div class="mt-4">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=130x130&data={{ urlencode($kunjungan->no_kunjungan) }}" alt="QR Code" class="img-thumbnail" style="width: 140px; height: 140px;">
                <span class="text-muted d-block small mt-2">Scan untuk check-in / verifikasi</span>
            </div>
            
            @if ($kunjungan->status === 'menunggu')
                <hr class="my-4">
                <form action="{{ route('pasien.kunjungan.batal', $kunjungan->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pendaftaran kunjungan ini?');" class="d-grid">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm"><i class="fa-solid fa-ban me-1"></i> Batalkan Kunjungan</button>
                </form>
            @endif
        </div>

        <!-- Status Flow Timeline -->
        <div class="card card-premium p-4">
            <h5 class="fw-bold mb-4">Alur Pelayanan</h5>
            <div class="status-timeline">
                <div class="status-step {{ in_array($kunjungan->status, ['menunggu', 'dipanggil', 'diperiksa', 'resep', 'selesai']) ? 'active' : '' }}">
                    <h6 class="fw-bold mb-0 text-dark">Pendaftaran Terkonfirmasi</h6>
                    <p class="text-muted small mb-0">Pasien terdaftar di antrian poli klinik tujuan.</p>
                </div>
                <div class="status-step {{ in_array($kunjungan->status, ['dipanggil', 'diperiksa', 'resep', 'selesai']) ? 'active' : '' }}">
                    <h6 class="fw-bold mb-0 text-dark">Panggilan Poli Klinik</h6>
                    <p class="text-muted small mb-0">Nomor dipanggil menuju ruang periksa dokter.</p>
                </div>
                <div class="status-step {{ in_array($kunjungan->status, ['diperiksa', 'resep', 'selesai']) ? 'active' : '' }}">
                    <h6 class="fw-bold mb-0 text-dark">Pemeriksaan Medis</h6>
                    <p class="text-muted small mb-0">Pasien sedang diperiksa oleh dokter.</p>
                </div>
                <div class="status-step {{ in_array($kunjungan->status, ['resep', 'selesai']) ? 'active' : '' }}">
                    <h6 class="fw-bold mb-0 text-dark">Penyiapan Resep Obat</h6>
                    <p class="text-muted small mb-0">Resep dikirim dan diracik di bagian Farmasi.</p>
                </div>
                <div class="status-step {{ $kunjungan->status === 'selesai' ? 'active' : '' }}">
                    <h6 class="fw-bold mb-0 text-dark">Selesai / Pengambilan</h6>
                    <p class="text-muted small mb-0">Obat diserahterimakan ke pasien di loket Apotek.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
