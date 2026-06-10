@extends('layouts.app')

@section('title', 'Edit Pengguna - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="card card-premium shadow-sm p-4 max-w-lg mx-auto" style="max-width: 650px;">
        <h4 class="fw-bold mb-2 border-bottom pb-2">Edit Pengguna</h4>
        <p class="text-muted small mb-4">Mengedit data pengguna dengan peran <strong>{{ strtoupper($user->role) }}</strong>.</p>

        <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
            @csrf
            @method('PUT')

            <!-- Basic Profile Fields -->
            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="name" class="form-label fw-bold">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-md-6 mb-3">
                    <label for="email" class="form-label fw-bold">Email</label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6 mb-3">
                    <label for="phone" class="form-label fw-bold">No. Telepon</label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                    @error('phone')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <!-- Conditional Section for Dokter -->
            @if ($user->role === 'dokter')
                <div class="border-top pt-3 mt-2">
                    <h6 class="fw-bold mb-3 text-primary"><i class="fa-solid fa-stethoscope me-1"></i> Informasi Tambahan Dokter</h6>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="nip_dokter" class="form-label fw-bold">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" name="nip" id="nip_dokter" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $user->profilDokter->nip ?? '') }}" required>
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="sip" class="form-label fw-bold">SIP (Surat Izin Praktik)</label>
                            <input type="text" name="sip" id="sip" class="form-control @error('sip') is-invalid @enderror" value="{{ old('sip', $user->profilDokter->sip ?? '') }}" required>
                            @error('sip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="spesialisasi" class="form-label fw-bold">Spesialisasi</label>
                            <input type="text" name="spesialisasi" id="spesialisasi" class="form-control @error('spesialisasi') is-invalid @enderror" value="{{ old('spesialisasi', $user->profilDokter->spesialisasi ?? 'Umum') }}" required>
                            @error('spesialisasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="poli" class="form-label fw-bold">Ditugaskan di Poli</label>
                            <select name="poli" id="poli" class="form-select @error('poli') is-invalid @enderror" required>
                                <option value="">Pilih Poli</option>
                                @foreach ($polis as $poli)
                                    <option value="{{ $poli->nama_poli }}" {{ old('poli', $user->profilDokter->poli ?? '') == $poli->nama_poli ? 'selected' : '' }}>{{ $poli->nama_poli }}</option>
                                @endforeach
                            </select>
                            @error('poli')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="harga_konsultasi" class="form-label fw-bold">Jasa / Harga Dokter (Rp)</label>
                            <input type="number" name="harga_konsultasi" id="harga_konsultasi" class="form-control @error('harga_konsultasi') is-invalid @enderror" value="{{ old('harga_konsultasi', $user->profilDokter->harga_konsultasi ?? 50000) }}" required min="0">
                            @error('harga_konsultasi')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jam_kerja" class="form-label fw-bold">Jam Kerja / Jadwal Dokter</label>
                            <input type="text" name="jam_kerja" id="jam_kerja" class="form-control @error('jam_kerja') is-invalid @enderror" value="{{ old('jam_kerja', $user->profilDokter->jam_kerja ?? '08:00 - 15:00') }}" required>
                            @error('jam_kerja')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- Conditional Section for Farmasi -->
            @if ($user->role === 'farmasi')
                <div class="border-top pt-3 mt-2">
                    <h6 class="fw-bold mb-3 text-warning"><i class="fa-solid fa-pills me-1"></i> Informasi Tambahan Farmasi</h6>
                    <div class="row g-3">
                        <div class="col-md-6 mb-3">
                            <label for="nip_farmasi" class="form-label fw-bold">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" name="nip" id="nip_farmasi" class="form-control @error('nip') is-invalid @enderror" value="{{ old('nip', $user->profilFarmasi->nip ?? '') }}" required>
                            @error('nip')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="jabatan" class="form-label fw-bold">Jabatan</label>
                            <input type="text" name="jabatan" id="jabatan" class="form-control @error('jabatan') is-invalid @enderror" value="{{ old('jabatan', $user->profilFarmasi->jabatan ?? '') }}" required>
                            @error('jabatan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            @endif

            <!-- Credentials Section -->
            <div class="border-top pt-3 mt-2">
                <h6 class="fw-bold mb-1 text-secondary"><i class="fa-solid fa-key me-1"></i> Ubah Kredensial Keamanan</h6>
                <p class="text-muted small mb-3">Biarkan kosong jika tidak ingin mengubah kata sandi pengguna.</p>
                
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label for="password" class="form-label fw-bold">Kata Sandi Baru</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" placeholder="Min 8 karakter">
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="password_confirmation" class="form-label fw-bold">Konfirmasi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="Ulangi kata sandi">
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
                <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary text-white"><i class="fa-solid fa-save me-1"></i> Perbarui Pengguna</button>
            </div>
        </form>
    </div>
</div>
@endsection
