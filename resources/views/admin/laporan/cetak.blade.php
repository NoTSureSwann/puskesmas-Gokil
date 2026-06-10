@extends('layouts.app')

@section('title', 'Audit Log Cetak - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Admin Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Audit Log Pencetakan Struk Obat (SQLite)</h2>
        <p class="text-muted mb-0">Riwayat otentikasi pencetakan dan pencetakan ulang struk etiket obat farmasi.</p>
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
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.laporan.kunjungan') }}">
                <i class="fa-solid fa-file-medical me-1"></i> Laporan Kunjungan
            </a>
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('admin.laporan.cetak') }}">
                <i class="fa-solid fa-print me-1"></i> Audit Cetak Struk (SQLite)
            </a>
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.laporan.ai_dataset') }}">
                <i class="fa-solid fa-robot me-1"></i> Dataset Keluhan AI
            </a>
        </div>
    </div>

    <!-- SQLite Log List -->
    <div class="card card-premium shadow-sm p-4">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-database text-primary me-2"></i> Log Database SQLite (`log_cetak`)</h5>
            <span class="badge bg-secondary text-uppercase">Connection: sqlite_log</span>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID Log</th>
                        <th>No. Resep</th>
                        <th>Pasien</th>
                        <th>Dicetak Oleh (ID)</th>
                        <th>Waktu Cetak</th>
                        <th class="text-center">Tipe Cetak</th>
                        <th class="text-center">File PDF</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td><code>#{{ $log->id }}</code></td>
                            <td class="fw-bold text-dark">{{ $log->no_resep }}</td>
                            <td>{{ $log->nama_pasien }}</td>
                            <td>
                                @php
                                    $pharmacist = \App\Models\User::find($log->farmasi_user_id);
                                @endphp
                                {{ $pharmacist ? $pharmacist->name : 'Staff Farmasi' }}
                                <span class="text-muted small d-block">ID: {{ $log->farmasi_user_id }}</span>
                            </td>
                            <td>{{ \Carbon\Carbon::parse($log->dicetak_pada)->format('d-m-Y H:i:s') }} WIB</td>
                            <td class="text-center">
                                @if ($log->is_reprint)
                                    <span class="badge bg-warning text-dark border border-warning-subtle">
                                        <i class="fa-solid fa-arrows-rotate me-1"></i> Cetak Ulang (Reprint)
                                    </span>
                                @else
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="fa-solid fa-file-invoice me-1"></i> Cetakan Pertama
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ $log->path_pdf }}" target="_blank" class="btn btn-sm btn-outline-danger py-1 px-2">
                                    <i class="fa-solid fa-file-pdf"></i> Buka PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">Belum ada riwayat cetak struk obat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection
