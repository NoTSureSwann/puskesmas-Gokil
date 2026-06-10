@extends('layouts.app')

@section('title', 'Laporan Kunjungan - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Admin Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Laporan Kunjungan Pasien</h2>
        <p class="text-muted mb-0">Cari, filter, dan rekap kunjungan pasien berdasarkan rentang tanggal dan poli.</p>
    </div>

    <!-- Admin Sub-Navigation -->
    <div class="card card-premium shadow-sm mb-4 p-3">
        <div class="nav nav-pills card-header-pills flex-column flex-md-row gap-2">
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.dashboard') }}">
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
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('admin.laporan.kunjungan') }}">
                <i class="fa-solid fa-file-medical me-1"></i> Laporan Kunjungan
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.laporan.cetak') }}">
                <i class="fa-solid fa-print me-1"></i> Audit Cetak Struk (SQLite)
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card card-premium shadow-sm p-4 mb-4">
        <h5 class="fw-bold mb-3 border-bottom pb-2">Filter Data Kunjungan</h5>
        
        <form action="{{ route('admin.laporan.kunjungan') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="start_date" class="form-label fw-bold small">Tanggal Mulai</label>
                <input type="date" name="start_date" id="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            
            <div class="col-md-3">
                <label for="end_date" class="form-label fw-bold small">Tanggal Akhir</label>
                <input type="date" name="end_date" id="end_date" class="form-control" value="{{ $endDate }}">
            </div>

            <div class="col-md-4">
                <label for="poli_id" class="form-label fw-bold small">Poli / Klinik</label>
                <select name="poli_id" id="poli_id" class="form-select">
                    <option value="">Semua Poli</option>
                    @foreach ($polis as $pl)
                        <option value="{{ $pl->id }}" {{ $poliId == $pl->id ? 'selected' : '' }}>{{ $pl->nama_poli }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2 d-grid">
                <button type="submit" class="btn btn-primary text-white"><i class="fa-solid fa-filter me-1"></i> Terapkan</button>
            </div>
        </form>
    </div>

    <!-- Visit Report Cards Summary -->
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="bg-white p-3 rounded-3 shadow-sm border text-center">
                <span class="text-muted small">Total Kunjungan Periode</span>
                <h4 class="fw-bold mb-0 text-dark">{{ $totalCount }}</h4>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="bg-white p-3 rounded-3 shadow-sm border border-success text-center">
                <span class="text-muted small text-success">Kunjungan BPJS</span>
                <h4 class="fw-bold mb-0 text-success">{{ $totalBpjs }}</h4>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="bg-white p-3 rounded-3 shadow-sm border border-primary text-center">
                <span class="text-muted small text-primary">Kunjungan Umum</span>
                <h4 class="fw-bold mb-0 text-primary">{{ $totalUmum }}</h4>
            </div>
        </div>
    </div>

    <!-- Results Table -->
    <div class="card card-premium shadow-sm p-4">
        <h5 class="fw-bold mb-3 border-bottom pb-2">Rincian Kunjungan</h5>
        
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>No. Kunjungan</th>
                        <th>Antrian</th>
                        <th>Tanggal</th>
                        <th>Pasien</th>
                        <th>Poli</th>
                        <th>Dokter</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($kunjungans as $kjn)
                        <tr>
                            <td><code>{{ $kjn->no_kunjungan }}</code></td>
                            <td><strong class="text-dark">{{ $kjn->no_antrian }}</strong></td>
                            <td>{{ $kjn->tanggal_kunjungan->format('d-m-Y') }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $kjn->pasien->user->name }}</div>
                                <span class="small text-muted text-capitalize">{{ $kjn->jenis_kunjungan }}</span>
                            </td>
                            <td>{{ $kjn->poli->nama_poli }}</td>
                            <td>{{ $kjn->dokter->user->name ?? '-' }}</td>
                            <td>
                                <span class="badge text-capitalize
                                    @if($kjn->status === 'selesai') bg-success 
                                    @elseif($kjn->status === 'batal') bg-danger 
                                    @elseif($kjn->status === 'diperiksa') bg-info 
                                    @else bg-warning text-dark 
                                    @endif">
                                    {{ $kjn->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Tidak ada data kunjungan pada periode filter ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            {{ $kunjungans->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
