@extends('layouts.app')

@section('title', 'Manajemen Obat - SI Puskesmas & Klinik')

@section('content')
<div class="animated-fade">
    <div class="mb-4">
        <h2 class="fw-bold mb-1">Manajemen Obat & Harga</h2>
        <p class="text-muted mb-0">Kelola persediaan obat, harga, dan batas minimum stok apotek.</p>
    </div>

    <!-- Farmasi Sub-Navigation -->
    <div class="card card-premium shadow-sm mb-4 p-3">
        <div class="nav nav-pills card-header-pills flex-column flex-md-row gap-2">
            <a class="nav-link text-dark fw-semibold" href="{{ route('farmasi.dashboard') }}">
                <i class="fa-solid fa-gauge me-1"></i> Dashboard Antrean Resep
            </a>
            <a class="nav-link active bg-primary text-white fw-semibold" href="{{ route('farmasi.obat.index') }}">
                <i class="fa-solid fa-pills me-1"></i> Manajemen Obat
            </a>
        </div>
    </div>

    <!-- Session Alerts are now handled by SweetAlert2 below -->

    <div class="row g-4" x-data="{ editingObat: null }">
        <!-- Form Column -->
        <div class="col-lg-4">
            <!-- Add Obat Card -->
            <div class="card card-premium shadow-sm p-4 mb-4 position-sticky" style="top: 1.5rem;" x-show="!editingObat">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-primary"><i class="fa-solid fa-plus-circle me-2"></i>Tambah Obat Baru</h5>
                <form action="{{ route('farmasi.obat.store') }}" method="POST">
                    @csrf
                    
                    <div class="form-floating mb-3">
                        <input type="text" name="kode_obat" id="kode_obat" class="form-control @error('kode_obat') is-invalid @enderror" value="{{ old('kode_obat') }}" placeholder="Kode Obat" required>
                        <label for="kode_obat"><i class="fa-solid fa-barcode text-muted me-1"></i>Kode Obat (e.g. OB-AMX-500)</label>
                        @error('kode_obat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="nama_obat" id="nama_obat" class="form-control @error('nama_obat') is-invalid @enderror" value="{{ old('nama_obat') }}" placeholder="Nama Obat" required>
                        <label for="nama_obat"><i class="fa-solid fa-pills text-muted me-1"></i>Nama Obat</label>
                        @error('nama_obat') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3 form-floating">
                            <input type="text" name="satuan" id="satuan" class="form-control @error('satuan') is-invalid @enderror" value="{{ old('satuan', 'Tablet') }}" placeholder="Satuan" required>
                            <label for="satuan"><i class="fa-solid fa-box text-muted me-1"></i>Satuan</label>
                        </div>
                        <div class="col-6 mb-3 form-floating">
                            <input type="text" name="kategori" id="kategori" class="form-control @error('kategori') is-invalid @enderror" value="{{ old('kategori', 'Antibiotik') }}" placeholder="Kategori" required>
                            <label for="kategori"><i class="fa-solid fa-tags text-muted me-1"></i>Kategori</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3 form-floating">
                            <input type="number" name="stok" id="stok" class="form-control @error('stok') is-invalid @enderror" value="{{ old('stok', 100) }}" required min="0">
                            <label for="stok"><i class="fa-solid fa-cubes text-muted me-1"></i>Stok Fisik</label>
                        </div>
                        <div class="col-6 mb-3 form-floating">
                            <input type="number" name="stok_minimum" id="stok_minimum" class="form-control @error('stok_minimum') is-invalid @enderror" value="{{ old('stok_minimum', 20) }}" required min="0">
                            <label for="stok_minimum"><i class="fa-solid fa-arrow-down-short-wide text-muted me-1"></i>Stok Min</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3 form-floating">
                            <input type="number" step="0.01" name="harga_satuan" id="harga_satuan" class="form-control @error('harga_satuan') is-invalid @enderror" value="{{ old('harga_satuan', 500) }}" required min="0">
                            <label for="harga_satuan"><i class="fa-solid fa-money-bill-wave text-muted me-1"></i>Harga (Rp)</label>
                        </div>
                        <div class="col-6 mb-3 form-floating">
                            <input type="date" name="tanggal_kadaluarsa" id="tanggal_kadaluarsa" class="form-control @error('tanggal_kadaluarsa') is-invalid @enderror" value="{{ old('tanggal_kadaluarsa') }}">
                            <label for="tanggal_kadaluarsa"><i class="fa-solid fa-calendar-times text-muted me-1"></i>Kadaluarsa</label>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea name="deskripsi" id="deskripsi" class="form-control" style="height: 80px" placeholder="Deskripsi">{{ old('deskripsi') }}</textarea>
                        <label for="deskripsi"><i class="fa-solid fa-align-left text-muted me-1"></i>Deskripsi Singkat</label>
                    </div>

                    <button type="submit" class="btn btn-primary text-white w-100 py-2"><i class="fa-solid fa-plus me-1"></i> Simpan Obat Baru</button>
                </form>
            </div>

            <!-- Edit Obat Card -->
            <div class="card card-premium shadow-sm p-4 border border-primary mb-4" x-show="editingObat" x-cloak>
                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                    <h5 class="fw-bold mb-0 text-primary">Edit Data & Harga Obat</h5>
                    <button type="button" class="btn-close" @click="editingObat = null"></button>
                </div>
                <form :action="'/farmasi/obat/' + (editingObat ? editingObat.id : '')" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="form-floating mb-3">
                        <input type="text" class="form-control bg-light" x-model="editingObat ? editingObat.kode_obat : ''" readonly disabled placeholder="Kode Obat">
                        <label><i class="fa-solid fa-barcode text-muted me-1"></i>Kode Obat (Terkunci)</label>
                    </div>

                    <div class="form-floating mb-3">
                        <input type="text" name="nama_obat" id="edit_nama_obat" class="form-control" x-model="editingObat ? editingObat.nama_obat : ''" required placeholder="Nama Obat">
                        <label for="edit_nama_obat"><i class="fa-solid fa-pills text-muted me-1"></i>Nama Obat</label>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3 form-floating">
                            <input type="text" name="satuan" id="edit_satuan" class="form-control" x-model="editingObat ? editingObat.satuan : ''" required placeholder="Satuan">
                            <label for="edit_satuan"><i class="fa-solid fa-box text-muted me-1"></i>Satuan</label>
                        </div>
                        <div class="col-6 mb-3 form-floating">
                            <input type="text" name="kategori" id="edit_kategori" class="form-control" x-model="editingObat ? editingObat.kategori : ''" required placeholder="Kategori">
                            <label for="edit_kategori"><i class="fa-solid fa-tags text-muted me-1"></i>Kategori</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3 form-floating">
                            <input type="number" name="stok" id="edit_stok" class="form-control" x-model="editingObat ? editingObat.stok : 0" required min="0" placeholder="Stok Fisik">
                            <label for="edit_stok"><i class="fa-solid fa-cubes text-muted me-1"></i>Stok Fisik</label>
                        </div>
                        <div class="col-6 mb-3 form-floating">
                            <input type="number" name="stok_minimum" id="edit_stok_minimum" class="form-control" x-model="editingObat ? editingObat.stok_minimum : 0" required min="0" placeholder="Stok Min">
                            <label for="edit_stok_minimum"><i class="fa-solid fa-arrow-down-short-wide text-muted me-1"></i>Stok Min</label>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-6 mb-3 form-floating">
                            <input type="number" step="0.01" name="harga_satuan" id="edit_harga_satuan" class="form-control border-primary" x-model="editingObat ? editingObat.harga_satuan : 0" required min="0" placeholder="Harga Jual Satuan (Rp)">
                            <label for="edit_harga_satuan" class="text-primary"><i class="fa-solid fa-money-bill-wave me-1"></i>Harga (Rp)</label>
                        </div>
                        <div class="col-6 mb-3 form-floating">
                            <input type="date" name="tanggal_kadaluarsa" id="edit_tanggal_kadaluarsa" class="form-control" x-model="editingObat && editingObat.tanggal_kadaluarsa ? editingObat.tanggal_kadaluarsa.substring(0,10) : ''" placeholder="Kadaluarsa">
                            <label for="edit_tanggal_kadaluarsa"><i class="fa-solid fa-calendar-times text-muted me-1"></i>Kadaluarsa</label>
                        </div>
                    </div>

                    <div class="form-floating mb-3">
                        <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="2" style="height: 80px" x-model="editingObat ? editingObat.deskripsi : ''" placeholder="Deskripsi"></textarea>
                        <label for="edit_deskripsi"><i class="fa-solid fa-align-left text-muted me-1"></i>Deskripsi</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-outline-secondary w-50" @click="editingObat = null">Batal</button>
                        <button type="submit" class="btn btn-primary text-white w-50"><i class="fa-solid fa-save me-1"></i> Simpan Update</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- List Column -->
        <div class="col-lg-8">
            <!-- Analytics Row -->
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <div class="card card-premium shadow-sm p-3 h-100">
                        <h6 class="fw-bold text-muted mb-2 text-center"><i class="fa-solid fa-chart-pie me-1"></i> Kategori Obat</h6>
                        <div style="height: 200px; display: flex; justify-content: center;">
                            <canvas id="kategoriChart"></canvas>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card card-premium shadow-sm p-3 h-100">
                        <h6 class="fw-bold text-muted mb-2 text-center"><i class="fa-solid fa-chart-bar me-1"></i> Status Stok Kritis</h6>
                        <div style="height: 200px; display: flex; justify-content: center;">
                            <canvas id="stokChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-premium shadow-sm p-4 border-top border-4 border-primary">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <h5 class="fw-bold mb-0">Daftar Inventaris Apotek</h5>
                        <!-- Bulk Delete Button (Alpine JS driven) -->
                        <form action="{{ route('farmasi.obat.bulk-destroy') }}" method="POST" id="bulkDeleteForm" style="display: none;">
                            @csrf
                            <div id="bulkDeleteInputs"></div>
                            <button type="button" class="btn btn-sm btn-danger shadow-sm fw-bold" onclick="confirmBulkDelete()">
                                <i class="fa-solid fa-trash me-1"></i> Hapus Terpilih (<span id="bulkCount">0</span>)
                            </button>
                        </form>
                    </div>
                    <div class="input-group" style="max-width: 300px;">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-search"></i></span>
                        <input type="text" id="liveSearchInput" class="form-control border-start-0 ps-0" placeholder="Cari kode atau nama obat..." onkeyup="filterTable()">
                    </div>
                </div>
                
                <div class="table-responsive">
                    <table class="table table-hover align-middle" id="obatTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;"><input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)"></th>
                                <th>Kode</th>
                                <th>Nama Obat</th>
                                <th class="text-center">Sisa Stok</th>
                                <th>Kadaluarsa</th>
                                <th>Harga Jual</th>
                                <th>Status</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($obats as $obat)
                                @php
                                    $stokRendah = $obat->stok <= $obat->stok_minimum;
                                    $isExpired = false;
                                    $isWarning = false;
                                    if ($obat->tanggal_kadaluarsa) {
                                        $expDate = \Carbon\Carbon::parse($obat->tanggal_kadaluarsa);
                                        $isExpired = $expDate->isPast();
                                        $isWarning = !$isExpired && $expDate->diffInMonths(now()) < 3;
                                    }
                                @endphp
                                <tr class="{{ $stokRendah || $isExpired ? 'table-danger-subtle' : '' }}">
                                    <td><input class="form-check-input row-checkbox" type="checkbox" value="{{ $obat->id }}" onchange="updateBulkAction()"></td>
                                    <td><code>{{ $obat->kode_obat }}</code></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $obat->nama_obat }}</div>
                                        <span class="small text-muted">{{ $obat->kategori }} ({{ $obat->satuan }})</span>
                                    </td>
                                    <td class="text-center fw-bold {{ $stokRendah ? 'text-danger' : 'text-success' }}">
                                        {{ $obat->stok }}
                                        @if ($stokRendah)
                                            <div class="small text-danger fw-normal" style="font-size: 0.7rem;"><i class="fa-solid fa-triangle-exclamation"></i> Rendah</div>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($obat->tanggal_kadaluarsa)
                                            @if ($isExpired)
                                                <span class="badge bg-danger"><i class="fa-solid fa-skull me-1"></i>{{ \Carbon\Carbon::parse($obat->tanggal_kadaluarsa)->format('d M Y') }}</span>
                                            @elseif ($isWarning)
                                                <span class="badge bg-warning text-dark"><i class="fa-solid fa-clock me-1"></i>{{ \Carbon\Carbon::parse($obat->tanggal_kadaluarsa)->format('d M Y') }}</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-shield-check me-1"></i>{{ \Carbon\Carbon::parse($obat->tanggal_kadaluarsa)->format('d M Y') }}</span>
                                            @endif
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-primary">Rp {{ number_format((float) $obat->harga_satuan, 2, ',', '.') }}</td>
                                    <td>
                                        @if ($isExpired)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="fa-solid fa-ban me-1"></i>Terkunci (Expired)</span>
                                        @elseif ($obat->is_aktif)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle">Tersedia</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Ditarik</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" @click="editingObat = @js($obat)">
                                                <i class="fa-solid fa-edit"></i> Edit
                                            </button>
                                            
                                            <form action="{{ route('farmasi.obat.toggle', $obat->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm {{ $obat->is_aktif ? 'btn-outline-warning' : 'btn-outline-success' }} py-1 px-2" {{ $isExpired ? 'disabled' : '' }}>
                                                    {{ $obat->is_aktif ? 'Tarik' : 'Sediakan' }}
                                                </button>
                                            </form>

                                            <form action="{{ route('farmasi.obat.destroy', $obat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus obat ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2">
                                                    <i class="fa-solid fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fa-solid fa-box-open fa-3x mb-3 text-light-subtle"></i>
                                            <h5 class="fw-bold">Belum Ada Data Obat</h5>
                                            <p class="mb-0">Apotek ini belum memiliki inventaris obat yang terdaftar.</p>
                                            <p class="small">Gunakan formulir di sebelah kiri untuk menambah obat baru.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $obats->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // SweetAlert2 Toasts for Flash Messages
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener('mouseenter', Swal.stopTimer)
            toast.addEventListener('mouseleave', Swal.resumeTimer)
        }
    });

    @if(session('status'))
        Toast.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: {!! json_encode(session('status')) !!}
        });
    @endif

    @if(session('error'))
        Toast.fire({
            icon: 'error',
            title: 'Gagal!',
            text: {!! json_encode(session('error')) !!}
        });
    @endif

    // Bulk Actions Logic
    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
        updateBulkAction();
    }

    function updateBulkAction() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        const bulkCount = document.getElementById('bulkCount');
        const bulkForm = document.getElementById('bulkDeleteForm');
        
        bulkCount.innerText = checkboxes.length;
        if(checkboxes.length > 0) {
            bulkForm.style.display = 'block';
        } else {
            bulkForm.style.display = 'none';
        }
    }

    function confirmBulkDelete() {
        const checkboxes = document.querySelectorAll('.row-checkbox:checked');
        if (checkboxes.length === 0) return;

        Swal.fire({
            title: 'Hapus Massal?',
            text: "Anda akan menghapus " + checkboxes.length + " obat secara permanen (jika belum dipakai di resep).",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Populate hidden inputs
                const container = document.getElementById('bulkDeleteInputs');
                container.innerHTML = '';
                checkboxes.forEach(cb => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'obat_ids[]';
                    input.value = cb.value;
                    container.appendChild(input);
                });
                document.getElementById('bulkDeleteForm').submit();
            }
        });
    }

    // Live Search Functionality (Boyer-Moore approach simulated)
    function filterTable() {
        const input = document.getElementById("liveSearchInput");
        const filter = input.value.toUpperCase();
        const table = document.getElementById("obatTable");
        const tr = table.getElementsByTagName("tr");

        for (let i = 1; i < tr.length; i++) {
            let tdKode = tr[i].getElementsByTagName("td")[1]; // Index shifted by 1 because of checkbox
            let tdNama = tr[i].getElementsByTagName("td")[2];
            if (tdKode || tdNama) {
                let txtValueKode = tdKode.textContent || tdKode.innerText;
                let txtValueNama = tdNama.textContent || tdNama.innerText;
                if (txtValueKode.toUpperCase().indexOf(filter) > -1 || txtValueNama.toUpperCase().indexOf(filter) > -1) {
                    tr[i].style.display = "";
                } else {
                    tr[i].style.display = "none";
                }
            }
        }
    }

    // Chart.js Implementations
    document.addEventListener("DOMContentLoaded", function() {
        const obats = @json($obats->items());
        
        const kategoriCount = {};
        obats.forEach(obat => {
            if (obat.kategori) {
                kategoriCount[obat.kategori] = (kategoriCount[obat.kategori] || 0) + 1;
            }
        });
        
        const ctxKategori = document.getElementById('kategoriChart').getContext('2d');
        new Chart(ctxKategori, {
            type: 'doughnut',
            data: {
                labels: Object.keys(kategoriCount),
                datasets: [{
                    data: Object.values(kategoriCount),
                    backgroundColor: ['#0D6EFD', '#198754', '#FFC107', '#DC3545', '#0dcaf0', '#6c757d'],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'right', labels: { boxWidth: 12, font: {size: 10} } }
                }
            }
        });

        const sortedObats = [...obats].sort((a, b) => (a.stok - a.stok_minimum) - (b.stok - b.stok_minimum)).slice(0, 5);
        
        const ctxStok = document.getElementById('stokChart').getContext('2d');
        new Chart(ctxStok, {
            type: 'bar',
            data: {
                labels: sortedObats.map(o => o.nama_obat.substring(0, 15) + '...'),
                datasets: [{
                    label: 'Stok Fisik',
                    data: sortedObats.map(o => o.stok),
                    backgroundColor: sortedObats.map(o => o.stok <= o.stok_minimum ? '#DC3545' : '#FFC107'),
                }, {
                    label: 'Stok Min',
                    data: sortedObats.map(o => o.stok_minimum),
                    backgroundColor: '#e9ecef',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    x: { stacked: false, ticks: {font: {size: 10}} },
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    });
</script>
@endpush
