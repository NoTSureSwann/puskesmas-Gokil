@extends('layouts.app')

@section('title', 'Dashboard Admin - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Admin Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Panel Administrasi</h2>
            <p class="text-muted mb-0">Kelola master data, pengguna, dan laporan performa sistem.</p>
        </div>
    </div>

    <!-- Admin Sub-Navigation -->
    <div class="card card-premium shadow-sm mb-4 p-3">
        <div class="nav nav-pills card-header-pills flex-column flex-md-row gap-2">
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-gauge me-1"></i> Ringkasan
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.users.index') }}">
                <i class="fa-solid fa-users me-1"></i> Kelola Pengguna
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.poli.index') }}">
                <i class="fa-solid fa-clinic-medical me-1"></i> Kelola Poli
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.obat.index') }}">
                <i class="fa-solid fa-pills me-1"></i> Kelola Obat
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.laporan.kunjungan') }}">
                <i class="fa-solid fa-file-medical me-1"></i> Laporan Kunjungan
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.laporan.cetak') }}">
                <i class="fa-solid fa-print me-1"></i> Audit Cetak Struk (SQLite)
            </a>
        </div>
    </div>

    <!-- Statistics Cards Row -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-premium text-center p-3">
                <span class="text-muted small">Total Pasien</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total_pasien'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-premium text-center p-3">
                <span class="text-muted small">Total Dokter</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total_dokter'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-premium text-center p-3">
                <span class="text-muted small">Poli & Kategori</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total_poli'] }}</h3>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card card-premium text-center p-3">
                <span class="text-muted small">Jenis Obat</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['total_obat'] }}</h3>
            </div>
        </div>
    </div>

    <!-- Second Stats Row (Daily transactions) -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card card-premium text-center p-3 border-start border-4 border-warning">
                <span class="text-muted small">Kunjungan Terdaftar Hari Ini</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['kunjungan_hari_ini'] }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-premium text-center p-3 border-start border-4 border-success">
                <span class="text-muted small">Resep Selesai Hari Ini</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['resep_selesai'] }}</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-premium text-center p-3 border-start border-4 border-primary">
                <span class="text-muted small">Total Log Cetak PDF (SQLite)</span>
                <h3 class="fw-bold text-dark mb-0 mt-1">{{ $stats['log_cetak_count'] }}</h3>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Low Stock Alerts -->
        <div class="col-lg-6">
            <div class="card card-premium shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-triangle-exclamation text-danger me-2"></i> Peringatan Stok Obat Rendah</h5>
                <p class="text-muted small">Daftar obat yang stoknya saat ini berada di bawah batas minimum pengaman.</p>

                @if ($lowStockObats->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fa-solid fa-circle-check text-success fs-2 mb-2"></i>
                        <p class="mb-0 small">Semua obat memiliki stok yang cukup.</p>
                    </div>
                @else
                    <div class="table-responsive" style="max-height: 280px; overflow-y: auto;">
                        <table class="table table-sm table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Obat</th>
                                    <th class="text-center">Stok</th>
                                    <th class="text-center">Min</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($lowStockObats as $obat)
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-dark small">{{ $obat->nama_obat }}</div>
                                            <code class="small text-muted">{{ $obat->kode_obat }}</code>
                                        </td>
                                        <td class="text-center fw-bold text-danger">{{ $obat->stok }}</td>
                                        <td class="text-center text-muted small">{{ $obat->stok_minimum }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.obat.index') }}" class="btn btn-xs btn-outline-primary py-0 px-2 small">Update</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Today's Queue Status Chart/Summary -->
        <div class="col-lg-6">
            <div class="card card-premium shadow-sm p-4 h-100">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-hospital-user text-primary me-2"></i> Distribusi Antrian Hari Ini</h5>
                <p class="text-muted small">Status kunjungan pasien yang melakukan pendaftaran hari ini.</p>

                <div class="list-group list-group-flush mt-2">
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-hourglass-start text-warning me-2"></i> Menunggu Pemanggilan</span>
                        <span class="badge bg-warning text-dark rounded-pill fw-bold">{{ $kunjunganStatus['menunggu'] ?? 0 }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-bullhorn text-primary me-2"></i> Dipanggil ke Poli</span>
                        <span class="badge bg-primary rounded-pill fw-bold">{{ $kunjunganStatus['dipanggil'] ?? 0 }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-stethoscope text-info me-2"></i> Sedang Diperiksa</span>
                        <span class="badge bg-info text-white rounded-pill fw-bold">{{ $kunjunganStatus['diperiksa'] ?? 0 }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-receipt text-secondary me-2"></i> Menunggu Resep</span>
                        <span class="badge bg-secondary rounded-pill fw-bold">{{ $kunjunganStatus['resep'] ?? 0 }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <span><i class="fa-solid fa-circle-check text-success me-2"></i> Selesai Layanan</span>
                        <span class="badge bg-success rounded-pill fw-bold">{{ $kunjunganStatus['selesai'] ?? 0 }}</span>
                    </div>
                    <div class="list-group-item d-flex justify-content-between align-items-center border-bottom-0">
                        <span><i class="fa-solid fa-ban text-danger me-2"></i> Dibatalkan</span>
                        <span class="badge bg-danger rounded-pill fw-bold">{{ $kunjunganStatus['batal'] ?? 0 }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
