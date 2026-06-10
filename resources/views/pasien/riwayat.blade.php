@extends('layouts.app')

@section('title', 'Riwayat Kunjungan - SI Puskesmas & Klinik')

@section('styles')
<style>
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
<div class="card card-premium p-4 p-md-5 animated-fade my-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Riwayat Kunjungan</h3>
            <p class="text-muted mb-0">Daftar lengkap rekam medis pendaftaran dan konsultasi Anda.</p>
        </div>
        <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-primary"><i class="fa-solid fa-house me-2"></i> Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr class="text-muted small">
                    <th>Tanggal Kunjungan</th>
                    <th>No. Kunjungan</th>
                    <th>Poli Klinik</th>
                    <th>Dokter Pemeriksa</th>
                    <th>Jenis Kunjungan</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($kunjungans as $kunj)
                    <tr>
                        <td class="fw-medium small">{{ \Carbon\Carbon::parse($kunj->tanggal_kunjungan)->format('d-m-Y') }}</td>
                        <td class="small fw-semibold text-slate-700">{{ $kunj->no_kunjungan }}</td>
                        <td>{{ $kunj->poli->nama_poli }}</td>
                        <td class="small">{{ $kunj->dokter ? $kunj->dokter->user->name : '-' }}</td>
                        <td class="small text-uppercase">{{ $kunj->jenis_kunjungan }}</td>
                        <td>
                            <span class="badge status-badge status-{{ $kunj->status }}">{{ $kunj->status }}</span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('pasien.kunjungan', $kunj->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye me-1"></i> Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-5">
                            <i class="fa-solid fa-folder-open display-6 mb-3 text-slate-300"></i>
                            <h6 class="text-muted">Belum ada data kunjungan.</h6>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="mt-4">
        {{ $kunjungans->links() }}
    </div>
</div>
@endsection
