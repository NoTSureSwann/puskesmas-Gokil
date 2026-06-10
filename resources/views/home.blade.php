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
                <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill fw-semibold mb-3">{{ __('messages.layanan_digital') }}</span>
                <h1 class="display-4 fw-extrabold mb-3">{!! __('messages.hero_title') !!}</h1>
                <p class="lead text-muted mb-4">{{ __('messages.hero_subtitle') }}</p>
                <div class="d-flex flex-column flex-sm-row gap-3">
                    <a href="{{ route('login') }}" class="btn btn-primary btn-lg"><i class="fa-solid fa-lock me-2"></i> {{ __('messages.btn_login') }}</a>
                </div>
            </div>
            <div class="col-lg-5 text-center d-none d-lg-block">
                <i class="fa-solid fa-hospital-user text-primary" style="font-size: 15rem; filter: drop-shadow(0 10px 20px rgba(16, 185, 129, 0.15));"></i>
            </div>
        </div>
    </div>

    <!-- EVENT CAROUSEL SECTION (Replacing Role Selection) -->
    <div class="mb-5">
        <div class="text-center mb-4">
            <h2 class="fw-bold">{{ __('messages.pengumuman_title') }}</h2>
            <p class="text-muted">{{ __('messages.pengumuman_subtitle') }}</p>
        </div>
        
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div id="eventCarousel" class="carousel slide carousel-fade shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel">
                    <div class="carousel-indicators">
                        <button type="button" data-bs-target="#eventCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#eventCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#eventCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>
                    <div class="carousel-inner">
                        <!-- Slide 1 -->
                        <div class="carousel-item active" data-bs-interval="5000">
                            <div class="d-flex align-items-center justify-content-center bg-primary bg-gradient text-white" style="height: 400px;">
                                <div class="text-center px-4">
                                    <i class="fa-solid fa-stethoscope mb-3" style="font-size: 4rem;"></i>
                                    <h3 class="fw-bold">{{ __('messages.slide_1_title') }}</h3>
                                    <p class="lead">{{ __('messages.slide_1_desc') }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Slide 2 -->
                        <div class="carousel-item" data-bs-interval="5000">
                            <div class="d-flex align-items-center justify-content-center bg-success bg-gradient text-white" style="height: 400px;">
                                <div class="text-center px-4">
                                    <i class="fa-solid fa-apple-whole mb-3" style="font-size: 4rem;"></i>
                                    <h3 class="fw-bold">{{ __('messages.slide_2_title') }}</h3>
                                    <p class="lead">{{ __('messages.slide_2_desc') }}</p>
                                </div>
                            </div>
                        </div>
                        <!-- Slide 3 -->
                        <div class="carousel-item" data-bs-interval="5000">
                            <div class="d-flex align-items-center justify-content-center bg-warning bg-gradient text-white" style="height: 400px;">
                                <div class="text-center px-4">
                                    <i class="fa-solid fa-mosquito mb-3" style="font-size: 4rem;"></i>
                                    <h3 class="fw-bold">{{ __('messages.slide_3_title') }}</h3>
                                    <p class="lead">{{ __('messages.slide_3_desc') }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#eventCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#eventCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- INFO CLINIC SECTION -->
    <div class="row g-4 py-5 border-top">
        <div class="col-md-4 text-center">
            <div class="info-icon-box"><i class="fa-solid fa-clock-rotate-left"></i></div>
            <h5 class="fw-bold">{{ __('messages.tepat_waktu_title') }}</h5>
            <p class="text-muted small px-3">{{ __('messages.tepat_waktu_desc') }}</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="info-icon-box"><i class="fa-solid fa-envelope-open-text"></i></div>
            <h5 class="fw-bold">{{ __('messages.notifikasi_title') }}</h5>
            <p class="text-muted small px-3">{{ __('messages.notifikasi_desc') }}</p>
        </div>
        <div class="col-md-4 text-center">
            <div class="info-icon-box"><i class="fa-solid fa-shield-halved"></i></div>
            <h5 class="fw-bold">{{ __('messages.resep_title') }}</h5>
            <p class="text-muted small px-3">{{ __('messages.resep_desc') }}</p>
        </div>
    </div>
</div>
</div>

<!-- kBot Widget -->
<div id="kbot-widget" style="position: fixed; bottom: 30px; right: 30px; z-index: 1050; display: flex; flex-direction: column; align-items: flex-end;">
    <!-- Chat Window (Hidden by default) -->
    <div id="kbot-chat-window" class="shadow-lg" style="display: none; width: 350px; height: 500px; background: white; border-radius: 15px; overflow: hidden; margin-bottom: 15px; border: 1px solid #e2e8f0; flex-direction: column;">
        <div style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; padding: 15px; font-weight: bold; display: flex; justify-content: space-between; align-items: center;">
            <span><i class="fa-solid fa-robot me-2"></i> kBot Enterprise</span>
            <button id="close-kbot" style="background: none; border: none; color: white; cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        
        <div id="kbot-messages" style="flex: 1; padding: 15px; overflow-y: auto; background: #f8fafc; font-size: 0.9rem;">
            <!-- Messages load here (Lazy Loaded) -->
            <div class="text-center text-muted small my-2" id="kbot-loading" style="display: none;">Memuat histori...</div>
            <div class="d-flex mb-3">
                <div class="bg-light p-3 rounded-3" style="max-width: 85%; border-bottom-left-radius: 0;">
                    Halo! Saya kBot. Ada keluhan apa hari ini?
                </div>
            </div>
        </div>
        
        <div style="padding: 10px; background: white; border-top: 1px solid #e2e8f0; display: flex;">
            <input type="text" id="kbot-input" class="form-control form-control-sm" placeholder="Tulis gejala atau keluhan..." style="border-radius: 20px; border-top-right-radius: 0; border-bottom-right-radius: 0; outline: none; box-shadow: none;">
            <button id="send-kbot" class="btn btn-primary btn-sm" style="border-radius: 20px; border-top-left-radius: 0; border-bottom-left-radius: 0;"><i class="fa-solid fa-paper-plane"></i></button>
        </div>
    </div>
    
    <!-- kBot Icon Button -->
    <button id="toggle-kbot" class="btn btn-success rounded-circle shadow-lg d-flex align-items-center justify-content-center" style="width: 60px; height: 60px; font-size: 1.5rem; transition: transform 0.2s;">
        <i class="fa-solid fa-headset"></i>
    </button>
</div>

@endsection

@section('scripts')
<script src="{{ asset('js/kbot.js') }}"></script>
@endsection
