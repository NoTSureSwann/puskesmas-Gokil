@extends('layouts.app')

@section('title', 'Daftar Resep - SI Puskesmas & Klinik')

@section('content')
<div class="card card-premium p-4 p-md-5 animated-fade my-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Daftar Resep Elektronik</h3>
            <p class="text-muted mb-0">Daftar resep obat yang telah Anda buat di Puskesmas & Klinik.</p>
        </div>
        <a href="{{ route('dokter.dashboard') }}" class="btn btn-outline-primary"><i class="fa-solid fa-house me-2"></i> Dashboard</a>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead>
                <tr class="text-muted small">
                    <th>Tanggal Resep</th>
                    <th>No. Resep</th>
                    <th>Nama Pasien / NIK</th>
                    <th>Prioritas</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reseps as $rsp)
                    <tr>
                        <td class="small">{{ $rsp->jam_input_resep->format('d-m-Y H:i') }} WIB</td>
                        <td class="fw-semibold small">{{ $rsp->no_resep }}</td>
                        <td>
                            <div class="fw-bold small">{{ $rsp->kunjungan->pasien->user->name }}</div>
                            <span class="text-muted small" style="font-size: 0.75rem;">NIK: {{ $rsp->kunjungan->pasien->nik }}</span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $rsp->prioritas === 'urgen' ? 'danger' : 'secondary' }} text-uppercase" style="font-size: 0.7rem;">
                                {{ $rsp->prioritas }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $rsp->status === 'selesai' ? 'success' : ($rsp->status === 'diproses' ? 'warning' : 'secondary') }} text-uppercase" style="font-size: 0.7rem;">
                                {{ $rsp->status }}
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('dokter.resep.show', $rsp->id) }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-eye"></i> Detail</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">Belum ada resep obat dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination links -->
    <div class="mt-4">
        {{ $reseps->links() }}
    </div>
</div>
@endsection
