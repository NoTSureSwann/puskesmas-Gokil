@extends('layouts.app')

@section('title', 'Beranda - SI Puskesmas & Klinik')

@section('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        border-radius: 24px;
        padding: 5rem 3rem;
        margin-bottom: 4rem;
        position: relative;
        overflow: hidden;
    }
    
    .hero-circle {
        position: absolute;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(16, 185, 129, 0.1);
        top: -100px;
        right: -100px;
    }

    .role-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.8rem;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .role-card-pasien .role-icon { background: #d1fae5; color: #059669; }
    .role-card-dokter .role-icon { background: #e0f2fe; color: #0284c7; }
    .role-card-farmasi .role-icon { background: #fef3c7; color: #d97706; }
    .role-card-admin .role-icon { background: #f3e8ff; color: #7c3aed; }

    .role-card {
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 18px;
        padding: 2.2rem 1.8rem;
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        background: white;
    }

    .role-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.08);
    }
    
    .role-card:hover .role-icon {
        transform: scale(1.1) rotate(5deg);
    }

    .info-icon-box {
        font-size: 2rem;
        color: var(--primary);
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="animated-fade">
    <!-- HERO SECTION -->
    <div class="hero-section text-center text-lg-start d-flex align-items-center position-relative">
        <div class="hero-circle"></div>
        <div class="row align-items-center z-1 w-100 g-4">
            <div class="col-lg-7">
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold mb-3">Layanan Kesehatan Digital</span>
                <h1 class="display-4 fw-extrabold mb-3">Sistem Informasi<br>Puskesmas & Klinik</h1>
                <p class="lead text-muted mb-4">Layanan Pendaftaran Pasien Digital 24 Jam dan Pemrosesan Resep Obat Elektronik Secara Terintegrasi.</p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="{{ route('register', ['role' => 'pasien']) }}" class="btn btn-primary btn-lg shadow-sm text-white"><i class="fa-solid fa-user-plus me-2"></i> Daftar Sebagai Pasien</a>
                    <a href="{{ route('login') }}" class="btn btn-outline-primary btn-lg"><i class="fa-solid fa-lock me-2"></i> Login Petugas</a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="fa-solid fa-hospital-user text-primary" style="font-size: 15rem; filter: drop-shadow(0 10px 20px rgba(16, 185, 129, 0.15));"></i>
            </div>
        </div>
    </div>

    <!-- ROLE CARD SELECTION -->
    <div class="mb-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Pilih Peran Akses Anda</h2>
            <p class="text-muted">Masuk ke dashboard sesuai wewenang dan akun Anda</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <!-- PASIEN -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-card-pasien">
                    <div>
                        <div class="role-icon">
                            <i class="fa-solid fa-user-injured"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Pasien</h4>
                        <p class="text-muted small">Pendaftaran kunjungan, cek nomor antrian hari ini, dan pantau status resep obat elektronik Anda.</p>
                    </div>
                    <div class="mt-4 d-grid gap-2">
                        <a href="{{ route('login', ['role' => 'pasien']) }}" class="btn btn-outline-primary">Masuk</a>
                        <a href="{{ route('register', ['role' => 'pasien']) }}" class="btn btn-link btn-sm text-muted text-decoration-none">Belum punya akun? Daftar</a>
                    </div>
                </div>
            </div>

            <!-- DOKTER -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-card-dokter">
                    <div>
                        <div class="role-icon">
                            <i class="fa-solid fa-user-doctor"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Dokter</h4>
                        <p class="text-muted small">Kelola antrian pemeriksaan pasien di klinik, input resep elektronik (e-prescription) secara aman.</p>
                    </div>
                    <div class="mt-4 d-grid">
                        <a href="{{ route('login', ['role' => 'dokter']) }}" class="btn btn-outline-primary">Masuk</a>
                    </div>
                </div>
            </div>

            <!-- FARMASI -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-card-farmasi">
                    <div>
                        <div class="role-icon">
                            <i class="fa-solid fa-prescription-bottle-medical"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Farmasi</h4>
                        <p class="text-muted small">Kelola status antrian resep obat di Kanban Board, cetak struk etiket obat PDF, log transaksi SQLite.</p>
                    </div>
                    <div class="mt-4 d-grid">
                        <a href="{{ route('login', ['role' => 'farmasi']) }}" class="btn btn-outline-primary">Masuk</a>
                    </div>
                </div>
            </div>

            <!-- ADMIN -->
            <div class="col-md-6 col-lg-3">
                <div class="role-card role-card-admin">
                    <div>
                        <div class="role-icon">
                            <i class="fa-solid fa-user-gear"></i>
                        </div>
                        <h4 class="fw-bold text-dark">Admin</h4>
                        <p class="text-muted small">Manajemen master data obat & klinik (poli), pendaftaran staf dokter/farmasi, dan laporan kinerja sistem.</p>
                    </div>
                    <div class="mt-4 d-grid">
                        <a href="{{ route('login', ['role' => 'admin']) }}" class="btn btn-outline-primary">Masuk</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO CLINIC SECTION -->
    <div class="row g-4 py-5 border-top">
        <div class="col-md-4 text-center">
            <div class="info-icon-box"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <h5 class="fw-bold">Pelayanan Tepat Waktu</h5>
            <p class="text-muted small px-3">Antrian terpantau secara real-time mengurangi waktu tunggu pasien di ruang tunggu puskesmas.</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="info-icon-box"><i class="fa-solid fa-envelope-open-text"></i></div>
            <h5 class="fw-bold">Notifikasi Instan</h5>
            <p class="text-muted small px-3">Terima rincian antrian dan pemberitahuan obat siap ambil via Email secara otomatis.</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="info-icon-box"><i class="fa-solid fa-shield-halved"></i></div>
            <h5 class="fw-bold">Resep Elektronik</h5>
            <p class="text-muted small px-3">Resep dikirim langsung dari ruang dokter ke bagian apotek untuk meminimalkan kesalahan baca resep.</p>
        </div>
    </div>
</div>
@endsection
