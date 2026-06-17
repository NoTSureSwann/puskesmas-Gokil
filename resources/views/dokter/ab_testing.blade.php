@extends('layouts.app')

@section('title', 'A/B Testing Models Dashboard')

@section('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        border-radius: 1.25rem;
        padding: 1.5rem;
    }
    .metric-card {
        border-radius: 1rem;
        padding: 1.25rem;
        color: white;
        transition: transform 0.3s;
    }
    .metric-card:hover {
        transform: translateY(-5px);
    }
    .metric-card.v1 {
        background: linear-gradient(135deg, #3b82f6, #2563eb);
    }
    .metric-card.v2 {
        background: linear-gradient(135deg, #10b981, #059669);
    }
    .metric-title {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.9;
    }
    .metric-value {
        font-size: 2.5rem;
        font-weight: 800;
        margin: 0.5rem 0;
    }
    .vs-badge {
        background: linear-gradient(135deg, #ef4444, #f97316);
        color: white;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="container-fluid py-4 animated-fade">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-slate-800 mb-1"><i class="fa-solid fa-code-compare text-primary me-2"></i> A/B Testing Models</h3>
            <p class="text-slate-500 mb-0">Komparasi performa AI Model V1 (Baseline) vs AI Model V2 (Challenger) secara Real-Time.</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="fetchABData()"><i class="fa-solid fa-rotate-right me-1"></i> Refresh Data</button>
        </div>
    </div>

    <!-- Head to Head Metrics -->
    <div class="row align-items-center mb-5 position-relative">
        
        <!-- V1 Card -->
        <div class="col-md-5">
            <div class="metric-card v1 text-center">
                <div class="metric-title"><i class="fa-solid fa-robot me-1"></i> Model V1 (Baseline)</div>
                <div class="row mt-4">
                    <div class="col-4">
                        <div class="small opacity-75">Total Requests</div>
                        <h4 class="fw-bold mt-1" id="v1-count">0</h4>
                    </div>
                    <div class="col-4 border-start border-white-50">
                        <div class="small opacity-75">Avg Confidence</div>
                        <h4 class="fw-bold mt-1" id="v1-conf">0%</h4>
                    </div>
                    <div class="col-4 border-start border-white-50">
                        <div class="small opacity-75">RLHF Reward</div>
                        <h4 class="fw-bold mt-1" id="v1-reward">0.0</h4>
                    </div>
                </div>
            </div>
        </div>

        <!-- VS Badge -->
        <div class="col-md-2 d-flex justify-content-center my-3 my-md-0 position-relative z-index-1">
            <div class="vs-badge">VS</div>
        </div>

        <!-- V2 Card -->
        <div class="col-md-5">
            <div class="metric-card v2 text-center">
                <div class="metric-title"><i class="fa-solid fa-flask text-white me-1"></i> Model V2 (Challenger)</div>
                <div class="row mt-4">
                    <div class="col-4">
                        <div class="small opacity-75">Total Requests</div>
                        <h4 class="fw-bold mt-1" id="v2-count">0</h4>
                    </div>
                    <div class="col-4 border-start border-white-50">
                        <div class="small opacity-75">Avg Confidence</div>
                        <h4 class="fw-bold mt-1" id="v2-conf">0%</h4>
                    </div>
                    <div class="col-4 border-start border-white-50">
                        <div class="small opacity-75">RLHF Reward</div>
                        <h4 class="fw-bold mt-1" id="v2-reward">0.0</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="glass-panel h-100">
                <h5 class="fw-bold mb-3">Tingkat Keyakinan (Confidence Score)</h5>
                <canvas id="confidenceBarChart" height="250"></canvas>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="glass-panel h-100">
                <h5 class="fw-bold mb-3">Tren Request Harian</h5>
                <canvas id="trendLineChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let confChart, trendChart;

    function initCharts() {
        // Bar Chart for Confidence
        const ctxConf = document.getElementById('confidenceBarChart').getContext('2d');
        confChart = new Chart(ctxConf, {
            type: 'bar',
            data: {
                labels: ['Model V1', 'Model V2'],
                datasets: [{
                    label: 'Avg Confidence Score (%)',
                    data: [0, 0],
                    backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(16, 185, 129, 0.8)'],
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, max: 100 }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });

        // Line Chart for Trends
        const ctxTrend = document.getElementById('trendLineChart').getContext('2d');
        trendChart = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Requests V1',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Requests V2',
                        data: [],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    }

    function fetchABData() {
        fetch('{{ route('dokter.ml.analytics.ab_testing_data') }}')
            .then(res => res.json())
            .then(data => {
                // Update DOM Metrics
                document.getElementById('v1-count').innerText = data.stats.v1.count;
                document.getElementById('v1-conf').innerText = (data.stats.v1.avg_confidence * 100).toFixed(1) + '%';
                document.getElementById('v1-reward').innerText = parseFloat(data.stats.v1.avg_reward).toFixed(2);

                document.getElementById('v2-count').innerText = data.stats.v2.count;
                document.getElementById('v2-conf').innerText = (data.stats.v2.avg_confidence * 100).toFixed(1) + '%';
                document.getElementById('v2-reward').innerText = parseFloat(data.stats.v2.avg_reward).toFixed(2);

                // Update Confidence Chart
                confChart.data.datasets[0].data = [
                    data.stats.v1.avg_confidence * 100, 
                    data.stats.v2.avg_confidence * 100
                ];
                confChart.update();

                // Update Trend Chart
                trendChart.data.labels = data.trends.labels;
                trendChart.data.datasets[0].data = data.trends.v1;
                trendChart.data.datasets[1].data = data.trends.v2;
                trendChart.update();
            })
            .catch(err => console.error("Error fetching A/B Testing data:", err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        fetchABData();
        setInterval(fetchABData, 30000); // 30s polling
    });
</script>
@endsection
