@extends('layouts.app')

@section('title', 'Proses Resep - SI Puskesmas & Klinik')

@section('styles')
<style>
    .patient-info-card {
        border-top: 4px solid var(--primary);
    }
    .status-badge {
        font-family: var(--font-display);
        font-weight: 600;
        font-size: 0.85rem;
    }
    .medicine-table th {
        font-family: var(--font-display);
        font-weight: 600;
        background-color: #f8fafc;
    }
</style>
@endsection

@section('content')
<div class="animated-fade">
    <div class="mb-4">
        <a href="{{ route('farmasi.dashboard') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>

    <div class="row g-4">
        <!-- Patient Info Column -->
        <div class="col-lg-4">
            <div class="card card-premium patient-info-card shadow-sm p-4">
                <h4 class="fw-bold mb-3 border-bottom pb-2">Informasi Pasien</h4>
                
                <div class="mb-3">
                    <span class="text-muted small d-block">Nomor Resep</span>
                    <strong class="text-dark fs-5">{{ $resep->no_resep }}</strong>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Nama Lengkap</span>
                    <span class="fw-bold text-dark">{{ $resep->kunjungan->pasien->user->name }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">NIK / BPJS</span>
                    <span class="text-dark">
                        {{ $resep->kunjungan->pasien->nik }} 
                        @if ($resep->kunjungan->pasien->no_bpjs)
                            / <span class="badge bg-success-subtle text-success border border-success-subtle">{{ $resep->kunjungan->pasien->no_bpjs }}</span>
                        @endif
                    </span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Metode Pembayaran</span>
                    <span class="badge {{ $resep->kunjungan->jenis_kunjungan === 'bpjs' ? 'bg-success text-white' : 'bg-primary text-white' }} text-capitalize">
                        {{ $resep->kunjungan->jenis_kunjungan }}
                    </span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Alergi</span>
                    <span class="text-danger fw-bold">{{ $resep->kunjungan->pasien->riwayat_alergi ?: 'Tidak Ada' }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Dokter Pengirim</span>
                    <span class="text-dark fw-medium">{{ $resep->dokter->user->name }}</span>
                    <span class="text-muted small d-block">{{ $resep->kunjungan->poli->nama_poli }}</span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Prioritas Resep</span>
                    <span class="badge {{ $resep->prioritas === 'urgen' ? 'bg-danger text-white' : 'bg-secondary text-white' }} text-uppercase">
                        {{ $resep->prioritas }}
                    </span>
                </div>

                <div class="mb-3">
                    <span class="text-muted small d-block">Catatan Dokter</span>
                    <p class="text-dark bg-light p-2 rounded small border mb-0">{{ $resep->catatan_dokter ?: '-' }}</p>
                </div>
            </div>
        </div>

        <!-- Prescription Details Column -->
        <div class="col-lg-8">
            <div class="card card-premium shadow-sm p-4">
                <h4 class="fw-bold mb-4 border-bottom pb-2">Rincian Obat & Verifikasi Stok</h4>

                @php
                    $canProcess = true;
                @endphp

                <div class="table-responsive">
                    <table class="table table-hover align-middle medicine-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Obat</th>
                                <th class="text-center">Jumlah Resep</th>
                                <th class="text-center">Stok Apotek</th>
                                <th>Satuan</th>
                                <th>Dosis & Aturan</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resep->detailResep as $detail)
                                @php
                                    $stokCukup = $detail->obat->stok >= $detail->jumlah;
                                    if (!$stokCukup) {
                                        $canProcess = false;
                                    }
                                @endphp
                                <tr>
                                    <td><code>{{ $detail->obat->kode_obat }}</code></td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $detail->obat->nama_obat }}</div>
                                        <div class="text-muted small">{{ $detail->obat->kategori }}</div>
                                    </td>
                                    <td class="text-center fw-bold text-primary">{{ $detail->jumlah }}</td>
                                    <td class="text-center fw-bold {{ $stokCukup ? 'text-success' : 'text-danger' }}">
                                        {{ $detail->obat->stok }}
                                    </td>
                                    <td>{{ $detail->obat->satuan }}</td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $detail->dosis }}</div>
                                        <div class="small text-muted">{{ $detail->aturan_pakai }}</div>
                                        @if ($detail->keterangan)
                                            <div class="small text-secondary italic">Note: {{ $detail->keterangan }}</div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($stokCukup)
                                            <span class="badge bg-success-subtle text-success status-badge border border-success-subtle">
                                                <i class="fa-solid fa-circle-check me-1"></i> Cukup
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger status-badge border border-danger-subtle">
                                                <i class="fa-solid fa-circle-xmark me-1"></i> Kurang
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if (!$canProcess)
                    <div class="alert alert-danger alert-premium d-flex align-items-start mt-3" role="alert">
                        <i class="fa-solid fa-circle-exclamation me-3 fs-4 mt-1"></i>
                        <div>
                            <h6 class="fw-bold mb-1">Stok Obat Tidak Mencukupi!</h6>
                            <span>Stok obat di apotek kurang dari jumlah yang diresepkan dokter. Pemrosesan resep ditangguhkan. Silakan hubungi dokter atau perbarui stok melalui dashboard admin.</span>
                        </div>
                    </div>
                @endif

                <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                    @if ($resep->status === 'diproses' && $canProcess)
                        <form action="{{ route('farmasi.resep.selesai', $resep->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success text-white fw-bold px-4 py-2">
                                <i class="fa-solid fa-check-double me-2"></i> Selesaikan & Cetak Struk
                            </button>
                        </form>
                    @elseif ($resep->status === 'menunggu' && $canProcess)
                        <form action="{{ route('farmasi.resep.start', $resep->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary text-white fw-bold px-4 py-2">
                                <i class="fa-solid fa-gears me-2"></i> Mulai Proses Resep
                            </button>
                        </form>
                    @else
                        <button type="button" class="btn btn-success fw-bold px-4 py-2" disabled>
                            <i class="fa-solid fa-ban me-2"></i> Tidak Bisa Diproses
                        </button>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
