@extends('layouts.app')

@section('title', 'Profil Saya - SI Puskesmas & Klinik')

@section('styles')
<style>
    .step-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
        border-bottom: 2px solid var(--primary-light);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        margin-top: 2rem;
    }
    .step-header:first-of-type {
        margin-top: 0;
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center animated-fade">
    <div class="col-lg-8">
        <div class="card card-premium p-4 p-md-5 my-4">
            <div class="text-center mb-4">
                <i class="fa-solid fa-circle-user text-primary fs-1 mb-2"></i>
                <h3 class="fw-bold">Profil Akun Saya</h3>
                <p class="text-muted">Perbarui data kependudukan dan alamat tinggal Anda sesuai berkas resmi.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-premium mb-4 py-2 px-3">
                    <ul class="mb-0 small ps-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('pasien.profil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- BAGIAN 1: AKUN -->
                <div class="step-header"><i class="fa-solid fa-key me-2"></i> Bagian 1: Data Akun Dasar</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-medium small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-medium small">Alamat Email (Read-only)</label>
                        <input type="email" class="form-control bg-light text-muted" id="email" value="{{ $user->email }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-medium small">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    </div>
                </div>

                <!-- BAGIAN 2: DATA KEPENDUDUKAN -->
                <div class="step-header"><i class="fa-solid fa-id-card me-2"></i> Bagian 2: Data Kependudukan</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nik" class="form-label fw-medium small">NIK (Nomor Induk Kependudukan) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik', $pasien->nik) }}" maxlength="16" required>
                    </div>
                    <div class="col-md-6">
                        <label for="no_bpjs" class="form-label fw-medium small">Nomor BPJS (Opsional)</label>
                        <input type="text" class="form-control @error('no_bpjs') is-invalid @enderror" id="no_bpjs" name="no_bpjs" value="{{ old('no_bpjs', $pasien->no_bpjs) }}" maxlength="13">
                    </div>
                    <div class="col-md-6">
                        <label for="no_kk" class="form-label fw-medium small">Nomor Kartu Keluarga (KK) (Opsional)</label>
                        <input type="text" class="form-control @error('no_kk') is-invalid @enderror" id="no_kk" name="no_kk" value="{{ old('no_kk', $pasien->no_kk) }}" maxlength="16">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium small d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" {{ old('jenis_kelamin', $pasien->jenis_kelamin) === 'L' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="jk_l">Laki-laki</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" {{ old('jenis_kelamin', $pasien->jenis_kelamin) === 'P' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="jk_p">Perempuan</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tempat_lahir" class="form-label fw-medium small">Tempat Lahir <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" id="tempat_lahir" name="tempat_lahir" value="{{ old('tempat_lahir', $pasien->tempat_lahir) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label fw-medium small">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir', $pasien->tanggal_lahir) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="golongan_darah" class="form-label fw-medium small">Golongan Darah</label>
                        <select class="form-select @error('golongan_darah') is-invalid @enderror" id="golongan_darah" name="golongan_darah">
                            <option value="Tidak Tahu" {{ old('golongan_darah', $pasien->golongan_darah) === 'Tidak Tahu' ? 'selected' : '' }}>Tidak Tahu / Belum Cek</option>
                            <option value="A" {{ old('golongan_darah', $pasien->golongan_darah) === 'A' ? 'selected' : '' }}>A</option>
                            <option value="B" {{ old('golongan_darah', $pasien->golongan_darah) === 'B' ? 'selected' : '' }}>B</option>
                            <option value="AB" {{ old('golongan_darah', $pasien->golongan_darah) === 'AB' ? 'selected' : '' }}>AB</option>
                            <option value="O" {{ old('golongan_darah', $pasien->golongan_darah) === 'O' ? 'selected' : '' }}>O</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="tinggi_badan" class="form-label fw-medium small">Tinggi Badan (cm)</label>
                        <input type="number" class="form-control @error('tinggi_badan') is-invalid @enderror" id="tinggi_badan" name="tinggi_badan" value="{{ old('tinggi_badan', $pasien->tinggi_badan) }}" placeholder="Contoh: 170" min="10" max="300">
                    </div>
                    <div class="col-md-6">
                        <label for="berat_badan" class="form-label fw-medium small">Berat Badan (kg)</label>
                        <input type="number" class="form-control @error('berat_badan') is-invalid @enderror" id="berat_badan" name="berat_badan" value="{{ old('berat_badan', $pasien->berat_badan) }}" placeholder="Contoh: 65" min="1" max="500">
                    </div>
                </div>

                <!-- BAGIAN 3: ALAMAT -->
                <div class="step-header"><i class="fa-solid fa-map-location-dot me-2"></i> Bagian 3: Alamat Tinggal & Layanan</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="alamat" class="form-label fw-medium small">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="3" required>{{ old('alamat', $pasien->alamat) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label for="kelurahan" class="form-label fw-medium small">Kelurahan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kelurahan') is-invalid @enderror" id="kelurahan" name="kelurahan" value="{{ old('kelurahan', $pasien->kelurahan) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="kecamatan" class="form-label fw-medium small">Kecamatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('kecamatan') is-invalid @enderror" id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $pasien->kecamatan) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium small d-block">Jenis Layanan Pasien <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="jenis_pasien" id="jp_umum" value="umum" {{ old('jenis_pasien', $pasien->jenis_pasien) === 'umum' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="jp_umum">Umum (Mandiri)</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="jenis_pasien" id="jp_bpjs" value="bpjs" {{ old('jenis_pasien', $pasien->jenis_pasien) === 'bpjs' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="jp_bpjs">BPJS Kesehatan</label>
                        </div>
                    </div>
                </div>

                <!-- BAGIAN 4: RIWAYAT KESEHATAN -->
                <div class="step-header"><i class="fa-solid fa-notes-medical me-2"></i> Bagian 4: Riwayat Kesehatan</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="riwayat_alergi" class="form-label fw-medium small">Riwayat Alergi Obat</label>
                        <textarea class="form-control @error('riwayat_alergi') is-invalid @enderror" id="riwayat_alergi" name="riwayat_alergi" rows="2">{{ old('riwayat_alergi', $pasien->riwayat_alergi) }}</textarea>
                    </div>
                </div>

                <div class="mt-5 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary order-2 order-sm-1 w-100 w-sm-auto"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary text-white order-1 order-sm-2 w-100 w-sm-auto"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
