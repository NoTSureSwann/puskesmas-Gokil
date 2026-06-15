<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TV Antrean Puskesmas & Klinik</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&family=Inter:wght@400;600&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <style>
        :root {
            --primary: #10b981;
            --secondary: #0f172a;
            --bg-dark: #0f172a;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        h1, h2, h3, .display-font {
            font-family: 'Outfit', sans-serif;
        }
        .header {
            background: rgba(16, 185, 129, 0.1);
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .main-callout {
            background: linear-gradient(135deg, var(--primary) 0%, #059669 100%);
            border-radius: 24px;
            padding: 4rem 2rem;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(16, 185, 129, 0.4);
            animation: pulse-border 2s infinite;
            border: 4px solid transparent;
        }
        @keyframes pulse-border {
            0% { border-color: rgba(255,255,255,0.1); }
            50% { border-color: rgba(255,255,255,0.8); }
            100% { border-color: rgba(255,255,255,0.1); }
        }
        .number-display {
            font-size: 10rem;
            font-weight: 800;
            line-height: 1;
            margin: 1rem 0;
            font-family: 'Outfit', sans-serif;
            text-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        .poli-display {
            font-size: 3rem;
            font-weight: 600;
            background: rgba(0,0,0,0.2);
            padding: 1rem 2rem;
            border-radius: 16px;
            display: inline-block;
        }
        .history-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 16px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
        }
        .history-number {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--primary);
        }
        #activation-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.95);
            z-index: 9999;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
    </style>
</head>
<body>

    <!-- Overlay untuk aktivasi Auto-play Audio (Kewajiban Browser) -->
    <div id="activation-overlay">
        <h2 class="fw-bold mb-4 text-white"><i class="fa-solid fa-volume-high me-2 text-primary"></i> Layar Antrean Publik</h2>
        <p class="text-white-50 mb-4 text-center" style="max-width: 500px;">
            Browser mewajibkan interaksi pengguna sebelum suara dapat diputar otomatis. Silakan klik tombol di bawah untuk mengaktifkan sistem suara antrean.
        </p>
        <button id="btn-activate" class="btn btn-primary btn-lg fw-bold px-5 py-3 rounded-pill">
            <i class="fa-solid fa-play me-2"></i> Aktifkan Sistem & Mulai
        </button>
    </div>

    <!-- Header -->
    <div class="header">
        <div class="d-flex align-items-center gap-3">
            <i class="fa-solid fa-stethoscope fs-1 text-primary"></i>
            <div>
                <h3 class="mb-0 fw-bold">TV Antrean Puskesmas</h3>
                <div class="text-white-50 small">Sistem Pemanggilan Pintar Berbasis AI</div>
            </div>
        </div>
        <div class="text-end">
            <div id="clock" class="fs-2 fw-bold text-primary display-font">00:00:00</div>
            <div id="date" class="text-white-50">Memuat tanggal...</div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container-fluid flex-grow-1 p-4 d-flex flex-column">
        <div class="row g-4 flex-grow-1">
            <!-- Kolom Panggilan Saat Ini (Kiri) -->
            <div class="col-lg-8 d-flex flex-column justify-content-center">
                <div class="main-callout" id="current-callout">
                    <h2 class="text-white-50 fw-bold text-uppercase tracking-wider">Nomor Antrean</h2>
                    <div class="number-display" id="current-number">
                        @if($antrians->count() > 0)
                            {{ str_pad((string)$antrians->first()->no_antrian, 3, '0', STR_PAD_LEFT) }}
                        @else
                            ---
                        @endif
                    </div>
                    <div class="poli-display" id="current-poli">
                        @if($antrians->count() > 0)
                            {{ $antrians->first()->poli->nama_poli }}
                        @else
                            Menunggu Panggilan
                        @endif
                    </div>
                </div>
                <div class="text-center mt-4 text-white-50">
                    <i class="fa-solid fa-volume-up me-2"></i> <span id="status-text">Sistem Suara Aktif - Menunggu Panggilan Selanjutnya...</span>
                </div>
            </div>

            <!-- Kolom Riwayat Panggilan (Kanan) -->
            <div class="col-lg-4 d-flex flex-column">
                <h4 class="fw-bold mb-4 text-white-50"><i class="fa-solid fa-clock-rotate-left me-2"></i> Panggilan Sebelumnya</h4>
                <div class="flex-grow-1 overflow-hidden" id="history-container">
                    @foreach($antrians->skip(1)->take(4) as $antrian)
                        <div class="history-card d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-white-50 small text-uppercase">Nomor</div>
                                <div class="history-number">{{ str_pad((string)$antrian->no_antrian, 3, '0', STR_PAD_LEFT) }}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-white fw-bold fs-5">{{ $antrian->poli->nama_poli }}</div>
                                <div class="text-white-50 small">{{ $antrian->updated_at->diffForHumans() }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        // --- 1. Konfigurasi Jam & Tanggal ---
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').textContent = now.toLocaleTimeString('id-ID', { hour12: false });
            document.getElementById('date').textContent = now.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();

        // --- 2. Konfigurasi Speech API & Aktivasi Layar ---
        let isSystemActive = false;
        const synth = window.speechSynthesis;

        document.getElementById('btn-activate').addEventListener('click', () => {
            document.getElementById('activation-overlay').style.display = 'none';
            isSystemActive = true;
            
            // Trigger suara kosong untuk meminta izin browser
            const utterance = new SpeechSynthesisUtterance("Sistem antrean suara diaktifkan");
            utterance.lang = 'id-ID';
            utterance.volume = 0.5;
            synth.speak(utterance);
            
            document.getElementById('status-text').textContent = 'Sistem Suara Terhubung - Menunggu Panggilan...';
        });

        // --- 3. Fungsi Panggilan Suara ---
        function playVoiceAnnouncement(noAntrian, namaPoli) {
            if (!isSystemActive) return;

            // Mainkan nada "Ting Nong" (opsional jika file ada, atau gunakan default)
            try {
                const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
                audio.play();
            } catch(e) {}

            // Beri jeda 1 detik agar nada Ting Nong selesai, baru mulai bicara
            setTimeout(() => {
                const kalimat = `Nomor antrean, ${noAntrian.split('').join(' ')}, silakan menuju ke ${namaPoli}`;
                const utterance = new SpeechSynthesisUtterance(kalimat);
                utterance.lang = 'id-ID';
                utterance.rate = 0.85; // Sedikit dilambatkan agar jelas
                utterance.pitch = 1;
                synth.speak(utterance);
            }, 1000);
        }

        // --- 4. Integrasi Pusher/Reverb Real-time ---
        const reverbAppKey = "{{ env('REVERB_APP_KEY') }}";
        const reverbHost = "{{ env('REVERB_HOST', 'localhost') }}";
        const reverbPort = {{ env('REVERB_PORT', 8080) }};
        const reverbScheme = "{{ env('REVERB_SCHEME', 'http') }}";
        
        if (reverbAppKey) {
            const pusher = new Pusher(reverbAppKey, {
                wsHost: reverbHost,
                wsPort: reverbPort,
                wssPort: reverbPort,
                forceTLS: (reverbScheme === 'https'),
                disableStats: true,
                enabledTransports: ['ws', 'wss'],
                cluster: 'mt1'
            });

            const channel = pusher.subscribe('kunjungan-channel');
            channel.bind('App\\Events\\KunjunganUpdated', function(data) {
                if (data.status === 'dipanggil') {
                    // Ekstrak data
                    const rawNoAntrian = data.no_antrian;
                    const paddedNoAntrian = rawNoAntrian.toString().padStart(3, '0');
                    const poliNama = data.poli_nama || 'Poli Tujuan';

                    // 1. Geser UI (Simulasi pindah data dari Main ke History)
                    const currentNoStr = document.getElementById('current-number').textContent.trim();
                    const currentPoliStr = document.getElementById('current-poli').textContent.trim();
                    
                    if (currentNoStr !== '---') {
                        const historyContainer = document.getElementById('history-container');
                        const newCard = document.createElement('div');
                        newCard.className = 'history-card d-flex justify-content-between align-items-center';
                        newCard.innerHTML = `
                            <div>
                                <div class="text-white-50 small text-uppercase">Nomor</div>
                                <div class="history-number">${currentNoStr}</div>
                            </div>
                            <div class="text-end">
                                <div class="text-white fw-bold fs-5">${currentPoliStr}</div>
                                <div class="text-white-50 small">Baru saja</div>
                            </div>
                        `;
                        historyContainer.prepend(newCard);
                        if (historyContainer.children.length > 4) {
                            historyContainer.removeChild(historyContainer.lastChild);
                        }
                    }

                    // 2. Update UI Main Callout
                    document.getElementById('current-number').textContent = paddedNoAntrian;
                    document.getElementById('current-poli').textContent = poliNama;

                    // 3. Mainkan Panggilan Suara
                    playVoiceAnnouncement(paddedNoAntrian, poliNama);
                    
                    // Efek visual kedip layar
                    const callout = document.getElementById('current-callout');
                    callout.style.background = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                    setTimeout(() => {
                        callout.style.background = 'linear-gradient(135deg, var(--primary) 0%, #059669 100%)';
                    }, 4000);
                }
            });
        }
    </script>
</body>
</html>
