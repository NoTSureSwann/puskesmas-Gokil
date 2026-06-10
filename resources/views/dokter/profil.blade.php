@extends('layouts.app')

@section('title', 'Profil Dokter - SI Puskesmas & Klinik')

@section('content')
<div class="row justify-content-center animated-fade">
    <div class="col-lg-8">
        <div class="card card-premium p-4 p-md-5 my-4">
            <div class="text-center mb-4">
                <i class="fa-solid fa-user-doctor text-primary fs-1 mb-2"></i>
                <h3 class="fw-bold">Profil Medis Saya</h3>
                <p class="text-muted">Kelola data kepegawaian dan Surat Izin Praktik (SIP) Dokter Anda.</p>
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

            <form action="{{ route('dokter.profil.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- BAGIAN 1: AKUN -->
                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-key me-2"></i> Akun Dokter</h5>
                <div class="row g-3 mb-4">
                    <div class="col-md-12">
                        <label for="name" class="form-label fw-medium small">Nama Lengkap Dokter (beserta gelar) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $user->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label for="email" class="form-label fw-medium small">Email Instansi (Read-only)</label>
                        <input type="email" class="form-control bg-light text-muted" id="email" value="{{ $user->email }}" readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label fw-medium small">Nomor HP Dokter <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" value="{{ old('phone', $user->phone) }}" required>
                    </div>
                </div>

                <!-- BAGIAN 2: DATA PROFESI -->
                <h5 class="fw-bold text-primary border-bottom pb-2 mb-3"><i class="fa-solid fa-id-card me-2"></i> Data Kepegawaian & Profesi</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nip" class="form-label fw-medium small">NIP / Nomor Registrasi</label>
                        <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" name="nip" value="{{ old('nip', $dokter->nip) }}">
                    </div>
                    <div class="col-md-6">
                        <label for="sip" class="form-label fw-medium small">Nomor SIP (Surat Izin Praktik)</label>
                        <input type="text" class="form-control @error('sip') is-invalid @enderror" id="sip" name="sip" value="{{ old('sip', $dokter->sip) }}" placeholder="Contoh: SIP/2026/0012/100">
                    </div>
                    <div class="col-md-6">
                        <label for="spesialisasi" class="form-label fw-medium small">Spesialisasi <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('spesialisasi') is-invalid @enderror" id="spesialisasi" name="spesialisasi" value="{{ old('spesialisasi', $dokter->spesialisasi) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-medium small">Poli yang Ditangani (Read-only)</label>
                        <input type="text" class="form-control bg-light text-muted" value="{{ $dokter->poli }}" readonly>
                    </div>
                </div>

                <div class="mt-5 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <a href="{{ route('dokter.dashboard') }}" class="btn btn-outline-secondary order-2 order-sm-1 w-100 w-sm-auto"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-primary text-white order-1 order-sm-2 w-100 w-sm-auto"><i class="fa-solid fa-floppy-disk me-1"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
