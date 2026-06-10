@extends('layouts.app')

@section('title', 'Dashboard Dokter - SI Puskesmas & Klinik')

@section('styles')
<style>
    .metric-card {
        padding: 1.5rem;
        border-radius: 16px;
        color: white;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: var(--card-shadow);
    }
    .metric-blue { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
    .metric-amber { background: linear-gradient(135deg, #d97706 0%, #b45309 100%); }
    .metric-emerald { background: linear-gradient(135deg, #059669 0%, #047857 100%); }
    .metric-value { font-size: 2.2rem; font-weight: 800; }
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
</style>
@endsection

@section('content')
<div class="animated-fade">
    <!-- Welcome Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">{{ $user->name }}</h2>
            <p class="text-muted mb-0">Poli Pemeriksaan: <strong>{{ $dokter->poli }}</strong> | SIP: {{ $dokter->sip ?? '-' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('dokter.resep.index') }}" class="btn btn-outline-primary"><i class="fa-solid fa-list-check me-2"></i> Riwayat Resep</a>
            <a href="{{ route('dokter.profil') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-user-doctor me-2"></i> Profil Dokter</a>
        </div>
    </div>

    <!-- Stats Bar -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="metric-card metric-blue">
                <div>
                    <span class="small text-white-50 text-uppercase fw-semibold">Pasien Hari Ini</span>
                    <div class="metric-value">{{ $totalPasienHariIni }}</div>
                </div>
                <i class="fa-solid fa-users fs-1 text-white-50"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card metric-amber">
                <div>
                    <span class="small text-white-50 text-uppercase fw-semibold">Menunggu Giliran</span>
                    <div class="metric-value">{{ $menungguCount }}</div>
                </div>
                <i class="fa-solid fa-spinner fa-spin fs-1 text-white-50"></i>
            </div>
        </div>
        <div class="col-md-4">
            <div class="metric-card metric-emerald">
                <div>
                    <span class="small text-white-50 text-uppercase fw-semibold">Pelayanan Selesai</span>
                    <div class="metric-value">{{ $selesaiCount }}</div>
                </div>
                <i class="fa-solid fa-circle-check fs-1 text-white-50"></i>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Today's Antrian Queue -->
        <div class="col-lg-8">
            <div class="card card-premium p-4 h-100">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-clipboard-list text-primary me-2"></i> Antrian Pasien Aktif</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr class="text-muted small">
                                <th>No. Antrian</th>
                                <th>Nama Pasien</th>
                                <th>Keluhan</th>
                                <th>Status</th>
                                <th class="text-center">Aksi Pelayanan</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($antrians as $antrian)
                                <tr>
                                    <td class="text-center">
                                        <span class="fs-5 fw-bold text-dark">{{ str_pad((string)$antrian->no_antrian, 3, '0', STR_PAD_LEFT) }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $antrian->pasien->user->name }}</div>
                                        <span class="text-muted small">NIK: {{ $antrian->pasien->nik }}</span>
                                    </td>
                                    <td class="small text-muted" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="{{ $antrian->keluhan }}">
                                        {{ $antrian->keluhan }}
                                    </td>
                                    <td>
                                        <span class="badge status-badge status-{{ $antrian->status }}">{{ $antrian->status }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if ($antrian->status === 'menunggu')
                                            <form action="{{ route('dokter.kunjungan.panggil', $antrian->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-primary text-white"><i class="fa-solid fa-bullhorn me-1"></i> Panggil</button>
                                            </form>
                                        @elseif ($antrian->status === 'dipanggil')
                                            <form action="{{ route('dokter.kunjungan.periksa', $antrian->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-warning text-dark"><i class="fa-solid fa-stethoscope me-1"></i> Periksa</button>
                                            </form>
                                        @elseif ($antrian->status === 'diperiksa')
                                            <a href="{{ route('dokter.resep.create', $antrian->id) }}" class="btn btn-sm btn-success text-white"><i class="fa-solid fa-file-prescription me-1"></i> Input Resep</a>
                                        @else
                                            <span class="text-muted small">Menunggu Apotek</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-folder-open display-6 mb-3 text-slate-300"></i>
                                        <h6 class="text-muted">Tidak ada pasien aktif dalam antrian saat ini.</h6>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Sidebar Column: Patient Search & Recent Resep -->
        <div class="col-lg-4 d-flex flex-column gap-4">
            <!-- NIK Patient Lookup -->
            <div class="card card-premium p-4">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-search-plus text-primary me-2"></i> Cari Rekam Pasien</h5>
                <p class="small text-muted mb-3">Masukkan NIK pasien untuk memantau riwayat kunjungan dan resep terdahulu.</p>
                <div class="input-group">
                    <input type="text" id="nik-search" class="form-control" placeholder="16 digit NIK Pasien" maxlength="16">
                    <button class="btn btn-primary text-white" type="button" onclick="searchPatient()">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>

            <!-- Recent Prescription Made -->
            <div class="card card-premium p-4 flex-grow-1">
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-receipt text-primary me-2"></i> Resep Hari Ini</h5>
                <div class="overflow-y-auto" style="max-height: 250px;">
                    @forelse ($resepsHariIni as $rsp)
                        <div class="p-2 border-bottom mb-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="small"><a href="{{ route('dokter.resep.show', $rsp->id) }}" class="text-primary text-decoration-none">{{ $rsp->no_resep }}</a></strong>
                                <span class="badge bg-secondary" style="font-size: 0.65rem;">{{ $rsp->status }}</span>
                            </div>
                            <div class="small text-dark mt-1">{{ $rsp->kunjungan->pasien->user->name }}</div>
                            <div class="text-muted small mt-1" style="font-size: 0.75rem;"><i class="fa-solid fa-clock me-1"></i> {{ $rsp->jam_input_resep->format('H:i') }} WIB</div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4 small">Belum ada resep diinput hari ini.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function searchPatient() {
        const nik = document.getElementById('nik-search').value.trim();
        if (nik.length !== 16) {
            alert('NIK harus berisi tepat 16 digit angka.');
            return;
        }
        window.location.href = `/dokter/pasien/${nik}/riwayat`;
    }
</script>
@endsection
