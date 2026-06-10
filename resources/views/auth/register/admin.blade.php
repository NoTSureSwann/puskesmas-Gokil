@extends('layouts.app')

@section('title', 'Daftar Akun Admin Baru - SI Puskesmas & Klinik')

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
                <i class="fa-solid fa-user-gear text-primary fs-1 mb-2"></i>
                <h2 class="fw-bold">Pendaftaran Akun Admin</h2>
                <p class="text-muted">Lengkapi data diri Anda untuk pendaftaran administrator sistem.</p>
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
                <input type="hidden" name="role" value="admin">

                <!-- BAGIAN 1: AKUN -->
                <div class="step-header"><i class="fa-solid fa-key me-2"></i> Bagian 1: Kredensial Akun</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-medium small">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Admin Klinik" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-medium small">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: admin@klinik.com" required>
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
                    <span class="text-muted small">Sudah memiliki akun? <a href="{{ route('login', ['role' => 'admin']) }}" class="text-primary fw-medium text-decoration-none">Masuk di sini</a></span>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
