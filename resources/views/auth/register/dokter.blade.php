@extends('layouts.app')

@section('title', 'Daftar Akun Dokter Baru - SI Puskesmas & Klinik')

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
                <i class="fa-solid fa-user-doctor text-primary fs-1 mb-2"></i>
                <h2 class="fw-bold">Pendaftaran Akun Dokter</h2>
                <p class="text-muted">Lengkapi profil medis dan data diri Anda untuk pendaftaran dokter klinik.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-premium mb-4">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Mohon koreksi kesalahan berikut:</h6>
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="dokter">

                <!-- BAGIAN 1: AKUN -->
                <div class="step-header"><i class="fa-solid fa-key me-2"></i> Bagian 1: Kredensial Akun</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-medium small">Nama Lengkap (dengan Gelar) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: dr. Budi Santoso, Sp.PD" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-medium small">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: dr.budi@gmail.com" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-medium small">Nomor HP / WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-medium small">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Minimal 8 karakter" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-medium small">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" required>
                    </div>
                </div>

                <!-- BAGIAN 2: PROFIL DOKTER -->
                <div class="step-header"><i class="fa-solid fa-stethoscope me-2"></i> Bagian 2: Profil Dokter</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nip" class="form-label fw-medium small">NIP (Opsional)</label>
                        <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai" maxlength="20">
                    </div>
                    <div class="col-md-6">
                        <label for="sip" class="form-label fw-medium small">SIP (Opsional)</label>
                        <input type="text" class="form-control @error('sip') is-invalid @enderror" id="sip" name="sip" value="{{ old('sip') }}" placeholder="Surat Izin Praktik" maxlength="30">
                    </div>
                    <div class="col-md-6">
                        <label for="spesialisasi" class="form-label fw-medium small">Spesialisasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('spesialisasi') is-invalid @enderror" id="spesialisasi" name="spesialisasi" value="{{ old('spesialisasi', 'Dokter Umum') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="poli" class="form-label fw-medium small">Poli <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('poli') is-invalid @enderror" id="poli" name="poli" value="{{ old('poli', 'Poli Umum') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="harga_konsultasi" class="form-label fw-medium small">Harga Konsultasi (Rp) <span class="text-danger">*</span></label>
                        <input type="number" class="form-control @error('harga_konsultasi') is-invalid @enderror" id="harga_konsultasi" name="harga_konsultasi" value="{{ old('harga_konsultasi', '50000') }}" min="0" required>
                    </div>
                    <div class="col-md-6">
                        <label for="jam_kerja" class="form-label fw-medium small">Jam Kerja <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('jam_kerja') is-invalid @enderror" id="jam_kerja" name="jam_kerja" value="{{ old('jam_kerja', '08:00 - 16:00') }}" placeholder="Misal: 08:00 - 16:00" required>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms_conditions" name="terms_conditions" required>
                        <label class="form-check-label small" for="terms_conditions">
                            Saya menyetujui Syarat & Ketentuan pada <strong class="text-primary">project prototipe platform ini</strong> (Termasuk persetujuan regulasi UU PDP & ISO 27001).
                        </label>
                    </div>
                </div>

                <div class="mt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary order-2 order-sm-1 w-100 w-sm-auto"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary text-white order-1 order-sm-2 w-100 w-sm-auto"><i class="fa-solid fa-user-plus me-1"></i> Daftar Sekarang</button>
                </div>
                
                <div class="text-center mt-4">
                    <span class="text-muted small">Sudah memiliki akun? <a href="{{ route('login', ['role' => 'dokter']) }}" class="text-primary fw-medium text-decoration-none">Masuk di sini</a></span>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
