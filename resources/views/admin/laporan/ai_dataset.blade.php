@extends('layouts.app')

@section('title', 'AI Symptom Dataset - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Admin Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Dataset Keluhan & Analisis AI Pasien</h2>
        <p class="text-muted mb-0">Manajemen pengolahan dataset hasil klasifikasi penyakit AI (LLaMA 3.3) berdasarkan keluhan pasien.</p>
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
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.laporan.cetak') }}">
                <i class="fa-solid fa-print me-1"></i> Audit Cetak Struk (SQLite)
            </a>
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('admin.laporan.ai_dataset') }}">
                <i class="fa-solid fa-robot me-1"></i> Dataset Keluhan AI
            </a>
        </div>
    </div>

    <!-- Dataset Controls & Table -->
    <div class="card card-premium shadow-sm p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 border-bottom pb-3 gap-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-network-wired text-primary me-2"></i> Repositori Dataset Keluhan</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.laporan.ai_dataset.export', 'json') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-file-code me-2"></i> Export JSON Dataset
                </a>
                <a href="{{ route('admin.laporan.ai_dataset.export', 'csv') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-file-csv me-2"></i> Export CSV Log
                </a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th style="width: 70px;">ID</th>
                        <th style="width: 150px;">Pasien</th>
                        <th style="width: 250px;">Keluhan Pasien</th>
                        <th style="width: 200px;">Kemungkinan Penyakit</th>
                        <th class="text-center" style="width: 100px;">Urgensi</th>
                        <th style="width: 150px;">Rekomendasi Poli</th>
                        <th class="text-center" style="width: 150px;">Status Cetak Struk</th>
                        <th style="width: 150px;">Tanggal Masuk</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($datasets as $item)
                        <tr>
                            <td><code>#{{ $item->id }}</code></td>
                            <td>
                                <span class="fw-bold text-dark d-block">{{ $item->kunjungan && $item->kunjungan->pasien && $item->kunjungan->pasien->user ? $item->kunjungan->pasien->user->name : 'Anonim' }}</span>
                                <span class="text-muted small">No: {{ $item->kunjungan ? $item->kunjungan->no_kunjungan : '-' }}</span>
                            </td>
                            <td>
                                <div class="text-wrap" style="max-height: 80px; overflow-y: auto; font-size: 0.85rem; line-height: 1.3;">
                                    {{ $item->keluhan }}
                                </div>
                            </td>
                            <td>
                                @foreach ($item->kemungkinan_penyakit ?? [] as $penyakit)
                                    <span class="badge bg-secondary mb-1">{{ $penyakit }}</span>
                                @endforeach
                                @if(empty($item->kemungkinan_penyakit))
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge" 
                                      :class="{
                                          'bg-success': '{{ $item->tingkat_urgensi }}' === 'Rendah',
                                          'bg-warning text-dark': '{{ $item->tingkat_urgensi }}' === 'Sedang',
                                          'bg-danger': '{{ $item->tingkat_urgensi }}' === 'Tinggi'
                                      }">
                                    {{ $item->tingkat_urgensi }}
                                </span>
                            </td>
                            <td>
                                <span class="text-primary fw-semibold"><i class="fa-solid fa-stethoscope me-1"></i> {{ $item->rekomendasi_poli_nama }}</span>
                            </td>
                            <td class="text-center">
                                @if ($item->is_printed)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle d-block mb-1">
                                        <i class="fa-solid fa-circle-check me-1"></i> Sudah Dicetak
                                    </span>
                                    <span class="text-muted small d-block" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($item->dicetak_pada)->format('d-m-Y H:i') }}</span>
                                @else
                                    <span class="badge bg-light text-muted border d-block">
                                        <i class="fa-solid fa-clock me-1"></i> Belum Dicetak
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="text-muted small">{{ $item->created_at->format('d-m-Y H:i') }} WIB</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-5">Belum ada data analisis keluhan AI yang diolah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $datasets->links() }}
        </div>
    </div>
</div>
@endsection
