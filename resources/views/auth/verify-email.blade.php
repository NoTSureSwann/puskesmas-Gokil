@extends('layouts.app')

@section('title', 'Verifikasi Email - SI Puskesmas & Klinik')

@section('content')
<div class="row justify-content-center animated-fade my-5">
    <div class="col-md-7">
        <div class="card card-premium p-4 p-md-5 text-center">
            <div class="mb-4">
                <i class="fa-solid fa-paper-plane text-primary" style="font-size: 4rem; filter: drop-shadow(0 8px 16px rgba(16, 185, 129, 0.2));"></i>
            </div>
            
            <h2 class="fw-bold mb-3">Verifikasi Email Anda</h2>
            
            <p class="text-muted mb-4 px-lg-4">
                Kami telah mengirimkan tautan verifikasi akun ke alamat email Anda. Silakan periksa kotak masuk (atau folder spam/promosi) email Anda untuk mengaktifkan akun.
            </p>

            @if (session('email'))
                <div class="alert alert-info alert-premium py-3 mb-4 mx-lg-4">
                    <span class="small fw-semibold text-muted">Email Terkirim Ke:</span><br>
                    <strong class="text-dark">{{ session('email') }}</strong>
                </div>
            @endif

            <p class="small text-muted mb-4">
                Setelah mengeklik tautan verifikasi dalam email tersebut, Anda dapat langsung masuk ke dashboard pasien.
            </p>

            <div class="d-flex justify-content-center gap-3">
                <a href="{{ route('home') }}" class="btn btn-outline-primary"><i class="fa-solid fa-house me-2"></i> Halaman Utama</a>
                <a href="{{ route('login', ['role' => 'pasien']) }}" class="btn btn-primary text-white"><i class="fa-solid fa-sign-in me-2"></i> Ke Halaman Login</a>
            </div>
        </div>
    </div>
</div>
@endsection
