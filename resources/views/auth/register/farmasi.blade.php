@extends('layouts.app')

@section('title', 'Daftar Akun Farmasi Baru - SI Puskesmas & Klinik')

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
                <i class="fa-solid fa-pills text-primary fs-1 mb-2"></i>
                <h2 class="fw-bold">Pendaftaran Akun Farmasi</h2>
                <p class="text-muted">Lengkapi data diri Anda untuk pendaftaran apoteker/staf farmasi.</p>
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
                <input type="hidden" name="role" value="farmasi">

                <!-- BAGIAN 1: AKUN -->
                <div class="step-header"><i class="fa-solid fa-key me-2"></i> Bagian 1: Kredensial Akun</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-medium small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Siti Aminah, S.Farm" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-medium small">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: siti.farmasi@gmail.com" required>
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

                <!-- BAGIAN 2: PROFIL FARMASI -->
                <div class="step-header"><i class="fa-solid fa-prescription-bottle-medical me-2"></i> Bagian 2: Profil Farmasi</div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nip" class="form-label fw-medium small">NIP (Opsional)</label>
                        <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip') }}" placeholder="Nomor Induk Pegawai" maxlength="20">
                    </div>
                    <div class="col-md-6">
                        <label for="jabatan" class="form-label fw-medium small">Jabatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('jabatan') is-invalid @enderror" id="jabatan" name="jabatan" value="{{ old('jabatan', 'Apoteker') }}" placeholder="Misal: Apoteker atau Asisten Apoteker" required>
                    </div>
                </div>

                <div class="mt-5 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <a href="{{ route('home') }}" class="btn btn-outline-secondary order-2 order-sm-1 w-100 w-sm-auto"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary text-white order-1 order-sm-2 w-100 w-sm-auto"><i class="fa-solid fa-user-plus me-1"></i> Daftar Sekarang</button>
                </div>
                
                <div class="text-center mt-4">
                    <span class="text-muted small">Sudah memiliki akun? <a href="{{ route('login', ['role' => 'farmasi']) }}" class="text-primary fw-medium text-decoration-none">Masuk di sini</a></span>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
