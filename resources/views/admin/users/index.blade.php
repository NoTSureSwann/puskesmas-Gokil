@extends('layouts.app')

@section('title', 'Kelola Pengguna - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Admin Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1">Manajemen Pengguna</h2>
            <p class="text-muted mb-0">Kelola akun dan hak akses staff medis, apoteker, admin, dan pasien.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary text-white">
            <i class="fa-solid fa-user-plus me-1"></i> Tambah Pengguna
        </a>
    </div>

    <!-- Admin Sub-Navigation -->
    <div class="card card-premium shadow-sm mb-4 p-3">
        <div class="nav nav-pills card-header-pills flex-column flex-md-row gap-2">
            <a class="nav-link text-dark fw-semibold" href="{{ route('admin.dashboard') }}">
                <i class="fa-solid fa-gauge me-1"></i> Ringkasan
            </a>
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('admin.users.index') }}">
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

    <!-- Filters & List -->
    <div class="card card-premium shadow-sm p-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4 border-bottom pb-3">
            <h5 class="fw-bold mb-0">Daftar Pengguna</h5>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.users.index') }}" class="btn btn-sm {{ !$role ? 'btn-secondary text-white' : 'btn-outline-secondary' }}">Semua</a>
                <a href="{{ route('admin.users.index', ['role' => 'dokter']) }}" class="btn btn-sm {{ $role === 'dokter' ? 'btn-primary text-white' : 'btn-outline-primary' }}">Dokter</a>
                <a href="{{ route('admin.users.index', ['role' => 'farmasi']) }}" class="btn btn-sm {{ $role === 'farmasi' ? 'btn-primary text-white' : 'btn-outline-primary' }}">Farmasi/Apotek</a>
                <a href="{{ route('admin.users.index', ['role' => 'admin']) }}" class="btn btn-sm {{ $role === 'admin' ? 'btn-primary text-white' : 'btn-outline-primary' }}">Admin</a>
                <a href="{{ route('admin.users.index', ['role' => 'pasien']) }}" class="btn btn-sm {{ $role === 'pasien' ? 'btn-primary text-white' : 'btn-outline-primary' }}">Pasien</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>Nama / Email</th>
                        <th>No. Telepon</th>
                        <th>Peran</th>
                        <th>Detail Profil</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $usr)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $usr->name }}</div>
                                <span class="small text-muted">{{ $usr->email }}</span>
                            </td>
                            <td>{{ $usr->phone ?: '-' }}</td>
                            <td>
                                <span class="badge text-capitalize 
                                    @if($usr->role === 'admin') bg-danger 
                                    @elseif($usr->role === 'dokter') bg-primary 
                                    @elseif($usr->role === 'farmasi') bg-warning text-dark 
                                    @else bg-secondary 
                                    @endif">
                                    {{ $usr->role }}
                                </span>
                            </td>
                            <td>
                                @if ($usr->role === 'dokter')
                                    <div class="small text-dark">NIP: {{ $usr->profilDokter->nip ?? '-' }}</div>
                                    <div class="small text-muted">Poli: {{ $usr->profilDokter->poli ?? '-' }} ({{ $usr->profilDokter->spesialisasi ?? '' }})</div>
                                    <div class="small text-primary"><i class="fa-solid fa-clock me-1"></i> Jam: {{ $usr->profilDokter->jam_kerja ?? '-' }}</div>
                                    <div class="small text-success"><i class="fa-solid fa-tags me-1"></i> Tarif: Rp {{ number_format((float)($usr->profilDokter->harga_konsultasi ?? 0), 0, ',', '.') }}</div>
                                @elseif ($usr->role === 'farmasi')
                                    <div class="small text-dark">NIP: {{ $usr->profilFarmasi->nip ?? '-' }}</div>
                                    <div class="small text-muted">Jabatan: {{ $usr->profilFarmasi->jabatan ?? '-' }}</div>
                                @elseif ($usr->role === 'pasien')
                                    <div class="small text-dark">NIK: {{ $usr->profilPasien->nik_masked ?? '-' }}</div>
                                    <div class="small text-muted">BPJS: {{ $usr->profilPasien->bpjs_masked ?? '-' }}</div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                @if ($usr->status === 'aktif')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center gap-2">
                                    <a href="{{ route('admin.users.edit', $usr->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                        <i class="fa-solid fa-pen-to-square"></i> Edit
                                    </a>
                                    
                                    @if ($usr->id !== auth()->id())
                                        <form action="{{ route('admin.users.toggle', $usr->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm {{ $usr->status === 'aktif' ? 'btn-outline-warning' : 'btn-outline-success' }} py-1 px-2">
                                                <i class="fa-solid {{ $usr->status === 'aktif' ? 'fa-user-slash' : 'fa-user-check' }}"></i> 
                                                {{ $usr->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }}
                                            </button>
                                        </form>

                                        <form action="{{ route('admin.users.destroy', $usr->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Tidak ada data pengguna.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection
