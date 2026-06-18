@extends('layouts.app')

@section('title', 'Peta Persebaran Wabah & Prediksi AI - SI Puskesmas')

@section('styles')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin=""/>
<style>
    #map-container {
        height: 600px;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        border: 2px solid #fff;
        z-index: 1;
    }
    .glass-panel-ai {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.9);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.1);
        border-radius: 16px;
        padding: 1.5rem;
        height: 100%;
    }
    .ai-typing {
        display: inline-block;
        width: 1ch;
        animation: typing 1s steps(1) infinite;
    }
    @keyframes typing {
        0%, 100% { opacity: 1; }
        50% { opacity: 0; }
    }
    .badge-bahaya-Tinggi { background-color: #ef4444; color: white; }
    .badge-bahaya-Sedang { background-color: #f59e0b; color: white; }
    .badge-bahaya-Rendah { background-color: #3b82f6; color: white; }
    
    .map-popup-custom {
        font-family: var(--font-sans);
    }
    .map-popup-custom .leaflet-popup-content-wrapper {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .map-popup-custom .leaflet-popup-content {
        margin: 16px;
        line-height: 1.5;
    }
    .leaflet-control-attribution {
        font-size: 10px !important;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 animated-fade">
    <div class="row mb-4">
        <div class="col-12 text-center text-md-start d-flex flex-column flex-md-row justify-content-between align-items-center">
            <div>
                <h2 class="fw-bold text-slate-800 mb-1"><i class="fa-solid fa-earth-asia text-primary me-2"></i> Peta Wabah & Prediksi Geospasial</h2>
                <p class="text-slate-500 mb-0">Pantau persebaran penyakit menular di Indonesia secara real-time berdasarkan data klinis.</p>
            </div>
            @if(Auth::check())
                <a href="{{ route(Auth::user()->role . '.dashboard') }}" class="btn btn-outline-secondary mt-3 mt-md-0"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Dashboard</a>
            @else
                <a href="{{ route('home') }}" class="btn btn-outline-secondary mt-3 mt-md-0"><i class="fa-solid fa-house me-1"></i> Beranda Publik</a>
            @endif
        </div>
    </div>

    <div class="row g-3 mb-4">
        <!-- Live Counters -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-users fs-3"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-muted fw-medium">Kunjungan Hari Ini</p>
                        <h2 class="mb-0 fw-bold text-dark" id="stat-kunjungan">0</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="bg-danger bg-opacity-10 text-danger rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px;">
                        <i class="fa-solid fa-virus-covid fs-3"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-muted fw-medium">Kasus Bahaya Tinggi</p>
                        <h2 class="mb-0 fw-bold text-dark" id="stat-kasus-tinggi">0</h2>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-body p-4 d-flex align-items-center">
                    <div class="rounded-circle d-flex justify-content-center align-items-center me-3" style="width: 60px; height: 60px; background-color: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                        <i class="fa-solid fa-satellite-dish fs-3"></i>
                    </div>
                    <div>
                        <p class="mb-0 text-muted fw-medium">Klaster Wabah Aktif</p>
                        <h2 class="mb-0 fw-bold text-dark" id="stat-klaster">0</h2>
                        <small class="text-primary d-block mt-1 fw-bold" style="font-size: 0.7rem;"><i class="fa-solid fa-rotate fa-spin me-1"></i> Live Sync</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- MAP SECTION -->
        <div class="col-lg-8">
            <div id="map-container"></div>
            <div class="text-muted small mt-2 text-center text-lg-start">
                <i class="fa-solid fa-circle-info text-primary me-1"></i> Peta disajikan oleh OpenStreetMap & disokong oleh algoritma AI Geospasial.
            </div>
        </div>

        <!-- AI REPORT PANEL -->
        <div class="col-lg-4">
            <div class="glass-panel-ai d-flex flex-column">
                <div class="d-flex align-items-center mb-3 border-bottom pb-3">
                    <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                        <i class="fa-solid fa-robot text-primary fs-3"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0 text-slate-800">Laporan kBot AI <span class="badge bg-success bg-opacity-10 text-success small ms-1" style="font-size:0.6rem;">LIVE</span></h5>
                        <small class="text-slate-500">Analisis Jaringan Syaraf Geospasial</small>
                    </div>
                </div>

                <div id="ai-loading" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-3 text-slate-500 small fw-medium">Sedang memproses dan menyimpulkan data satelit spasial...</p>
                </div>

                <div id="ai-report-content" class="overflow-auto pe-2 flex-grow-1" style="display: none; max-height: 400px;">
                    <!-- Diisi oleh JS -->
                </div>
                
                <div class="mt-auto pt-3 border-top text-center mt-3" id="ai-action-btn" style="display:none;">
                    @if(Auth::check())
                        <a href="{{ route('pasien.daftar') }}" class="btn btn-primary w-100"><i class="fa-solid fa-calendar-check me-2"></i> Jadwalkan Pemeriksaan</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary w-100"><i class="fa-solid fa-lock me-2"></i> Login untuk Konsultasi</a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // 1. Inisialisasi Peta (Fokus di Tengah Indonesia)
        const map = L.map('map-container').setView([-2.5489, 118.0149], 5);

        // Tambahkan Tile Layer (OpenStreetMap - Gratis dan Cepat)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(map);

        // Custom Icons 
        const redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });
        
        const orangeIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-orange.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        const blueIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-blue.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        // Layer Group for easy clearing
        const markersLayer = L.layerGroup().addTo(map);

        // 2. Fetch Data Wabah Geospasial Function
        function fetchWabahData() {
            fetch('{{ route('api.wabah') }}')
                .then(response => response.json())
                .then(res => {
                    if(res.success) {
                        // Update Stats
                        if(res.data.stats) {
                            document.getElementById('stat-kunjungan').innerText = res.data.stats.kunjungan_hari_ini;
                            document.getElementById('stat-kasus-tinggi').innerText = res.data.stats.kasus_tingkat_tinggi;
                            document.getElementById('stat-klaster').innerText = res.data.stats.total_klaster_wabah;
                        }

                        if(res.data.outbreaks && res.data.outbreaks.length > 0) {
                            let aiHtml = '';
                            markersLayer.clearLayers(); // Clear old markers for real-time update
                            
                            res.data.outbreaks.forEach((wabah, index) => {
                                // Tentukan style marker & radius berdasarkan bahaya
                                let mIcon = blueIcon;
                                let circleColor = '#3b82f6';
                                
                                if(wabah.tingkat_bahaya === 'Tinggi') {
                                    mIcon = redIcon;
                                    circleColor = '#ef4444';
                                } else if (wabah.tingkat_bahaya === 'Sedang') {
                                    mIcon = orangeIcon;
                                    circleColor = '#f59e0b';
                                }

                                // Add Marker
                                const marker = L.marker([wabah.latitude, wabah.longitude], {icon: mIcon}).addTo(markersLayer);
                                
                                // Add Circle (Radius in meters, so km * 1000)
                                L.circle([wabah.latitude, wabah.longitude], {
                                    color: circleColor,
                                    fillColor: circleColor,
                                    fillOpacity: 0.2,
                                    radius: wabah.radius_km * 1000
                                }).addTo(markersLayer);

                                // Popup HTML
                                const popupHtml = `
                                    <div class="map-popup-custom">
                                        <h6 class="fw-bold mb-1 border-bottom pb-1">${wabah.nama_penyakit}</h6>
                                        <p class="mb-1 small text-muted"><i class="fa-solid fa-location-dot me-1"></i> ${wabah.kota}</p>
                                        <div class="d-flex justify-content-between align-items-center mt-2">
                                            <span class="badge badge-bahaya-${wabah.tingkat_bahaya} small">Bahaya: ${wabah.tingkat_bahaya}</span>
                                            <span class="fw-bold fs-6">${wabah.kasus_aktif} Kasus</span>
                                        </div>
                                    </div>
                                `;
                                marker.bindPopup(popupHtml);

                                // Susun Laporan AI
                                aiHtml += `
                                    <div class="card border-0 bg-light rounded-4 p-3 mb-3" style="animation: fadeIn 0.5s ease-out ${index * 0.2}s forwards; opacity:0;">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="fw-bold text-dark mb-0">${wabah.nama_penyakit}</h6>
                                            <span class="badge badge-bahaya-${wabah.tingkat_bahaya} rounded-pill" style="font-size:0.7rem;">${wabah.tingkat_bahaya}</span>
                                        </div>
                                        <p class="small text-muted mb-2"><i class="fa-solid fa-map-pin text-primary me-1"></i> ${wabah.kota} (Radius ${wabah.radius_km} km)</p>
                                        <div class="p-2 bg-white rounded border-start border-3 border-${wabah.tingkat_bahaya === 'Tinggi' ? 'danger' : (wabah.tingkat_bahaya === 'Sedang' ? 'warning' : 'primary')}">
                                            <p class="small mb-0 text-slate-700"><i class="fa-solid fa-comment-medical text-muted me-1"></i> ${wabah.rekomendasi_ai}</p>
                                        </div>
                                    </div>
                                `;
                            });

                            // Sembunyikan loading dan tampilkan laporan AI
                            setTimeout(() => {
                                document.getElementById('ai-loading').style.display = 'none';
                                document.getElementById('ai-report-content').innerHTML = aiHtml;
                                document.getElementById('ai-report-content').style.display = 'block';
                                document.getElementById('ai-action-btn').style.display = 'block';
                            }, 500); // Simulasi delay berpikir AI (dipercepat agar enak dipandang)
                        } else {
                            markersLayer.clearLayers();
                            document.getElementById('ai-loading').innerHTML = '<p class="text-success"><i class="fa-solid fa-check-circle me-1"></i> Tidak ada wabah terdeteksi saat ini.</p>';
                        }
                    }
                })
                .catch(err => {
                    console.error("Gagal mengambil data wabah:", err);
                    document.getElementById('ai-loading').innerHTML = '<p class="text-danger">Gagal memuat data AI Geospasial.</p>';
                });
        }

        // Panggil fungsi pertama kali
        fetchWabahData();

        // Setup AJAX Polling setiap 10 detik untuk realtime synchronization
        setInterval(fetchWabahData, 10000);
    });
</script>
@endsection
