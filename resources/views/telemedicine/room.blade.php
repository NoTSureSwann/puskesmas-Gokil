@extends('layouts.app')

@section('title', 'Ruang Telemedisin - ' . $kunjungan->no_kunjungan)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold mb-0 text-primary">
        <i class="fa-solid fa-video me-2"></i> Ruang Konsultasi Telemedisin
    </h3>
    <a href="{{ Auth::user()->role === 'pasien' ? route('pasien.dashboard') : route('dokter.dashboard') }}" class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Dashboard
    </a>
</div>

<div class="row">
    @if(Auth::user()->role === 'dokter')
        <div class="col-md-8 mb-4">
    @else
        <div class="col-md-12 mb-4">
    @endif
        <div class="card card-premium overflow-hidden">
            <div class="card-body p-0">
                <!-- Jitsi Meet iframe will be injected here -->
                <div id="jitsi-container" style="height: 600px; width: 100%;"></div>
            </div>
        </div>
    </div>
    
    @if(Auth::user()->role === 'dokter')
        <div class="col-md-4">
            <div class="card card-premium mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-user-injured me-2"></i> Data Pasien</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm mb-0">
                        <tr>
                            <td class="text-muted" width="100">Nama</td>
                            <td class="fw-bold">: {{ $kunjungan->pasien->user->name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>: {{ $kunjungan->pasien->nik }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keluhan</td>
                            <td>: {{ $kunjungan->keluhan }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Usia</td>
                            <td>: {{ \Carbon\Carbon::parse($kunjungan->pasien->tanggal_lahir)->age }} Tahun</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alergi</td>
                            <td>: {{ $kunjungan->pasien->riwayat_alergi ?: 'Tidak ada' }}</td>
                        </tr>
                    </table>
                </div>
                <div class="card-footer bg-transparent">
                    <a href="{{ route('dokter.pasien.riwayat', $kunjungan->pasien->nik) }}" target="_blank" class="btn btn-outline-primary w-100">
                        <i class="fa-solid fa-file-medical me-2"></i> Buka Rekam Medis
                    </a>
                </div>
            </div>
            
            <div class="card card-premium">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0 text-white"><i class="fa-solid fa-prescription-bottle-medical me-2"></i> Tindakan Lanjut</h5>
                </div>
                <div class="card-body">
                    <a href="{{ route('dokter.resep.create', $kunjungan->id) }}" class="btn btn-success w-100 mb-2">
                        <i class="fa-solid fa-pen-to-square me-2"></i> Buat Resep / Rekam Medis
                    </a>
                    <form action="{{ route('dokter.kunjungan.periksa', $kunjungan->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary w-100">
                            <i class="fa-solid fa-check-double me-2"></i> Tandai Sedang Diperiksa
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@section('scripts')
<script src='https://meet.jit.si/external_api.js'></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const domain = 'meet.jit.si';
        const options = {
            roomName: '{{ $kunjungan->telemedisin_room }}',
            width: '100%',
            height: 600,
            parentNode: document.querySelector('#jitsi-container'),
            userInfo: {
                displayName: '{{ Auth::user()->name }}'
            },
            configOverwrite: { 
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                prejoinPageEnabled: false
            },
            interfaceConfigOverwrite: {
                filmStripOnly: false,
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                    'fodeviceselection', 'hangup', 'profile', 'chat', 'recording',
                    'livestreaming', 'etherpad', 'sharedvideo', 'settings', 'raisehand',
                    'videoquality', 'filmstrip', 'invite', 'feedback', 'stats', 'shortcuts',
                    'tileview', 'videobackgroundblur', 'download', 'help', 'mute-everyone'
                ],
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
        
        // Listen for when a user leaves the meeting
        api.addEventListener('videoConferenceLeft', () => {
            // Optional: Show a message or redirect
            console.log('User left the conference');
        });
    });
</script>
@endsection
