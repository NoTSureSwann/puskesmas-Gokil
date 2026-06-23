@extends('layouts.app')

@section('title', 'ML Analytics & RLHF Dashboard')

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
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
    }
    .metric-card.synthetic {
        background: linear-gradient(135deg, #10b981, #3b82f6);
    }
    .metric-card.feedback {
        background: linear-gradient(135deg, #f59e0b, #ef4444);
    }
    .metric-title {
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        opacity: 0.9;
    }
    .metric-value {
        font-size: 2.25rem;
        font-weight: 800;
        margin: 0.5rem 0;
    }
    .uncertain-row:hover {
        background-color: #f8fafc;
        cursor: pointer;
    }
</style>
<!-- Menggunakan Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@endsection

@section('content')
<div class="container-fluid py-4 animated-fade">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-slate-800 mb-1"><i class="fa-solid fa-brain text-primary me-2"></i> ML Analytics & Active Learning</h3>
            <p class="text-slate-500 mb-0">Pantau performa model AI dan bantu klasifikasi data yang meragukan (RLHF).</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="fetchRealtimeData()"><i class="fa-solid fa-rotate-right me-1"></i> Refresh Data</button>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-1"></i> {{ session('status') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Metrics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="metric-card">
                <div class="metric-title">Total AI Datasets</div>
                <div class="metric-value">{{ number_format($stats['total_datasets']) }}</div>
                <div class="small"><i class="fa-solid fa-database me-1"></i> Entri Rekam Medis AI</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card synthetic">
                <div class="metric-title">Data Sintetis (Generated)</div>
                <div class="metric-value">{{ number_format($stats['total_synthetic']) }}</div>
                <div class="small"><i class="fa-solid fa-robot me-1"></i> Digenerasi oleh LLM</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card feedback">
                <div class="metric-title">Human Feedback (RLHF)</div>
                <div class="metric-value">{{ number_format($stats['total_feedbacks']) }}</div>
                <div class="small"><i class="fa-solid fa-user-doctor me-1"></i> Validasi oleh Dokter</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="metric-card" style="background: linear-gradient(135deg, #6366f1, #d946ef);">
                <div class="metric-title">Avg Confidence (Real Data)</div>
                <div class="metric-value">{{ number_format($stats['avg_confidence'] * 100, 1) }}%</div>
                <div class="small"><i class="fa-solid fa-bullseye me-1"></i> Akurasi Pemahaman NLU</div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="glass-panel h-100">
                <h5 class="fw-bold mb-3">Distribusi Confidence Score Model</h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="confidenceChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="glass-panel h-100">
                <h5 class="fw-bold mb-3">Tren Pertumbuhan Data Harian</h5>
                <div style="height: 300px; position: relative;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Human In The Loop / RLHF Validation -->
    <div class="glass-panel">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-list-check text-warning me-2"></i> Ruang Validasi (Human-in-the-Loop)</h5>
        <p class="text-slate-500 small mb-4">Berikut adalah antrean data di mana Model AI memiliki tingkat keyakinan (confidence) di bawah 70%. Bantu model untuk belajar dengan memberikan koreksi atau persetujuan.</p>

        @if($uncertainData->isEmpty())
            <div class="text-center py-5">
                <div class="bg-light rounded-circle p-4 d-inline-block mb-3">
                    <i class="fa-solid fa-check-double text-success" style="font-size: 2rem;"></i>
                </div>
                <h6 class="fw-bold">Tidak ada data meragukan</h6>
                <p class="text-muted small">Semua prediksi AI saat ini memiliki confidence yang tinggi atau sudah Anda validasi.</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu & Sumber</th>
                            <th>Input Keluhan Pasien (NLU)</th>
                            <th>Prediksi AI</th>
                            <th>Confidence</th>
                            <th class="text-center">Aksi RLHF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($uncertainData as $data)
                        <tr class="uncertain-row">
                            <td class="small">
                                <div>{{ $data->created_at->diffForHumans() }}</div>
                                <span class="badge bg-secondary">{{ $data->source }}</span>
                            </td>
                            <td><span class="text-wrap" style="max-width: 300px; display: block;">"{{ $data->raw_text }}"</span></td>
                            <td>
                                <span class="fw-bold text-primary">{{ $data->predicted_poli ?? 'Tidak Tahu' }}</span><br>
                                <span class="badge bg-light text-dark border mt-1">{{ implode(', ', $data->extracted_symptoms ?? []) }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                        <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $data->nlp_confidence_score * 100 }}%"></div>
                                    </div>
                                    <span class="ms-2 small fw-bold">{{ number_format($data->nlp_confidence_score * 100, 1) }}%</span>
                                </div>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#rlhfModal-{{ $data->id }}">
                                    <i class="fa-solid fa-gavel"></i> Validasi
                                </button>
                            </td>
                        </tr>

                        @endforeach
                    </tbody>
                </table>
            </div>
            
        @endif
    </div>
</div>

<!-- Modal RLHF (Dipindahkan ke luar tabel & kontainer glass-panel untuk menghindari isu z-index/backdrop Bootstrap) -->
@if(isset($uncertainData) && $uncertainData->isNotEmpty())
    @foreach($uncertainData as $data)
    <div class="modal fade" id="rlhfModal-{{ $data->id }}" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header border-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-robot text-primary me-2"></i> Latih Model AI (RLHF)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-light border">
                        <strong>Keluhan Asli:</strong><br>
                        "{{ $data->raw_text }}"
                    </div>
                    <form action="{{ route('dokter.ml.analytics.feedback', $data->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Poli yang Sebenarnya <span class="text-danger">*</span></label>
                            <select name="corrected_poli" class="form-select" required>
                                <option value="Poli Umum" {{ $data->predicted_poli == 'Poli Umum' ? 'selected' : '' }}>Poli Umum</option>
                                <option value="Poli Gigi" {{ $data->predicted_poli == 'Poli Gigi' ? 'selected' : '' }}>Poli Gigi</option>
                                <option value="Poli KIA" {{ $data->predicted_poli == 'Poli KIA' ? 'selected' : '' }}>Poli KIA (Ibu & Anak)</option>
                                <option value="Poli Gizi" {{ $data->predicted_poli == 'Poli Gizi' ? 'selected' : '' }}>Poli Gizi</option>
                            </select>
                            <div class="form-text">Bantu koreksi atau pastikan tebakan AI sudah benar.</div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Beri Penilaian (Reward Score) <span class="text-danger">*</span></label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="reward_score" id="btnradio1-{{$data->id}}" value="1" required>
                                <label class="btn btn-outline-success" for="btnradio1-{{$data->id}}"><i class="fa-solid fa-thumbs-up"></i> Tepat (+1)</label>

                                <input type="radio" class="btn-check" name="reward_score" id="btnradio2-{{$data->id}}" value="0">
                                <label class="btn btn-outline-secondary" for="btnradio2-{{$data->id}}"><i class="fa-solid fa-minus"></i> Netral (0)</label>

                                <input type="radio" class="btn-check" name="reward_score" id="btnradio3-{{$data->id}}" value="-1">
                                <label class="btn btn-outline-danger" for="btnradio3-{{$data->id}}"><i class="fa-solid fa-thumbs-down"></i> Keliru (-1)</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Catatan Klinis Tambahan (Opsional)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Mengapa prediksi ini keliru?"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100"><i class="fa-solid fa-paper-plane me-1"></i> Simpan Label (Submit RLHF)</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection

@section('scripts')
<script>
    let confChart, trendChart;

    function initCharts() {
        const ctxConf = document.getElementById('confidenceChart').getContext('2d');
        confChart = new Chart(ctxConf, {
            type: 'doughnut',
            data: {
                labels: ['High (>80%)', 'Medium (50-79%)', 'Low (<50%)'],
                datasets: [{
                    data: [0, 0, 0],
                    backgroundColor: ['#10b981', '#f59e0b', '#ef4444'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        const ctxTrend = document.getElementById('trendChart').getContext('2d');
        trendChart = new Chart(ctxTrend, {
            type: 'line',
            data: {
                labels: [],
                datasets: [
                    {
                        label: 'Organik (Real)',
                        data: [],
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Sintetis (AI Generated)',
                        data: [],
                        borderColor: '#10b981',
                        borderDash: [5, 5],
                        backgroundColor: 'transparent',
                        fill: false,
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

    function fetchRealtimeData() {
        fetch('{{ route('dokter.ml.analytics.data') }}')
            .then(res => res.json())
            .then(data => {
                // Update Confidence Chart
                confChart.data.datasets[0].data = data.confidence.data;
                confChart.update();

                // Update Trend Chart
                trendChart.data.labels = data.trends.labels;
                trendChart.data.datasets[0].data = data.trends.organic;
                trendChart.data.datasets[1].data = data.trends.synthetic;
                trendChart.update();
            })
            .catch(err => console.error("Error fetching ML data:", err));
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCharts();
        fetchRealtimeData();

        // Optional: Polling setiap 30 detik untuk dashboard "Real-Time"
        setInterval(fetchRealtimeData, 30000);
    });
</script>
@endsection
