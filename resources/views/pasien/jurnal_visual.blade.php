@extends('layouts.app')

@section('content')
<div class="container py-4">
    <h2>Dashboard Visualisasi Jurnal RME</h2>
    <p class="text-muted">Pemantauan analisis kesehatan berdasarkan metrik AI</p>

    <!-- Tombol Ekspor & Import -->
    <div class="mb-4 d-flex justify-content-between align-items-center bg-light p-3 rounded border">
        <div>
            <!-- Tombol Ekspor PDF (Signed URL wajib dari backend, kita mock di sini jika tidak dipassing dari blade, atau gunakan helper URL::signedRoute) -->
            <a href="{{ URL::signedRoute('pasien.jurnal.export') }}" class="btn btn-danger">
                <i class="bi bi-file-earmark-pdf"></i> Unduh Jurnal Hukum (PDF)
            </a>
            <small class="text-muted ms-2">Terlindungi Anti-Intersepsi (UU ITE Psl 31)</small>
        </div>
        
        <form action="{{ route('pasien.jurnal.import') }}" method="POST" enctype="multipart/form-data" class="d-flex align-items-center">
            @csrf
            <input type="file" name="file_csv" accept=".csv" class="form-control me-2" required style="max-width: 250px;">
            <button type="submit" class="btn btn-success">Import CSV</button>
        </form>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row">
        <!-- Live System Monitor Widget -->
        <div class="col-12 mb-4">
            <div class="card bg-dark text-white shadow">
                <div class="card-body d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="bi bi-activity text-success"></i> Real-Time Activity Tracker</h5>
                        <small class="text-light">Mendukung pemantauan instan hingga 99+ concurrent users</small>
                    </div>
                    <div class="text-end">
                        <h2 class="mb-0 text-success fw-bold" id="liveUserCount">
                            <span class="spinner-grow spinner-grow-sm" role="status" aria-hidden="true"></span>
                        </h2>
                        <small>Pengguna Aktif Saat Ini</small>
                    </div>
                </div>
                <div class="card-footer bg-dark border-secondary">
                    <small id="liveLogText" class="text-muted">Menginisiasi koneksi monitoring...</small>
                </div>
            </div>
        </div>

        <!-- Pie Chart -->
        <div class="col-md-5">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0">Rasio Tingkat Keparahan</h5>
                </div>
                <div class="card-body">
                    <canvas id="severityPieChart" width="400" height="400"></canvas>
                </div>
            </div>
        </div>

        <!-- Line Chart -->
        <div class="col-md-7">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0">Tren Riwayat Kunjungan & Skor AI</h5>
                </div>
                <div class="card-body">
                    <canvas id="historyLineChart" width="400" height="280"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const pieCtx = document.getElementById('severityPieChart').getContext('2d');
        const lineCtx = document.getElementById('historyLineChart').getContext('2d');

        // Pie Chart
        new Chart(pieCtx, {
            type: 'pie',
            data: {
                labels: ['Ringan', 'Sedang', 'Kritis'],
                datasets: [{
                    data: [{{ $data['ringan'] }}, {{ $data['sedang'] }}, {{ $data['kritis'] }}],
                    backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // Line Chart
        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($data['labels_line']) !!},
                datasets: [{
                    label: 'Skor Keparahan AI',
                    data: {!! json_encode($data['data_line']) !!},
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13, 110, 253, 0.2)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, max: 10 }
                }
            }
        });

        // Real-Time Polling Script
        function fetchLiveTracking() {
            fetch("{{ route('pasien.jurnal.live-tracking') }}")
                .then(response => response.json())
                .then(data => {
                    // Update counter (Support 99+ visual indicator)
                    const countEl = document.getElementById('liveUserCount');
                    const count = data.active_users;
                    countEl.innerText = count > 99 ? '99+' : count;

                    // Update log text
                    const logEl = document.getElementById('liveLogText');
                    if (data.recent_logs.length > 0) {
                        const lastLog = data.recent_logs[0];
                        logEl.innerHTML = `Log terbaru: Aksi <span class="text-info">${lastLog.event}</span> tercatat ${lastLog.time}`;
                    } else {
                        logEl.innerText = 'Belum ada aktivitas dalam 15 menit terakhir.';
                    }
                })
                .catch(error => console.error('Error fetching live tracking:', error));
        }

        // Fetch first immediately, then every 3 seconds
        fetchLiveTracking();
        setInterval(fetchLiveTracking, 3000);
    });
</script>
@endsection
