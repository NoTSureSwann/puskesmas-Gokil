@extends('layouts.app')

@section('title', 'Daftar Kunjungan Baru - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade py-3">
    <!-- Header -->
    <div class="text-center mb-4">
        <i class="fa-solid fa-calendar-plus text-primary fs-1 mb-2"></i>
        <h3 class="fw-bold">Pendaftaran Kunjungan Pasien</h3>
        <p class="text-muted">Isi data keluhan penyakit secara detail dan tentukan klinik tujuan pemeriksaan Anda.</p>
    </div>

    <div class="row g-4">
        <!-- Form Pendaftaran Column -->
        <div class="col-lg-7">
            <div class="card card-premium p-4 shadow-sm">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i> Formulir Pendaftaran</h5>

                @if ($errors->any())
                    <div class="alert alert-danger alert-premium mb-4 py-2 px-3">
                        <ul class="mb-0 small ps-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('pasien.daftar.submit') }}" method="POST">
                    @csrf

                    <!-- Pasien Summary -->
                    <div class="alert alert-success alert-premium py-3 mb-4 d-flex gap-3 align-items-center">
                        <i class="fa-solid fa-circle-user fs-2 text-success"></i>
                        <div>
                            <h6 class="fw-bold mb-1">{{ Auth::user()->name }}</h6>
                            <span class="small text-muted me-3">NIK: <strong>{{ $pasien->nik }}</strong></span>
                            <span class="small text-muted">BPJS: <strong>{{ $pasien->no_bpjs ?? 'Tidak Terdaftar' }}</strong></span>
                        </div>
                    </div>

                    <!-- POLI TUJUAN -->
                    <div class="mb-3">
                        <label for="poli_id" class="form-label fw-semibold small text-dark">Klinik / Poli Tujuan <span class="text-danger">*</span></label>
                        <select class="form-select @error('poli_id') is-invalid @enderror" id="poli_id" name="poli_id" required>
                            <option value="" disabled selected>-- Pilih Poli Klinik --</option>
                            @foreach ($polis as $poli)
                                <option value="{{ $poli->id }}" {{ old('poli_id') == $poli->id ? 'selected' : '' }}>
                                    {{ $poli->nama_poli }} ({{ $poli->kode_poli }})
                                </option>
                            @endforeach
                        </select>
                        @error('poli_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- TANGGAL KUNJUNGAN -->
                    <div class="mb-3">
                        <label for="tanggal_kunjungan" class="form-label fw-semibold small text-dark">Tanggal Rencana Kunjungan <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('tanggal_kunjungan') is-invalid @enderror" id="tanggal_kunjungan" name="tanggal_kunjungan" min="{{ $today }}" value="{{ old('tanggal_kunjungan', $today) }}" required>
                        @error('tanggal_kunjungan')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- JENIS KUNJUNGAN -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small d-block text-dark">Metode Pembayaran / Jaminan Kesehatan <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="jenis_kunjungan" id="jk_umum" value="umum" {{ old('jenis_kunjungan', $pasien->jenis_pasien) === 'umum' ? 'checked' : '' }} required>
                            <label class="form-check-label text-dark" for="jk_umum">Umum (Biaya Mandiri)</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="jenis_kunjungan" id="jk_bpjs" value="bpjs" {{ old('jenis_kunjungan', $pasien->jenis_pasien) === 'bpjs' ? 'checked' : '' }} {{ is_null($pasien->no_bpjs) ? 'disabled' : '' }} required>
                            <label class="form-check-label text-dark" for="jk_bpjs">BPJS Kesehatan</label>
                        </div>
                        @if (is_null($pasien->no_bpjs))
                            <div class="form-text text-warning small"><i class="fa-solid fa-triangle-exclamation"></i> BPJS dinonaktifkan karena profil Anda belum mencantumkan nomor BPJS.</div>
                        @endif
                    </div>

                    <!-- METODE KUNJUNGAN -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small d-block text-dark">Metode Kunjungan <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="metode_kunjungan" id="mk_langsung" value="langsung" {{ old('metode_kunjungan', 'langsung') === 'langsung' ? 'checked' : '' }} required>
                            <label class="form-check-label text-dark" for="mk_langsung">Tatap Muka (Langsung)</label>
                        </div>
                        <div class="form-check form-check-inline mt-1">
                            <input class="form-check-input" type="radio" name="metode_kunjungan" id="mk_telemedisin" value="telemedisin" {{ old('metode_kunjungan') === 'telemedisin' ? 'checked' : '' }} required>
                            <label class="form-check-label text-dark" for="mk_telemedisin">Telemedisin (Video Call)</label>
                        </div>
                    </div>

                    <!-- KELUHAN DETAIL -->
                    <div class="mb-4" x-data="aiAnalyzer()">
                        <label for="keluhan" class="form-label fw-semibold small text-dark">Rincian Keluhan Penyakit & Gejala Sakit secara Detail <span class="text-danger">*</span></label>
                        <textarea x-model="keluhanText" class="form-control @error('keluhan') is-invalid @enderror" id="keluhan" name="keluhan" rows="5" placeholder="Tuliskan keluhan secara detail. Contoh: Mengalami demam tinggi naik-turun sejak 3 hari yang lalu, kepala terasa pusing berat, mual saat makan, dan ada ruam merah kecil di area lengan." required>{{ old('keluhan') }}</textarea>
                        
                        <!-- Hidden AI inputs for dataset collection -->
                        <input type="hidden" name="ai_run" :value="showResult ? '1' : '0'">
                        <input type="hidden" name="ai_kemungkinan_penyakit" :value="showResult ? JSON.stringify(result.kemungkinan_penyakit) : ''">
                        <input type="hidden" name="ai_tingkat_urgensi" :value="showResult ? result.tingkat_urgensi : ''">
                        <input type="hidden" name="ai_rekomendasi_poli_nama" :value="showResult ? result.rekomendasi_poli_nama : ''">
                        <input type="hidden" name="ai_saran_tindakan" :value="showResult ? result.saran_tindakan : ''">
                        <input type="hidden" name="ai_confidence_score" :value="showResult ? result.confidence_score : ''">
                        
                        <div class="d-flex justify-content-between align-items-start mt-2">
                            <span class="form-text text-muted small flex-grow-1 me-2">Harap jelaskan gejala secara detail untuk mempermudah pemeriksaan awal oleh dokter.</span>
                            <button type="button" @click="analyze" class="btn btn-sm btn-info text-white rounded-pill px-3 shadow-sm d-flex align-items-center" :class="{'pulse-animation': !isLoading}" :disabled="isLoading || keluhanText.length < 10" style="min-width: 130px;">
                                <i class="fa-solid fa-wand-magic-sparkles me-2" :class="{'fa-spin': isLoading}"></i> 
                                <span x-text="isLoading ? 'Menganalisis...' : 'Analisis AI'" class="small fw-bold"></span>
                            </button>
                        </div>
                        @error('keluhan')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror

                        <!-- AI Result Table -->
                        <div x-show="showResult" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 transform -translate-y-4" x-transition:enter-end="opacity-100 transform translate-y-0" class="mt-3 border rounded-3 p-3 bg-light" style="display: none;">
                            <h6 class="fw-bold text-info mb-3 border-bottom pb-2"><i class="fa-solid fa-robot me-1"></i> Hasil Analisis AI Pintar</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0 small align-middle">
                                    <tbody>
                                        <tr x-show="showResult" x-transition:enter="transition ease-out duration-300 delay-100" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                                            <td class="text-muted" style="width: 140px;"><i class="fa-solid fa-virus me-1"></i> Kemungkinan:</td>
                                            <td class="fw-semibold text-dark">
                                                <template x-for="p in result.kemungkinan_penyakit">
                                                    <span class="badge bg-secondary me-1" x-text="p"></span>
                                                </template>
                                            </td>
                                        </tr>
                                        <tr x-show="showResult" x-transition:enter="transition ease-out duration-300 delay-200" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                                            <td class="text-muted"><i class="fa-solid fa-triangle-exclamation me-1"></i> Urgensi:</td>
                                            <td>
                                                <span class="badge" 
                                                    :class="{
                                                        'bg-success': result.tingkat_urgensi === 'Rendah',
                                                        'bg-warning text-dark': result.tingkat_urgensi === 'Sedang',
                                                        'bg-danger pulse-animation': result.tingkat_urgensi === 'Tinggi'
                                                    }" 
                                                    x-text="result.tingkat_urgensi"></span>
                                            </td>
                                        </tr>
                                        <tr x-show="showResult" x-transition:enter="transition ease-out duration-300 delay-300" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                                            <td class="text-muted"><i class="fa-solid fa-kit-medical me-1"></i> Saran Tindakan:</td>
                                            <td class="text-dark" x-text="result.saran_tindakan"></td>
                                        </tr>
                                        <tr x-show="showResult" x-transition:enter="transition ease-out duration-300 delay-400" x-transition:enter-start="opacity-0 translate-x-2" x-transition:enter-end="opacity-100 translate-x-0">
                                            <td class="text-muted"><i class="fa-solid fa-house-medical-circle-check me-1"></i> Rekomendasi Poli:</td>
                                            <td class="fw-bold text-primary">
                                                <i class="fa-solid fa-stethoscope me-1"></i> <span x-text="result.rekomendasi_poli_nama"></span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 border-top pt-3">
                        <a href="{{ route('pasien.dashboard') }}" class="btn btn-outline-secondary order-2 order-sm-1 w-100 w-sm-auto"><i class="fa-solid fa-arrow-left me-1"></i> Kembali</a>
                        <button type="submit" class="btn btn-primary text-white order-1 order-sm-2 w-100 w-sm-auto"><i class="fa-solid fa-ticket-check me-1"></i> Ambil Antrian</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Jadwal & Tarif Dokter Column -->
        <div class="col-lg-5">
            <div class="card card-premium p-4 shadow-sm h-100">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-dark"><i class="fa-solid fa-clock-rotate-left me-2 text-primary"></i> Jadwal Praktik & Tarif Dokter</h5>
                <p class="text-muted small">Informasi dokter aktif yang bertugas di masing-masing klinik beserta tarif konsultasi.</p>

                <div class="mt-3">
                    @forelse ($dokters as $doc)
                        <div class="p-3 mb-3 bg-light rounded border border-light-subtle">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary text-white p-2 rounded-circle me-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                    <i class="fa-solid fa-user-doctor fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $doc->name }}</h6>
                                    <span class="badge bg-primary-subtle text-primary small py-1 mt-1">{{ $doc->profilDokter->poli }}</span>
                                </div>
                            </div>
                            <div class="mt-2 text-secondary small">
                                <div class="mb-1"><i class="fa-regular fa-clock text-primary me-2"></i> Jam Kerja: <strong>{{ $doc->profilDokter->jam_kerja }}</strong></div>
                                <div><i class="fa-solid fa-money-bill-wave text-success me-2"></i> Tarif Jasa: <strong>Rp {{ number_format((float)($doc->profilDokter->harga_konsultasi ?? 0), 0, ',', '.') }}</strong></div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-5 small">Belum ada dokter yang terdaftar aktif.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('aiAnalyzer', () => ({
            keluhanText: document.getElementById('keluhan').value || '',
            isLoading: false,
            showResult: false,
            result: {},
            
            async analyze() {
                if (this.keluhanText.length < 10) return;
                
                this.isLoading = true;
                this.showResult = false;
                
                try {
                    const response = await fetch('{{ route('pasien.analyze') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ keluhan: this.keluhanText })
                    });
                    
                    const res = await response.json();
                    
                    if (res.status === 'success') {
                        this.result = res.data;
                        this.showResult = true;
                        
                        // Auto select poli_id
                        if(this.result.rekomendasi_poli_id) {
                            const selectElement = document.getElementById('poli_id');
                            selectElement.value = this.result.rekomendasi_poli_id;
                            
                            // Highlight select element to notify user
                            selectElement.classList.add('pulse-animation', 'border-info');
                            setTimeout(() => {
                                selectElement.classList.remove('pulse-animation', 'border-info');
                            }, 3000);
                        }
                    }
                } catch (error) {
                    console.error("AI Analysis failed:", error);
                    alert("Terjadi kesalahan saat menganalisis keluhan. Silakan coba lagi.");
                } finally {
                    this.isLoading = false;
                }
            }
        }));
    });
</script>
@endsection
