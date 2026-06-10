@extends('layouts.app')

@section('title', 'Detail Resep - SI Puskesmas & Klinik')

@section('content')
<div class="row justify-content-center animated-fade my-4">
    <div class="col-lg-8">
        <div class="card card-premium p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="fw-bold mb-0">Detail Resep Elektronik</h4>
                <a href="{{ route('dokter.dashboard') }}" class="btn btn-outline-primary btn-sm"><i class="fa-solid fa-house"></i> Dashboard</a>
            </div>

            <!-- Resep Summary -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <span class="text-muted small">Nomor Resep</span>
                    <h5 class="fw-bold text-dark">{{ $resep->no_resep }}</h5>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Status Resep</span>
                    <div>
                        <span class="badge bg-{{ $resep->status === 'selesai' ? 'success' : ($resep->status === 'diproses' ? 'warning' : 'secondary') }} text-uppercase px-3 py-1 mt-1">
                            {{ $resep->status }}
                        </span>
                    </div>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Nama Pasien / NIK</span>
                    <h6 class="fw-bold text-dark">{{ $resep->kunjungan->pasien->user->name }} ({{ $resep->kunjungan->pasien->nik }})</h6>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Prioritas Pengerjaan</span>
                    <h6 class="fw-bold text-{{ $resep->prioritas === 'urgen' ? 'danger' : 'dark' }} text-uppercase">
                        {{ $resep->prioritas }}
                    </h6>
                </div>
                <div class="col-md-6">
                    <span class="text-muted small">Waktu Input</span>
                    <h6 class="fw-bold text-dark">{{ $resep->jam_input_resep->format('d-m-Y H:i') }} WIB</h6>
                </div>
                @if($resep->jam_selesai_farmasi)
                    <div class="col-md-6">
                        <span class="text-muted small">Waktu Selesai Apotek</span>
                        <h6 class="fw-bold text-dark">{{ $resep->jam_selesai_farmasi->format('d-m-Y H:i') }} WIB</h6>
                    </div>
                @endif
                <div class="col-12">
                    <span class="text-muted small">Catatan Pemeriksaan Dokter</span>
                    <p class="bg-light p-3 rounded-3 mt-1 small"><em>"{{ $resep->catatan_dokter ?? 'Tidak ada catatan khusus' }}"</em></p>
                </div>
            </div>

            <!-- Prescription Items Table -->
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-pills text-primary me-2"></i> Item Obat Terdaftar</h5>
            <div class="table-responsive">
                <table class="table table-bordered align-middle small">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Obat</th>
                            <th class="text-center">Jumlah</th>
                            <th>Dosis & Aturan Pakai</th>
                            <th>Keterangan Tambahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($resep->detailResep as $detail)
                            <tr>
                                <td class="fw-semibold">{{ $detail->obat->nama_obat }}</td>
                                <td class="text-center">{{ $detail->jumlah }} {{ $detail->obat->satuan }}</td>
                                <td>{{ $detail->dosis }} ({{ $detail->aturan_pakai }})</td>
                                <td>{{ $detail->keterangan ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <a href="{{ route('dokter.resep.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left"></i> Kembali ke Daftar Resep</a>
            </div>
        </div>
    </div>
</div>
@endsection
