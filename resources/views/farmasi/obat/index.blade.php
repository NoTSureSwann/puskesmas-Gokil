@extends('layouts.app')

@section('title', 'Manajemen Obat - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Manajemen Obat & Harga</h2>
        <p class="text-muted mb-0">Kelola persediaan obat, harga, dan batas minimum stok apotek.</p>
    </div>

    <!-- Farmasi Sub-Navigation -->
    <div class="card card-premium shadow-sm mb-4 p-3">
        <div class="nav nav-pills card-header-pills flex-column flex-md-row gap-2">
            <a class="nav-link text-dark fw-semibold" href="{{ route('farmasi.dashboard') }}">
                <i class="fa-solid fa-gauge me-1"></i> Dashboard Antrean Resep
            </a>
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('farmasi.obat.index') }}">
                <i class="fa-solid fa-pills me-1"></i> Manajemen Obat
            </a>
        </div>
    </div>

    @if (session('status'))
        <div class="alert alert-success alert-premium mb-4">
            {{ session('status') }}
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-premium mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="row g-4" x-data="{ editingObat: null }">
        <!-- Form Column -->
        <div class="col-lg-4">
            <!-- Add Obat Card -->
            <div class="card card-premium shadow-sm p-4 mb-4" x-show="!editingObat">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Tambah Obat Baru</h5>
                <form action="{{ route('farmasi.obat.store') }}" method="POST">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="kode_obat" class="form-label fw-bold">Kode Obat</label>
                        <input type="text" name="kode_obat" id="kode_obat" class="form-control @error('kode_obat') is-invalid @enderror" value="{{ old('kode_obat') }}" placeholder="Contoh: OB-AMX-500" required>
                        @error('kode_obat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nama_obat" class="form-label fw-bold">Nama Obat</label>
                        <input type="text" name="nama_obat" id="nama_obat" class="form-control @error('nama_obat') is-invalid @enderror" value="{{ old('nama_obat') }}" placeholder="Contoh: Amoxicillin 500mg" required>
                        @error('nama_obat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label for="satuan" class="form-label fw-bold">Satuan</label>
                            <input type="text" name="satuan" id="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan', 'Tablet') }}" placeholder="Tablet, Kapsul, Botol" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="kategori" class="form-label fw-bold">Kategori</label>
                            <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', 'Antibiotik') }}" placeholder="Antibiotik, Analgesik" required>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label for="stok" class="form-label fw-bold">Stok Saat Ini</label>
                            <input type="number" name="stok" id="stok" class="form-control @error('stok') is-invalid @enderror" value="{{ old('stok', 100) }}" required min="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="stok_minimum" class="form-label fw-bold">Stok Min</label>
                            <input type="number" name="stok_minimum" id="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" value="{{ old('stok_minimum', 20) }}" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="harga_satuan" class="form-label fw-bold">Harga Jual Satuan (Rp)</label>
                        <input type="number" step="0.01" name="harga_satuan" id="harga_satuan" class="form-control @error('harga_satuan') is-invalid @enderror" value="{{ old('harga_satuan', 500) }}" required min="0">
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" id="deskripsi" class="form-control" rows="2" placeholder="Keterangan obat...">{{ old('deskripsi') }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary text-white w-100"><i class="fa-solid fa-plus me-1"></i> Simpan Obat</button>
                </form>
            </div>

            <!-- Edit Obat Card -->
            <div class="card card-premium shadow-sm p-4 border border-primary mb-4" x-show="editingObat" x-cloak>
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold mb-0 text-primary">Edit Data & Harga Obat</h5>
                    <button type="button" class="btn-close" @click="editingObat = null"></button>
                </div>
                <form :action="'/farmasi/obat/' + (editingObat ? editingObat.id : '')" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Obat</label>
                        <input type="text" class="form-control bg-light" x-model="editingObat ? editingObat.kode_obat : ''" readonly disabled>
                    </div>

                    <div class="mb-3">
                        <label for="edit_nama_obat" class="form-label fw-bold">Nama Obat</label>
                        <input type="text" name="nama_obat" id="edit_nama_obat" class="form-control" x-model="editingObat ? editingObat.nama_obat : ''" required>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label for="edit_satuan" class="form-label fw-bold">Satuan</label>
                            <input type="text" name="satuan" id="edit_satuan" class="form-control" x-model="editingObat ? editingObat.satuan : ''" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_kategori" class="form-label fw-bold">Kategori</label>
                            <input type="text" name="kategori" id="edit_kategori" class="form-control" x-model="editingObat ? editingObat.kategori : ''" required>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3">
                            <label for="edit_stok" class="form-label fw-bold">Stok Fisik</label>
                            <input type="number" name="stok" id="edit_stok" class="form-control" x-model="editingObat ? editingObat.stok : 0" required min="0">
                        </div>
                        <div class="col-6 mb-3">
                            <label for="edit_stok_minimum" class="form-label fw-bold">Stok Min</label>
                            <input type="number" name="stok_minimum" id="edit_stok_minimum" class="form-control" x-model="editingObat ? editingObat.stok_minimum : 0" required min="0">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_harga_satuan" class="form-label fw-bold text-primary">Harga Jual Satuan (Rp)</label>
                        <input type="number" step="0.01" name="harga_satuan" id="edit_harga_satuan" class="form-control border-primary" x-model="editingObat ? editingObat.harga_satuan : 0" required min="0">
                    </div>

                    <div class="mb-3">
                        <label for="edit_deskripsi" class="form-label fw-bold">Deskripsi</label>
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2" x-model="editingObat ? editingObat.deskripsi : ''"></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50" @click="editingObat = null">Batal</button>
                        <button type="submit" class="btn btn-primary text-white w-50"><i class="fa-solid fa-save me-1"></i> Simpan Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <div class="card card-premium shadow-sm p-4">
                <h5 class="fw-bold mb-3 border-bottom pb-2">Daftar Inventaris Apotek</h5>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Obat</th>
                                <th class="text-center">Sisa Stok</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($obats as $obat)
                                @php
                                    $stokRendah = $obat->stok <= $obat->stok_minimum;
                                @endphp
                                <tr class="{{ $stokRendah ? 'table-danger-subtle' : '' }}">
                                    <td><code>{{ $obat->kode_obat }}</code></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $obat->nama_obat }}</div>
                                        <span class="small text-muted">{{ $obat->kategori }} ({{ $obat->satuan }})</span>
                                    </td>
                                    <td class="text-center fw-bold {{ $stokRendah ? 'text-danger' : 'text-success' }}">
                                        {{ $obat->stok }}
                                        @if ($stokRendah)
                                            <div class="small text-danger fw-normal" style="font-size: 0.7rem;"><i class="fa-solid fa-triangle-exclamation"></i> Rendah</div>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-primary">Rp {{ number_format((float) $obat->harga_satuan, 2, ',', '.') }}</td>
                                    <td>
                                        @if ($obat->is_aktif)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Tersedia</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis/Ditarik</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" @click="editingObat = @js($obat)">
                                                <i class="fa-solid fa-edit"></i> Edit
                                            </button>
                                            
                                            <form action="{{ route('farmasi.obat.toggle', $obat->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $obat->is_aktif ? 'btn-outline-warning' : 'btn-outline-success' }} py-1 px-2">
                                                    {{ $obat->is_aktif ? 'Tarik' : 'Sediakan' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('farmasi.obat.destroy', $obat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus obat ini?');">
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
                                    <td colspan="6" class="text-center text-muted py-4">Belum ada obat terdaftar.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $obats->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
