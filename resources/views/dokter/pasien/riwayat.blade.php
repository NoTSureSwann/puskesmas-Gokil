@extends('layouts.app')

@section('title', 'Riwayat Rekam Pasien - SI Puskesmas & Klinik')

@section('content')
<div class="card card-premium p-4 p-md-5 animated-fade my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Rekam Medis Pasien</h3>
            <p class="text-muted mb-0">Memantau kunjungan dan riwayat terapi obat pasien terdahulu.</p>
        </div>
        <a href="{{ route('dokter.dashboard') }}" class="btn btn-outline-primary"><i class="fa-solid fa-house"></i> Dashboard</a>
    </div>

    <!-- Patient Info Summary -->
    <div class="row g-3 p-3 bg-light rounded-3 mb-4">
        <div class="col-md-3">
            <span class="text-muted small d-block">Nama Lengkap</span>
            <strong class="text-dark">{{ $pasien->user->name }}</strong>
        </div>
        <div class="col-md-3">
            <span class="text-muted small d-block">Nomor NIK / BPJS</span>
            <strong class="text-dark">{{ $pasien->nik }} / {{ $pasien->no_bpjs ?? '-' }}</strong>
        </div>
        <div class="col-md-3">
            <span class="text-muted small d-block">Jenis Kelamin / Golongan Darah</span>
            <strong class="text-dark">{{ $pasien->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }} ({{ $pasien->golongan_darah ?? 'Tidak Tahu' }})</strong>
        </div>
        <div class="col-md-3">
            <span class="text-muted small d-block">Riwayat Alergi Obat</span>
            <strong class="text-danger">{{ $pasien->riwayat_alergi ?? 'Tidak Ada' }}</strong>
        </div>
    </div>

    <!-- AI Patient Summary -->
    @if(isset($aiSummary))
    <div class="row g-3 p-3 bg-primary-subtle border-primary border border-2 rounded-3 mb-4">
        <div class="col-12">
            <h5 class="fw-bold text-primary mb-2"><i class="fa-solid fa-robot me-2"></i> KBot AI Patient Summary</h5>
            <p class="mb-0 text-dark" style="font-size: 0.95rem;">{{ $aiSummary }}</p>
        </div>
    </div>
    @endif

    <!-- Timeline Visits -->
    <h5 class="fw-bold mb-4"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Daftar Kunjungan Selesai</h5>
    
    @forelse ($riwayats as $riw)
        <div class="border rounded-3 p-3 p-md-4 mb-3 bg-white">
            <div class="d-flex justify-content-between flex-wrap gap-2 align-items-center border-bottom pb-2 mb-3">
                <div>
                    <span class="badge bg-secondary me-2">Kunjungan</span>
                    <strong class="text-dark me-3">{{ $riw->no_kunjungan }}</strong>
                    <span class="text-muted small"><i class="fa-solid fa-calendar me-1"></i> Tanggal: {{ \Carbon\Carbon::parse($riw->tanggal_kunjungan)->format('d-m-Y') }}</span>
                </div>
                <div class="small text-muted">
                    Poli Klinik: <strong>{{ $riw->poli->nama_poli }}</strong> | Dokter: <strong>{{ $riw->dokter->user->name }}</strong>
                </div>
            </div>

            <div class="mb-3">
                <span class="fw-semibold text-dark small d-block mb-1">Keluhan Pasien:</span>
                <p class="text-muted small mb-0">{{ $riw->keluhan }}</p>
            </div>

            <!-- Resep details -->
            @if ($riw->resep)
                <div class="mt-3 p-3 bg-light rounded-3 border-start border-4 border-success">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <strong class="small text-success"><i class="fa-solid fa-prescription-bottle-medical"></i> Resep Elektronik ({{ $riw->resep->no_resep }})</strong>
                        <span class="text-muted small" style="font-size: 0.75rem;">Status: {{ strtoupper($riw->resep->status) }}</span>
                    </div>
                    
                    @if($riw->resep->catatan_dokter)
                        <div class="mb-2">
                            <span class="text-muted small">Catatan Diagnosa Dokter:</span>
                            <div class="small font-italic"><em>"{{ $riw->resep->catatan_dokter }}"</em></div>
                        </div>
                    @endif

                    <ul class="mb-0 small ps-3">
                        @foreach ($riw->resep->detailResep as $detail)
                            <li>
                                <strong>{{ $detail->obat->nama_obat }}</strong> - {{ $detail->jumlah }} {{ $detail->obat->satuan }} ({{ $detail->dosis }} - {{ $detail->aturan_pakai }})
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    @empty
        <div class="text-center py-5 text-muted">
            <i class="fa-solid fa-folder-open display-6 mb-2"></i>
            <h6>Belum ada riwayat kunjungan yang selesai untuk pasien ini.</h6>
        </div>
    @endforelse
</div>
@endsection
