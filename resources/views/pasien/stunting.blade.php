@extends('layouts.app')

@section('title', 'Kalkulator Stunting WHO - SI Puskesmas')

@section('styles')
<style>
    .glass-panel {
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.5);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.05);
        border-radius: 1.25rem;
        padding: 2rem;
    }
    .result-box {
        transition: all 0.4s ease;
        opacity: 0;
        transform: translateY(20px);
        display: none;
    }
    .result-box.show {
        opacity: 1;
        transform: translateY(0);
        display: block;
    }
    .spinner-border {
        display: none;
    }
    .loading .spinner-border {
        display: inline-block;
    }
    .loading .btn-text {
        display: none;
    }
    .status-icon {
        font-size: 3rem;
        margin-bottom: 1rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid py-4 animated-fade">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="fw-bold mb-1 text-slate-800">Kalkulator Stunting WHO <i class="fa-solid fa-baby text-primary ms-2"></i></h3>
                <p class="text-slate-500 mb-0">Pantau status gizi dan pertumbuhan tinggi badan anak Anda (0-60 bulan).</p>
            </div>
            <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
        </div>
    </div>

    <div class="row">
        <!-- FORM KALKULATOR -->
        <div class="col-lg-5 mb-4">
            <div class="glass-panel">
                <h5 class="fw-bold text-primary mb-4"><i class="fa-solid fa-calculator me-2"></i> Input Data Anak</h5>
                <form id="stuntingForm">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-medium small text-slate-700">Umur Anak (Bulan) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" class="form-control" id="umur_bulan" name="umur_bulan" placeholder="0 - 60" min="0" max="60" required>
                            <span class="input-group-text">Bulan</span>
                        </div>
                        <div class="form-text">Contoh: 24 (untuk anak usia 2 tahun).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-medium small text-slate-700 d-block">Jenis Kelamin <span class="text-danger">*</span></label>
                        <div class="btn-group w-100" role="group">
                            <input type="radio" class="btn-check" name="jenis_kelamin" id="jk_l" value="L" autocomplete="off" checked>
                            <label class="btn btn-outline-primary" for="jk_l"><i class="fa-solid fa-mars me-1"></i> Laki-Laki</label>

                            <input type="radio" class="btn-check" name="jenis_kelamin" id="jk_p" value="P" autocomplete="off">
                            <label class="btn btn-outline-primary" for="jk_p"><i class="fa-solid fa-venus me-1"></i> Perempuan</label>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-medium small text-slate-700">Tinggi / Panjang Badan <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.1" class="form-control" id="tinggi_badan" name="tinggi_badan" placeholder="Misal: 85.5" required>
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2 fs-6 fw-bold shadow-sm" id="btnSubmit">
                        <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                        <span class="btn-text"><i class="fa-solid fa-chart-line me-2"></i> Kalkulasi Z-Score</span>
                    </button>
                </form>
            </div>
        </div>

        <!-- AREA HASIL -->
        <div class="col-lg-7">
            <!-- State Kosong -->
            <div class="glass-panel text-center py-5 h-100 d-flex flex-column justify-content-center align-items-center" id="emptyState">
                <div class="bg-light rounded-circle p-4 mb-3 d-inline-block text-slate-400">
                    <i class="fa-solid fa-clipboard-check" style="font-size: 3rem;"></i>
                </div>
                <h5 class="text-slate-600 fw-bold">Belum Ada Hasil</h5>
                <p class="text-slate-500 small w-75 mx-auto">Silakan masukkan data umur, jenis kelamin, dan tinggi badan anak Anda pada form di sebelah kiri untuk melihat hasil evaluasi gizi.</p>
            </div>

            <!-- State Hasil -->
            <div class="glass-panel result-box h-100" id="resultState">
                <div class="text-center mb-4">
                    <i class="fa-solid status-icon" id="resIcon"></i>
                    <h3 class="fw-bold mb-1" id="resStatus">Status Gizi</h3>
                    <div class="badge rounded-pill bg-light text-dark border px-3 py-2 mt-2">
                        <span class="fw-normal">Z-Score: </span><strong class="fs-5" id="resZScore">-</strong>
                    </div>
                </div>

                <div class="card border-0 bg-light rounded-4 p-4 mb-4">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <p class="text-muted small mb-1">Tinggi Dimasukkan</p>
                            <h5 class="fw-bold text-slate-800 mb-0" id="resInputHeight">- cm</h5>
                        </div>
                        <div class="col-6">
                            <p class="text-muted small mb-1">Median Ideal WHO</p>
                            <h5 class="fw-bold text-slate-800 mb-0" id="resMedian">- cm</h5>
                        </div>
                    </div>
                </div>

                <div class="alert border-0 shadow-sm" id="resAdviceBox" role="alert">
                    <h6 class="fw-bold alert-heading"><i class="fa-solid fa-stethoscope me-2"></i> Rekomendasi Medis:</h6>
                    <p class="mb-0 small" id="resAdvice">-</p>
                </div>
                
                <div class="text-center mt-4">
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="resetForm()"><i class="fa-solid fa-rotate-right me-1"></i> Hitung Ulang</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const form = document.getElementById('stuntingForm');
    const btnSubmit = document.getElementById('btnSubmit');
    const emptyState = document.getElementById('emptyState');
    const resultState = document.getElementById('resultState');

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // UI Loading
        btnSubmit.classList.add('loading');
        btnSubmit.disabled = true;

        const formData = new FormData(form);

        fetch('{{ route('pasien.stunting.calculate') }}', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            btnSubmit.classList.remove('loading');
            btnSubmit.disabled = false;

            if (data.success) {
                displayResult(data.data, formData.get('tinggi_badan'));
            } else {
                alert('Gagal: ' + data.message);
            }
        })
        .catch(error => {
            btnSubmit.classList.remove('loading');
            btnSubmit.disabled = false;
            alert('Terjadi kesalahan pada server.');
            console.error(error);
        });
    });

    function displayResult(data, inputHeight) {
        // Sembunyikan empty state, munculkan result
        emptyState.style.display = 'none';
        resultState.classList.add('show');

        // Update Text
        document.getElementById('resStatus').innerText = data.status;
        document.getElementById('resZScore').innerText = data.z_score > 0 ? '+' + data.z_score : data.z_score;
        document.getElementById('resInputHeight').innerText = inputHeight + ' cm';
        document.getElementById('resMedian').innerText = data.median + ' cm';
        document.getElementById('resAdvice').innerText = data.advice;

        // Update Colors & Icons
        const iconElem = document.getElementById('resIcon');
        const statusElem = document.getElementById('resStatus');
        const adviceBox = document.getElementById('resAdviceBox');

        // Reset classes
        iconElem.className = 'fa-solid status-icon text-' + data.color + ' ' + data.icon;
        statusElem.className = 'fw-bold mb-1 text-' + data.color;
        adviceBox.className = 'alert border-0 shadow-sm bg-' + data.color + ' text-white bg-opacity-75';
    }

    function resetForm() {
        form.reset();
        resultState.classList.remove('show');
        setTimeout(() => {
            emptyState.style.display = 'flex';
        }, 400);
    }
</script>
@endsection
