@extends('layouts.app')

@section('title', 'Kelola Poli - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Admin Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Manajemen Poli / Klinik</h2>
        <p class="text-muted mb-0">Kelola poli pelayanan kesehatan aktif di Puskesmas & Klinik.</p>
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
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('admin.poli.index') }}">
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

    <div class="row g-4" x-data="{ editingPoli: null }">
        <!-- Form Add/Edit Column -->
        <div class="col-lg-4">
            <!-- Add Poli Card -->
            <div class="card card-premium shadow-sm p-4 mb-4" x-show="!editingPoli">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Tambah Poli</h5>
                <form action="{{ route('admin.poli.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="kode_poli" class="form-label fw-bold">Kode Poli</label>
                        <input type="text" name="kode_poli" id="kode_poli" class="form-control @error('kode_poli') is-invalid @enderror" value="{{ old('kode_poli') }}" placeholder="Contoh: PL-UMUM" required>
                        @error('kode_poli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama_poli" class="form-label fw-bold">Nama Poli</label>
                        <input type="text" name="nama_poli" id="nama_poli" class="form-control @error('nama_poli') is-invalid @enderror" value="{{ old('nama_poli') }}" placeholder="Contoh: Poli Umum" required>
                        @error('nama_poli')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="3" placeholder="Informasi detail poli...">{{ old('deskripsi') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary text-white w-100"><i class="fa-solid fa-plus me-1"></i> Simpan Poli</button>
                </form>
            </div>

            <!-- Edit Poli Card -->
            <div class="card card-premium shadow-sm p-4 border border-primary mb-4" x-show="editingPoli" x-cloak>
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold mb-0 text-primary">Edit Poli</h5>
                    <button type="button" class="btn-close" @click="editingPoli = null"></button>
                </div>
                <form :action="'/admin/poli/' + (editingPoli ? editingPoli.id : '')" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Poli</label>
                        <input type="text" class="form-control bg-light" x-model="editingPoli ? editingPoli.kode_poli : ''" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="edit_nama_poli" class="form-label fw-bold">Nama Poli</label>
                        <input type="text" name="nama_poli" id="edit_nama_poli" class="form-control @error('nama_poli') is-invalid @enderror" x-model="editingPoli ? editingPoli.nama_poli : ''" required>
                    </div>

                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="3" x-model="editingPoli ? editingPoli.deskripsi : ''"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50" @click="editingPoli = null">Batal</button>
                        <button type="submit" class="btn btn-primary text-white w-50"><i class="fa-solid fa-save me-1"></i> Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <div class="card card-premium shadow-sm p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Daftar Poli</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Poli</th>
                                <th>Deskripsi</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($polis as $poli)
                                <tr>
                                    <td><code>{{ $poli->kode_poli }}</code></td>
                                    <td class="fw-bold text-dark">{{ $poli->nama_poli }}</td>
                                    <td class="small text-muted text-truncate" style="max-width: 200px;">{{ $poli->deskripsi ?: '-' }}</td>
                                    <td>
                                        @if ($poli->is_aktif)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Aktif</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" @click="editingPoli = @js($poli)">
                                                <i class="fa-solid fa-edit"></i> Edit
                                            </button>
                                            
                                            <form action="{{ route('admin.poli.toggle', $poli->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $poli->is_aktif ? 'btn-outline-warning' : 'btn-outline-success' }} py-1 px-2">
                                                    {{ $poli->is_aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('admin.poli.destroy', $poli->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus poli ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">Belum ada poli terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $polis->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
