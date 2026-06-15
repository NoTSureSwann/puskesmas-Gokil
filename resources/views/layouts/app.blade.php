<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA Settings -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#10b981">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="SI Puskesmas">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">

    <title>@yield('title', 'SI Puskesmas & Klinik')</title>
    
    <!-- Google Fonts: Outfit & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- FontAwesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/intersect@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --primary: #10b981;
            --primary-hover: #059669;
            --primary-light: #ecfdf5;
            --secondary: #0f172a;
            --bg-light: #f8fafc;
            --card-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.08), 0 1px 3px rgba(0, 0, 0, 0.03);
            --font-display: 'Outfit', sans-serif;
            --font-sans: 'Inter', sans-serif;
        }

        body {
            font-family: var(--font-sans);
            background-color: var(--bg-light);
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        h1, h2, h3, h4, h5, h6, .display-font {
            font-family: var(--font-display);
            font-weight: 600;
            color: var(--secondary);
        }

        /* Premium Buttons */
        .btn-primary {
            background-color: var(--primary);
            border-color: var(--primary);
            font-family: var(--font-display);
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        .btn-primary:hover, .btn-primary:focus {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }

        .btn-outline-primary {
            color: var(--primary);
            border-color: var(--primary);
            font-family: var(--font-display);
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: 8px;
            transition: all 0.25s ease;
        }

        .btn-outline-primary:hover {
            background-color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);
        }

        /* Glassmorphism & Sleek Cards */
        .card-premium {
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(241, 245, 249, 0.8);
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .card-premium:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.12);
        }

        /* Navbar Styling */
        .navbar-premium {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            padding: 1rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand-text {
            font-family: var(--font-display);
            font-weight: 800;
            font-size: 1.4rem;
            color: var(--secondary);
            background: linear-gradient(135deg, var(--secondary) 30%, var(--primary) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Alerts & Badges */
        .alert-premium {
            border-radius: 12px;
            border: none;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        }

        /* Footer */
        footer {
            margin-top: auto;
            background: var(--secondary);
            color: #e2e8f0; /* Terang */
            padding: 3rem 0;
            border-top: 4px solid var(--primary);
        }

        footer h5 {
            color: white;
            margin-bottom: 1.2rem;
            font-weight: 700;
        }

        footer p, footer li, footer span {
            color: #e2e8f0 !important; /* Force light text colors */
        }

        footer a {
            color: #38bdf8 !important; /* Beautiful sky blue for links on dark background */
            transition: all 0.2s ease;
        }

        footer a:hover {
            color: var(--primary) !important;
            text-decoration: underline !important;
        }

        .map-container {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Micro-animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animated-fade {
            animation: fadeIn 0.4s ease-out forwards;
        }

        /* Icon & Interaction Animations */
        .icon-bounce-hover { transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275); display: inline-block; }
        .icon-bounce-hover:hover { transform: scale(1.15) translateY(-2px); color: var(--primary); }
        .icon-spin-hover { transition: transform 0.4s ease-in-out; display: inline-block; }
        .icon-spin-hover:hover { transform: rotate(180deg); }
        
        @keyframes pulseSoft {
            0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .pulse-animation {
            animation: pulseSoft 2.5s infinite;
        }

        /* Animated Hamburger Menu */
        .navbar-toggler {
            width: 32px;
            height: 24px;
            position: relative;
            transition: .5s ease-in-out;
            cursor: pointer;
            padding: 0;
            background: transparent;
        }
        .navbar-toggler span {
            display: block;
            position: absolute;
            height: 3px;
            width: 100%;
            background: var(--primary);
            border-radius: 9px;
            opacity: 1;
            left: 0;
            transform: rotate(0deg);
            transition: .25s ease-in-out;
        }
        .navbar-toggler span:nth-child(1) { top: 0px; }
        .navbar-toggler span:nth-child(2) { top: 10px; }
        .navbar-toggler span:nth-child(3) { top: 20px; }
        .navbar-toggler:not(.collapsed) span:nth-child(1) { top: 10px; transform: rotate(135deg); }
        .navbar-toggler:not(.collapsed) span:nth-child(2) { opacity: 0; left: -60px; }
        .navbar-toggler:not(.collapsed) span:nth-child(3) { top: 10px; transform: rotate(-135deg); }
    </style>
    @yield('styles')
</head>
<body>

    <!-- NAVBAR -->
    <nav class="navbar navbar-expand-lg navbar-premium">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <span class="text-primary me-2 fs-3"><i class="fa-solid fa-stethoscope"></i></span>
                <span class="navbar-brand-text">Puskesmas & Klinik</span>
            </a>
            <button class="navbar-toggler border-0 collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span></span>
                <span></span>
                <span></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto align-items-center gap-3 mt-3 mt-lg-0">
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="{{ route('home') }}">{{ __('messages.beranda') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-dark fw-medium" href="{{ route('wabah.peta') }}"><i class="fa-solid fa-map-location-dot text-primary me-1"></i> Peta Wabah</a>
                    </li>
                    
                    <!-- Language Switcher -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark fw-medium" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fa-solid fa-earth-americas text-primary me-1"></i> {{ strtoupper(App::getLocale()) }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                            <li><a class="dropdown-item py-2 {{ App::getLocale() == 'id' ? 'active bg-primary text-white' : '' }}" href="{{ route('lang.switch', 'id') }}">🇮🇩 Indonesia</a></li>
                            <li><a class="dropdown-item py-2 {{ App::getLocale() == 'en' ? 'active bg-primary text-white' : '' }}" href="{{ route('lang.switch', 'en') }}">🇬🇧 English</a></li>
                        </ul>
                    </li>

                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle text-dark fw-medium" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fa-solid fa-circle-user text-primary me-1"></i> {{ Auth::user()->name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end border-0 shadow-sm rounded-3">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route(Auth::user()->role . '.dashboard') }}">
                                        <i class="fa-solid fa-chart-line me-2 text-muted"></i> Dashboard
                                    </a>
                                </li>
                                @if(Auth::user()->role === 'pasien')
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('pasien.tagihan.index') }}">
                                        <i class="fa-solid fa-file-invoice-dollar me-2 text-muted"></i> Tagihan Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('stunting') }}">
                                        <i class="fa-solid fa-baby me-2 text-muted"></i> Kalkulator Stunting
                                    </a>
                                </li>
                                @endif
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger py-2 w-100 border-0 bg-transparent">
                                            <i class="fa-solid fa-right-from-bracket me-2"></i> Keluar
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="btn btn-outline-primary" href="{{ route('login') }}">{{ __('messages.login') }}</a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary text-white" href="{{ route('register', ['role' => 'pasien']) }}">{{ __('messages.daftar_sebagai') }} Pasien</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <!-- MAIN CONTENT -->
    <main class="py-4">
        <div class="container">
            @if (session('status'))
                <div class="alert alert-success alert-premium d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-check me-2 fs-5"></i>
                    <div>{{ session('status') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-premium d-flex align-items-center" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2 fs-5"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- FOOTER -->
    <footer>
        <div class="container">
            <div class="row g-4">
                <!-- Col 1: Puskesmas Info -->
                <div class="col-md-3">
                    <h5 class="fw-bold text-white mb-3">Puskesmas & Klinik</h5>
                    <p class="small mb-3">Memberikan pelayanan kesehatan prima dan terpercaya untuk seluruh lapisan masyarakat Puskesmas.</p>
                    <p class="small mb-2"><i class="fa-solid fa-location-dot me-2 text-primary"></i> Jl. Sehat Sejahtera No. 45, Kecamatan Suka Makmur, Jakarta Pusat</p>
                    <p class="small mb-0"><i class="fa-solid fa-phone me-2 text-primary"></i> (021) 4247854</p>
                </div>

                <!-- Col 2: Hours -->
                <div class="col-md-3">
                    <h5 class="fw-bold text-white mb-3">Jam Layanan</h5>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2"><i class="fa-solid fa-calendar-days text-primary me-2"></i> Senin - Jumat:<br><span class="ps-4">07:30 - 16:00 WIB</span></li>
                        <li class="mb-2"><i class="fa-solid fa-calendar-day text-primary me-2"></i> Sabtu:<br><span class="ps-4">07:30 - 12:00 WIB</span></li>
                        <li><i class="fa-solid fa-circle-xmark text-danger me-2"></i> Minggu & Libur:<br><span class="ps-4 text-danger fw-bold">Tutup</span></li>
                    </ul>
                </div>

                <!-- Col 3: Contact Us -->
                <div class="col-md-3">
                    <h5 class="fw-bold text-white mb-3">Hubungi Kami</h5>
                    <ul class="list-unstyled small mb-0">
                        <li class="mb-2">
                            <i class="fa-brands fa-whatsapp text-success me-2 fs-5"></i>
                            <a href="https://wa.me/6281234567890" target="_blank" class="text-decoration-none">+62 812-3456-7890</a>
                        </li>
                        <li class="mb-2">
                            <i class="fa-regular fa-envelope text-primary me-2"></i>
                            <a href="mailto:info@puskesmas-sentosa.id" class="text-decoration-none">info@puskesmas-sentosa.id</a>
                        </li>
                        <li class="mb-2">
                            <i class="fa-brands fa-instagram text-danger me-2 fs-5"></i>
                            <a href="https://instagram.com/puskesmas.sentosa" target="_blank" class="text-decoration-none">@puskesmas.sentosa</a>
                        </li>
                        <li>
                            <i class="fa-solid fa-globe text-info me-2"></i>
                            <a href="#" class="text-decoration-none">puskesmas-sentosa.id</a>
                        </li>
                    </ul>
                </div>

                <!-- Col 4: Google Maps API/Embed Jakarta -->
                <div class="col-md-3">
                    <h5 class="fw-bold text-white mb-3">Lokasi Puskesmas</h5>
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.6263595604135!2d106.8249641!3d-6.1753924!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e69f5d45d3e4cf3%3A0x2db48dfcf703b74e!2sMonumen%20Nasional!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid" 
                            width="100%" 
                            height="120" 
                            style="border:0;" 
                            allowfullscreen="" 
                            loading="lazy" 
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                    <div class="mt-2 text-center">
                        <a href="https://maps.google.com/?q=Monumen+Nasional" target="_blank" class="small text-decoration-none text-info">
                            <i class="fa-solid fa-map-location-dot me-1"></i> Buka Petunjuk Arah
                        </a>
                    </div>
                </div>
            </div>
            <hr class="border-secondary my-4">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                <span class="small text-muted">&copy; 2026 Puskesmas & Klinik. All Rights Reserved.</span>
                <span class="small text-muted">Kelompok IV — BSI Jakarta, Metode Penelitian 2026</span>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Real-time Form Validation -->
    <script src="{{ asset('js/form-validation.js') }}" defer></script>
    @yield('scripts')
    <!-- PWA Service Worker Registration -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js')
                    .then((reg) => {
                        console.log('[PWA] Service Worker registered successfully:', reg.scope);
                    })
                    .catch((err) => {
                        console.error('[PWA] Service Worker registration failed:', err);
                    });
            });
        }
    </script>
</body>
</html>
