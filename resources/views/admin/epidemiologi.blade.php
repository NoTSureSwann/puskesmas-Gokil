@extends('layouts.app')

@section('title', 'Dasbor Epidemiologi & Tren Penyakit - Admin')

@section('content')
<div class="card card-premium p-4 p-md-5 animated-fade my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-chart-line text-primary"></i> Dasbor Epidemiologi & Prediksi Wabah</h3>
            <p class="text-muted mb-0">Memantau tren penyakit berdasarkan ekstraksi keluhan kBot (30 Hari Terakhir).</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-primary"><i class="fa-solid fa-house"></i> Dashboard</a>
    </div>

    <div class="row g-4 mb-4">
        <!-- Urgency Stats -->
        <div class="col-md-4">
            <div class="card bg-danger text-white h-100 border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-white-50"><i class="fa-solid fa-triangle-exclamation"></i> Urgensi Tinggi (Red Tag)</h6>
                    <h2 class="fw-bold mb-0">{{ $urgencyStats['Tinggi'] ?? 0 }} Kasus</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-warning text-dark h-100 border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-dark-50"><i class="fa-solid fa-circle-exclamation"></i> Urgensi Sedang (Yellow Tag)</h6>
                    <h2 class="fw-bold mb-0">{{ $urgencyStats['Sedang'] ?? 0 }} Kasus</h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-success text-white h-100 border-0 rounded-4">
                <div class="card-body">
                    <h6 class="text-white-50"><i class="fa-solid fa-check-circle"></i> Urgensi Rendah (Green Tag)</h6>
                    <h2 class="fw-bold mb-0">{{ $urgencyStats['Rendah'] ?? 0 }} Kasus</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="card border border-2 rounded-4 h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0">Top 10 Prediksi Penyakit</h5>
                </div>
                <div class="card-body">
                    <canvas id="epidemiologiChart" height="300"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-4 mb-4">
            <div class="card border border-2 rounded-4 h-100">
                <div class="card-header bg-transparent border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold mb-0">Peringatan Wabah (AI Alert)</h5>
                </div>
                <div class="card-body">
                    @if(count($topDiseases) > 0)
                        @php 
                            $topDiseaseName = array_keys($topDiseases)[0];
                            $topDiseaseCount = array_values($topDiseases)[0];
                        @endphp
                        
                        @if($topDiseaseCount > 10)
                            <div class="alert alert-danger rounded-3">
                                <strong>🚨 Peringatan Lonjakan:</strong><br>
                                Terdeteksi lonjakan luar biasa pada kasus <b>{{ $topDiseaseName }}</b> ({{ $topDiseaseCount }} kasus). Disarankan untuk segera melakukan penyuluhan kesehatan terkait penyakit ini di wilayah sekitar.
                            </div>
                        @else
                            <div class="alert alert-success rounded-3">
                                <strong>✅ Situasi Terkendali:</strong><br>
                                Belum terdeteksi adanya lonjakan drastis dari satu jenis penyakit yang spesifik. Tren kasus <b>{{ $topDiseaseName }}</b> masih dalam batas normal ({{ $topDiseaseCount }} kasus).
                            </div>
                        @endif
                    @else
                        <div class="text-muted">Belum ada data penyakit yang terdeteksi dari kBot bulan ini.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('epidemiologiChart').getContext('2d');
    const chartData = @json($chartData);
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: chartData.labels,
            datasets: [{
                label: 'Jumlah Terdeteksi',
                data: chartData.data,
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            },
            plugins: {
                legend: { display: false }
            }
        }
    });
});
</script>
@endpush
