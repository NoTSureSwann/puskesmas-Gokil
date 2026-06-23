@extends('layouts.app')

@section('title', 'Input Rekomendasi Resep - Farmasi')

@section('content')
<div class="row justify-content-center animated-fade" x-data="draftResepForm()">
    <div class="col-lg-10">
        <div class="card card-premium p-4 p-md-5 my-4">
            <div class="text-center mb-4">
                <i class="fa-solid fa-pills text-success fs-1 mb-2"></i>
                <h3 class="fw-bold">Input Rekomendasi Resep</h3>
                <p class="text-muted">Buat draft rekomendasi obat untuk divalidasi oleh dokter</p>
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

            <form action="{{ route('farmasi.draft-resep.store') }}" method="POST">
                @csrf
                
                <div class="mb-4">
                    <label for="kunjungan_id" class="form-label fw-bold">Pilih Kunjungan Pasien</label>
                    <select class="form-select" id="kunjungan_id" name="kunjungan_id" required>
                        <option value="">-- Pilih Pasien --</option>
                        @foreach($kunjungans as $k)
                            <option value="{{ $k->id }}">
                                {{ $k->pasien->user->name }} - {{ $k->poli->nama_poli }} (Status: {{ $k->status }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- DYNAMIC DRUG ROWS (Alpine.js) -->
                <h5 class="fw-bold mb-3"><i class="fa-solid fa-list text-primary me-2"></i> Daftar Rekomendasi Obat</h5>
                
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
                                        
                                        <div class="mt-1 d-flex justify-content-between align-items-center px-1">
                                            <span class="small" :class="row.stok === 0 ? 'text-danger fw-bold' : (row.stok <= row.stok_min ? 'text-warning' : 'text-muted')">
                                                Stok Tersedia: <span x-text="row.stok !== null ? row.stok : '-'"></span>
                                            </span>
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

                <div class="mb-4">
                    <button type="button" class="btn btn-outline-primary btn-sm" @click="addRow()">
                        <i class="fa-solid fa-plus-circle me-1"></i> Tambah Obat
                    </button>
                </div>

                <div class="mb-4">
                    <label for="catatan_farmasi" class="form-label fw-semibold small">Catatan dari Farmasi (Opsional)</label>
                    <textarea class="form-control" id="catatan_farmasi" name="catatan_farmasi" rows="2" placeholder="Catatan untuk dokter..."></textarea>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-success text-white px-4" :disabled="hasEmptyOrOutofStockObat()">
                        <i class="fa-solid fa-paper-plane me-1"></i> Ajukan Rekomendasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function draftResepForm() {
        return {
            obatsList: @json($obats),
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
                    if (row.jumlah > row.stok) row.jumlah = row.stok > 0 ? row.stok : 1;
                } else {
                    row.stok = null; row.stok_min = 0; row.harga = null;
                }
            },
            hasEmptyOrOutofStockObat() {
                return this.rows.some(row => !row.obat_id || row.stok === 0);
            }
        };
    }
</script>
@endsection
