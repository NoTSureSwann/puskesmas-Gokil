@extends('layouts.app')

@section('title', 'Daftar Akun Pasien - SI Puskesmas & Klinik')

@section('styles')
<style>
    /* Glassmorphism Design */
    body {
        /* Tambahkan background gradient yang lebih menarik untuk menonjolkan efek glass */
        background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    }
    .glass-card {
        background: rgba(255, 255, 255, 0.65);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        border-radius: 1.5rem;
    }
    .step-header {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary);
        border-bottom: 2px solid rgba(14, 165, 233, 0.2);
        padding-bottom: 0.5rem;
        margin-bottom: 1.5rem;
        margin-top: 1.5rem;
    }
    .step-header:first-of-type {
        margin-top: 0;
    }
    .form-control, .form-select {
        background-color: rgba(255, 255, 255, 0.8);
        border: 1px solid rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    .form-control:focus, .form-select:focus {
        background-color: #fff;
        box-shadow: 0 0 0 0.25rem rgba(14, 165, 233, 0.25);
    }
</style>
@endsection

@section('content')
<div class="row justify-content-center animated-fade pb-5 pt-3">
    <div class="col-lg-7 col-md-9">
        <div class="card glass-card p-4 p-md-5 my-4">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 rounded-circle mb-3" style="width: 80px; height: 80px;">
                    <i class="fa-solid fa-user-plus text-primary fs-2"></i>
                </div>
                <h2 class="fw-bold text-slate-800">Pendaftaran Pasien Baru</h2>
                <p class="text-slate-500">Cukup isi data esensial untuk membuat antrean secara instan. Data lengkap dapat dilengkapi nanti di profil Anda.</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-premium mb-4 shadow-sm border-0">
                    <h6 class="fw-bold mb-2"><i class="fa-solid fa-triangle-exclamation me-2"></i> Mohon periksa kembali:</h6>
                    <ul class="mb-0 small ps-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('register.submit') }}" method="POST">
                @csrf
                <input type="hidden" name="role" value="pasien">

                <div class="step-header"><i class="fa-solid fa-key me-2"></i> Informasi Akun</div>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-medium small text-slate-700">Nama Lengkap Sesuai KTP <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name') }}" placeholder="Contoh: Ahmad Hidayat" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-medium small text-slate-700">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email') }}" placeholder="Contoh: ahmad@gmail.com" required>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-medium small text-slate-700">Nomor WhatsApp <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone') }}" placeholder="Contoh: 081234567890" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password" class="form-label fw-medium small text-slate-700">Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" placeholder="Minimal 8 karakter" required>
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label fw-medium small text-slate-700">Konfirmasi Kata Sandi <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" placeholder="Ulangi kata sandi" required>
                    </div>
                </div>

                <div class="step-header"><i class="fa-solid fa-id-card me-2"></i> Data Medis Dasar</div>
                <div class="row g-3">
                    <div class="col-md-12">
                        <label for="nik" class="form-label fw-medium small text-slate-700">NIK (Nomor Induk Kependudukan) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" value="{{ old('nik') }}" placeholder="16 digit angka KTP" maxlength="16" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium small d-block text-slate-700">Jenis Kelamin <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_l" value="L" {{ old('jenis_kelamin') === 'L' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="jk_l">Laki-laki</label>
                        </div>
                        <div class="form-check form-check-inline mt-2">
                            <input class="form-check-input" type="radio" name="jenis_kelamin" id="jk_p" value="P" {{ old('jenis_kelamin') === 'P' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="jk_p">Perempuan</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label fw-medium small text-slate-700">Tanggal Lahir <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" id="tanggal_lahir" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" required>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-top border-light">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="terms_conditions" name="terms_conditions" required>
                        <label class="form-check-label small text-slate-600" for="terms_conditions">
                            {!! __('messages.terms_agree') !!}
                        </label>
                    </div>
                </div>

                <div class="mt-4 pt-2">
                    <button type="submit" class="btn btn-primary w-100 py-2 fs-5 fw-medium shadow-sm"><i class="fa-solid fa-paper-plane me-2"></i> Daftar Akun Pasien</button>
                </div>
                
                <div class="text-center mt-4">
                    <span class="text-slate-500 small">Sudah memiliki akun? <a href="{{ route('login', ['role' => 'pasien']) }}" class="text-primary fw-bold text-decoration-none ms-1">Masuk di sini</a></span>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Legalitas & Kebijakan Privasi -->
<div class="modal fade" id="legalModal" tabindex="-1" aria-labelledby="legalModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg">
            <div class="modal-header border-bottom-0 bg-primary bg-opacity-10 py-3">
                <h5 class="modal-title fw-bold text-primary" id="legalModalLabel">
                    <i class="fa-solid fa-scale-balanced me-2"></i> {{ __('messages.legal_title') ?? 'Dokumen Legalitas & Kebijakan Privasi' }}
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-slate-600 mb-4">{{ __('messages.legal_desc') ?? 'Sistem ini mematuhi standar perlindungan data pribadi dan rekam medis elektronik sesuai dengan hukum yang berlaku.' }}</p>
                
                <div class="row g-3">
                    <!-- Term of Service -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light rounded-3 transition-hover">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa-solid fa-file-contract fs-3 text-warning me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Terms of Service / Syarat Layanan</h6>
                                    <a href="{{ asset('legal/Service Terms v22 2021Nov18.pdf') }}" target="_blank" class="small text-decoration-none text-primary fw-medium">Buka Dokumen <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- UU PDP -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light rounded-3 transition-hover">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa-solid fa-file-shield fs-3 text-success me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Pelindungan Data Pribadi (UU PDP)</h6>
                                    <a href="{{ asset('legal/UU Nomor 27 Tahun 2022.pdf') }}" target="_blank" class="small text-decoration-none text-primary fw-medium">Buka UU No. 27 Th 2022 <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Permenkes / Rekam Medis -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light rounded-3 transition-hover">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa-solid fa-book-medical fs-3 text-danger me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Rekam Medis Elektronik (Permenkes)</h6>
                                    <a href="{{ asset('legal/permenkes-no-11-tahun-2025.pdf') }}" target="_blank" class="small text-decoration-none text-primary fw-medium">Buka Permenkes <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- HIPAA Basics -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light rounded-3 transition-hover">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa-solid fa-user-lock fs-3 text-info me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">HIPAA Privacy Rule Basics</h6>
                                    <a href="{{ asset('legal/mln909001_2025_05_hipaa_basics_final.pdf') }}" target="_blank" class="small text-decoration-none text-primary fw-medium">Read Guidelines <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- ISO 27001 -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light rounded-3 transition-hover">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa-solid fa-shield-halved fs-3 text-secondary me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Keamanan Informasi (ISO 27001)</h6>
                                    <a href="{{ asset('legal/NQA-ISO-27001-Implementation-Guide.pdf') }}" target="_blank" class="small text-decoration-none text-primary fw-medium">Buka Panduan <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Kebijakan Tambahan -->
                    <div class="col-md-6">
                        <div class="card h-100 border-0 bg-light rounded-3 transition-hover">
                            <div class="card-body d-flex align-items-center">
                                <i class="fa-solid fa-file-invoice fs-3 text-primary me-3"></i>
                                <div>
                                    <h6 class="fw-bold mb-1">Kebijakan Tambahan (EN)</h6>
                                    <a href="{{ asset('legal/cb6d9eca-en.pdf') }}" target="_blank" class="small text-decoration-none text-primary fw-medium">View Policy <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="alert alert-warning mt-4 mb-0 d-flex align-items-center">
                    <i class="fa-solid fa-circle-exclamation me-3 fs-4"></i>
                    <p class="mb-0 small text-dark">
                        Dengan menekan tombol <strong>"Saya Mengerti"</strong> atau melanjutkan pendaftaran, Anda menyatakan telah membaca, mengerti, dan menyetujui seluruh ketentuan di atas.
                    </p>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 justify-content-center pb-4">
                <button type="button" class="btn btn-primary px-5 py-2 fw-medium rounded-pill" data-bs-dismiss="modal">Saya Mengerti</button>
            </div>
        </div>
    </div>
</div>

@endsection
