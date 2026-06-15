@extends('layouts.app')

@section('title', 'Riwayat Kunjungan - SI Puskesmas & Klinik')

@section('styles')
<link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
<link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
<style>
    .status-badge {
        padding: 0.35rem 0.8rem;
        border-radius: 50px;
        font-size: 0.8rem;
        font-weight: 600;
        text-transform: uppercase;
    }
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
        <table class="table table-hover align-middle w-100" id="riwayat-table">
            <thead>
                <tr class="text-muted small bg-light">
                    <th>Tanggal</th>
                    <th>No. Kunjungan</th>
                    <th>Poli Klinik</th>
                    <th>Dokter Pemeriksa</th>
                    <th>Jenis</th>
                    <th>Status</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    $('#riwayat-table').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: "{{ route('pasien.riwayat') }}",
        columns: [
            {data: 'tanggal', name: 'tanggal_kunjungan'},
            {data: 'no_kunjungan', name: 'no_kunjungan', className: 'small fw-semibold text-slate-700'},
            {data: 'poli_nama', name: 'poli_nama', orderable: false, searchable: false},
            {data: 'dokter_nama', name: 'dokter_nama', orderable: false, searchable: false, className: 'small'},
            {data: 'jenis_kunjungan', name: 'jenis_kunjungan', className: 'small text-uppercase'},
            {data: 'status_badge', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
        ],
        language: {
            url: "https://cdn.datatables.net/plug-ins/1.13.7/i18n/id.json"
        }
    });
});
</script>
@endsection
