@extends('layouts.app')

@section('title', 'Dashboard Farmasi - SI Puskesmas & Klinik')

@section('styles')
<style>
    .kanban-board {
        display: flex;
        gap: 1.5rem;
        margin-top: 2rem;
    }
    .kanban-column {
        flex: 1;
        background-color: #f1f5f9;
        border-radius: 16px;
        padding: 1.2rem;
        min-height: 500px;
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);
    }
    .kanban-header {
        font-family: var(--font-display);
        font-weight: 700;
        font-size: 1.1rem;
        color: var(--secondary);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.2rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(226, 232, 240, 0.8);
    }
    .kanban-card {
        background: white;
        border-radius: 12px;
        padding: 1.2rem;
        margin-bottom: 1rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        border-left: 5px solid #cbd5e1;
        transition: all 0.25s ease;
    }
    .kanban-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
    }
    .kanban-card.prio-urgen {
        border-left-color: #ef4444;
        background-color: #fff5f5;
    }
    .kanban-card.prio-normal {
        border-left-color: var(--primary);
    }
    .prio-badge {
        font-size: 0.65rem;
        padding: 0.2rem 0.5rem;
        border-radius: 50px;
        font-weight: 700;
        text-transform: uppercase;
    }
    .prio-badge-urgen { background-color: #fee2e2; color: #ef4444; }
    .prio-badge-normal { background-color: #ecfdf5; color: #10b981; }
</style>
@endsection

@section('content')
<div class="animated-fade">
    <!-- Welcome Header -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Dashboard Farmasi & Apotek</h2>
            <p class="text-muted mb-0">Kelola antrian resep obat elektronik secara real-time.</p>
        </div>
        <div class="small text-muted bg-white p-2 border rounded shadow-sm">
            <i class="fa-solid fa-circle text-success me-1"></i> Pusher: <strong id="pusher-status">Connecting...</strong>
        </div>
    </div>

    <!-- Stats summary -->
    <div class="row g-3 text-center mb-4">
        <div class="col-sm-3">
            <div class="bg-white p-3 rounded-3 shadow-sm border">
                <span class="text-muted small">Total Resep Hari Ini</span>
                <h4 class="fw-bold mb-0 text-dark">{{ $totalReseps }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="bg-white p-3 rounded-3 shadow-sm border border-warning">
                <span class="text-muted small text-warning">Menunggu</span>
                <h4 class="fw-bold mb-0 text-warning">{{ $menungguCount }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="bg-white p-3 rounded-3 shadow-sm border border-primary">
                <span class="text-muted small text-primary">Diproses</span>
                <h4 class="fw-bold mb-0 text-primary">{{ $diprosesCount }}</h4>
            </div>
        </div>
        <div class="col-sm-3">
            <div class="bg-white p-3 rounded-3 shadow-sm border border-success">
                <span class="text-muted small text-success">Selesai</span>
                <h4 class="fw-bold mb-0 text-success">{{ $selesaiCount }}</h4>
            </div>
        </div>
    </div>

    <!-- Kanban Board Grid -->
    <div class="kanban-board flex-column flex-lg-row">
        <!-- COLUMN 1: MENUNGGU -->
        <div class="kanban-column">
            <div class="kanban-header">
                <span><i class="fa-solid fa-hourglass-start text-warning me-2"></i> MENUNGGU</span>
                <span class="badge bg-warning text-dark">{{ $resepsMenunggu->count() }}</span>
            </div>
            
            <div class="kanban-cards-wrapper">
                @forelse ($resepsMenunggu as $rsp)
                    <div class="kanban-card prio-{{ $rsp->prioritas }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge prio-badge prio-badge-{{ $rsp->prioritas }}">{{ $rsp->prioritas }}</span>
                            <span class="small text-muted"><i class="fa-solid fa-clock me-1"></i> {{ $rsp->jam_input_resep->format('H:i') }} WIB</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $rsp->no_resep }}</h6>
                        <div class="small text-dark fw-medium mb-1">{{ $rsp->kunjungan->pasien->user->name }}</div>
                        <div class="small text-muted mb-3"><i class="fa-solid fa-pills me-1"></i> {{ $rsp->detailResep->count() }} item obat</div>
                        
                        <div class="d-grid">
                            <form action="{{ route('farmasi.resep.start', $rsp->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-primary w-100 text-white"><i class="fa-solid fa-gears me-1"></i> Mulai Proses</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 small">Tidak ada resep antrian.</div>
                @endforelse
            </div>
        </div>

        <!-- COLUMN 2: DIPROSES -->
        <div class="kanban-column">
            <div class="kanban-header">
                <span><i class="fa-solid fa-spinner fa-spin text-primary me-2"></i> DIPROSES</span>
                <span class="badge bg-primary">{{ $resepsDiproses->count() }}</span>
            </div>
            
            <div class="kanban-cards-wrapper">
                @forelse ($resepsDiproses as $rsp)
                    <div class="kanban-card prio-{{ $rsp->prioritas }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge prio-badge prio-badge-{{ $rsp->prioritas }}">{{ $rsp->prioritas }}</span>
                            <span class="small text-muted"><i class="fa-solid fa-clock me-1"></i> {{ $rsp->jam_input_resep->format('H:i') }} WIB</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $rsp->no_resep }}</h6>
                        <div class="small text-dark fw-medium mb-1">{{ $rsp->kunjungan->pasien->user->name }}</div>
                        <div class="small text-muted mb-3"><i class="fa-solid fa-pills me-1"></i> {{ $rsp->detailResep->count() }} item obat</div>
                        
                        <div class="d-grid">
                            <a href="{{ route('farmasi.resep.showProcess', $rsp->id) }}" class="btn btn-sm btn-warning text-dark fw-bold"><i class="fa-solid fa-check-double me-1"></i> Selesaikan</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 small">Tidak ada resep diproses.</div>
                @endforelse
            </div>
        </div>

        <!-- COLUMN 3: SELESAI -->
        <div class="kanban-column">
            <div class="kanban-header">
                <span><i class="fa-solid fa-circle-check text-success me-2"></i> SELESAI</span>
                <span class="badge bg-success">{{ $resepsSelesai->count() }}</span>
            </div>
            
            <div class="kanban-cards-wrapper">
                @forelse ($resepsSelesai as $rsp)
                    <div class="kanban-card prio-{{ $rsp->prioritas }}">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="badge prio-badge prio-badge-{{ $rsp->prioritas }}">{{ $rsp->prioritas }}</span>
                            <span class="small text-muted"><i class="fa-solid fa-check-circle text-success me-1"></i> {{ $rsp->jam_selesai_farmasi ? $rsp->jam_selesai_farmasi->format('H:i') : '' }}</span>
                        </div>
                        <h6 class="fw-bold text-dark mb-1">{{ $rsp->no_resep }}</h6>
                        <div class="small text-dark fw-medium mb-1">{{ $rsp->kunjungan->pasien->user->name }}</div>
                        <div class="small text-muted mb-3"><i class="fa-solid fa-circle-check text-success me-1"></i> Selesai diracik</div>
                        
                        <div class="d-grid">
                            <a href="{{ route('farmasi.resep.cetak', $rsp->id) }}" target="_blank" class="btn btn-sm btn-outline-success"><i class="fa-solid fa-print me-1"></i> Cetak Struk / Etiket</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-5 small">Belum ada resep diselesaikan.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Include Pusher and Echo if needed, else fallback -->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    // Inisialisasi Pusher untuk listening realtime resep baru
    const pusherAppKey = "{{ env('PUSHER_APP_KEY') }}";
    const pusherCluster = "{{ env('PUSHER_APP_CLUSTER', 'ap1') }}";
    
    if (pusherAppKey) {
        try {
            const pusher = new Pusher(pusherAppKey, {
                cluster: pusherCluster,
                forceTLS: true
            });

            const statusEl = document.getElementById('pusher-status');
            pusher.connection.bind('state_change', function(states) {
                statusEl.innerText = states.current.toUpperCase();
                if (states.current === 'connected') {
                    statusEl.className = "text-success";
                } else if (states.current === 'unavailable' || states.current === 'failed') {
                    statusEl.className = "text-danger";
                }
            });

            const channel = pusher.subscribe('farmasi-dashboard');
            channel.bind('App\\Events\\ResepBaru', function(data) {
                console.log('Resep Baru Real-time:', data);
                
                // Play sound alert
                try {
                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-600.wav');
                    audio.play();
                } catch(e) { console.log(e); }
                
                // Reload dashboard secara otomatis untuk memperbarui kolom
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });
        } catch (e) {
            console.error('Gagal memuat Pusher:', e);
            document.getElementById('pusher-status').innerText = "FAILED";
        }
    } else {
        document.getElementById('pusher-status').innerText = "LOG (POLLING)";
    }
</script>
@endsection
