@extends('layouts.app')

@section('title', 'Input Resep Baru - SI Puskesmas & Klinik')

@section('content')
<div class="row justify-content-center animated-fade" x-data="resepForm()">
    <div class="col-lg-10">
        <div class="card card-premium p-4 p-md-5 my-4">
            <div class="text-center mb-4">
                <i class="fa-solid fa-file-prescription text-primary fs-1 mb-2"></i>
                <h3 class="fw-bold">Input Resep Elektronik</h3>
                <p class="text-muted">Buat resep obat elektronik langsung terintegrasi dengan bagian apoteker</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger alert-premium mb-4 py-2 px-3">
                    <ul class="mb-0 small ps-2">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('dokter.resep.store') }}" method="POST">
                @csrf
                <input type="hidden" name="kunjungan_id" value="{{ $kunjungan->id }}">

                <!-- INFO PASIEN & ALERGI WARNING -->
                <div class="row g-3 mb-4 p-3 bg-light rounded-3">
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Nama Pasien</span>
                        <strong class="text-dark">{{ $kunjungan->pasien->user->name }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">NIK / No. BPJS</span>
                        <strong class="text-dark">{{ $kunjungan->pasien->nik }} / {{ $kunjungan->pasien->no_bpjs ?? '-' }}</strong>
                    </div>
                    <div class="col-md-4">
                        <span class="text-muted small d-block">Tanggal Lahir</span>
                        <strong class="text-dark">{{ $kunjungan->pasien->tanggal_lahir->format('d-m-Y') }}</strong>
                    </div>

                    <!-- Detailed Symptoms/Complaints -->
                    <div class="col-12 mt-3 pt-3 border-top">
                        <span class="text-muted small d-block fw-bold"><i class="fa-solid fa-notes-medical text-primary me-1"></i> Keluhan Pasien secara Detail:</span>
                        <p class="mb-0 text-dark small bg-white p-2 rounded border">{{ $kunjungan->keluhan }}</p>
                    </div>

                    <!-- Alergi Warning Box -->
                    @if ($kunjungan->pasien->riwayat_alergi)
                        <div class="col-12 mt-3">
                            <div class="alert alert-danger mb-0 py-2 d-flex align-items-center" role="alert">
                                <i class="fa-solid fa-triangle-exclamation me-2 fs-5"></i>
                                <div>
                                    <strong>PERINGATAN ALERGI OBAT:</strong> {{ $kunjungan->pasien->riwayat_alergi }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- DYNAMIC DRUG ROWS (Alpine.js) -->
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-pills text-primary me-2"></i> Daftar Resep Obat</h5>
                
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light small">
                            <tr>
                                <th style="width: 35%;">Nama Obat</th>
                                <th style="width: 15%;">Jumlah</th>
                                <th style="width: 20%;">Dosis (Frekuensi)</th>
                                <th style="width: 25%;">Aturan / Keterangan</th>
                                <th class="text-center" style="width: 5%;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="(row, index) in rows" :key="index">
                                <tr>
                                    <!-- Obat Selection -->
                                    <td>
                                        <select :name="'obat['+index+'][obat_id]'" class="form-select form-select-sm" x-model="row.obat_id" @change="updateObatInfo(row)" required>
                                            <option value="">-- Pilih Obat --</option>
                                            <template x-for="item in obatsList" :key="item.id">
                                                <option :value="item.id" x-text="item.nama_obat"></option>
                                            </template>
                                        </select>
                                        
                                        <!-- Stock & Price Real-time Display -->
                                        <div class="mt-1 d-flex justify-content-between align-items-center px-1">
                                            <span class="small" :class="row.stok === 0 ? 'text-danger fw-bold' : (row.stok <= row.stok_min ? 'text-warning' : 'text-muted')">
                                                Stok Tersedia: <span x-text="row.stok !== null ? row.stok : '-'"></span>
                                            </span>
                                            <span class="small text-muted" x-show="row.harga">Harga: Rp<span x-text="row.harga"></span></span>
                                        </div>
                                        <div x-show="row.stok === 0" class="text-danger small fw-bold mt-1 px-1">
                                            <i class="fa-solid fa-circle-xmark"></i> Stok habis! Hubungi farmasi.
                                        </div>
                                    </td>
                                    
                                    <!-- Jumlah -->
                                    <td>
                                        <input type="number" :name="'obat['+index+'][jumlah]'" class="form-control form-control-sm" x-model="row.jumlah" min="1" :max="row.stok" placeholder="Jumlah" required>
                                    </td>
                                    
                                    <!-- Dosis -->
                                    <td>
                                        <input type="text" :name="'obat['+index+'][dosis]'" class="form-control form-control-sm" x-model="row.dosis" placeholder="Contoh: 3x1 tablet" required>
                                    </td>
                                    
                                    <!-- Aturan Pakai & Keterangan -->
                                    <td>
                                        <input type="text" :name="'obat['+index+'][aturan_pakai]'" class="form-control form-control-sm mb-1" x-model="row.aturan_pakai" placeholder="Aturan (Sesudah makan)" required>
                                        <input type="text" :name="'obat['+index+'][keterangan]'" class="form-control form-control-sm" x-model="row.keterangan" placeholder="Keterangan tambahan">
                                    </td>
                                    
                                    <!-- Delete Row Action -->
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger" @click="removeRow(index)" :disabled="rows.length === 1">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <!-- Add Row Button -->
                <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addRow()">
                        <i class="fa-solid fa-plus-circle me-1"></i> Tambah Obat
                    </button>
                </div>

                <!-- CATATAN DOKTER -->
                <div class="mb-4">
                    <label for="catatan_dokter" class="form-label fw-semibold small">Catatan Pemeriksaan Dokter (Opsional)</label>
                    <textarea class="form-control" id="catatan_dokter" name="catatan_dokter" rows="3" placeholder="Masukkan instruksi khusus atau catatan diagnosa singkat jika diperlukan"></textarea>
                </div>

                <!-- PRIORITAS -->
                <div class="mb-4">
                    <label class="form-label fw-semibold small d-block">Prioritas Pengerjaan Resep <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline mt-1">
                        <input class="form-check-input" type="radio" name="prioritas" id="prio_normal" value="normal" checked>
                        <label class="form-check-label" for="prio_normal">Normal</label>
                    </div>
                    <div class="form-check form-check-inline mt-1">
                        <input class="form-check-input" type="radio" name="prioritas" id="prio_urgen" value="urgen">
                        <label class="form-check-label text-danger fw-bold" for="prio_urgen"><i class="fa-solid fa-triangle-exclamation"></i> Urgen (Dahulukan)</label>
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-5">
                    <a href="{{ route('dokter.dashboard') }}" class="btn btn-outline-secondary order-2 order-sm-1 w-100 w-sm-auto"><i class="fa-solid fa-arrow-left me-1"></i> Batal</a>
                    <button type="submit" class="btn btn-success text-white order-1 order-sm-2 w-100 w-sm-auto" :disabled="hasEmptyOrOutofStockObat()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Kirim Resep Ke Farmasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function resepForm() {
        return {
            // Data obat ter-seeding dari server untuk lookup instan
            obatsList: @json($obats),
            
            // Baris resep
            rows: [
                { obat_id: '', jumlah: 1, dosis: '', aturan_pakai: '', keterangan: '', stok: null, stok_min: 0, harga: null }
            ],
            
            addRow() {
                this.rows.push({ obat_id: '', jumlah: 1, dosis: '', aturan_pakai: '', keterangan: '', stok: null, stok_min: 0, harga: null });
            },
            
            removeRow(index) {
                if (this.rows.length > 1) {
                    this.rows.splice(index, 1);
                }
            },
            
            updateObatInfo(row) {
                const obatId = parseInt(row.obat_id);
                const selectedObat = this.obatsList.find(item => item.id === obatId);
                
                if (selectedObat) {
                    row.stok = selectedObat.stok;
                    row.stok_min = selectedObat.stok_minimum;
                    row.harga = selectedObat.harga_satuan;
                    
                    // Reset jumlah jika melebihi stok yang tersedia
                    if (row.jumlah > row.stok) {
                        row.jumlah = row.stok > 0 ? row.stok : 1;
                    }
                } else {
                    row.stok = null;
                    row.stok_min = 0;
                    row.harga = null;
                }
            },
            
            hasEmptyOrOutofStockObat() {
                // Nonaktifkan tombol kirim resep jika ada obat yang stoknya 0 atau tidak terpilih
                return this.rows.some(row => !row.obat_id || row.stok === 0);
            }
        };
    }
</script>
@endsection
