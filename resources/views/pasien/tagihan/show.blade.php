@extends('layouts.app')

@section('title', 'Detail Tagihan - ' . $tagihan->kode_pembayaran)

@section('content')
<div class="animated-fade py-3">
    <div class="mb-4">
        <a href="{{ route('pasien.tagihan.index') }}" class="btn btn-outline-secondary btn-sm mb-3"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Riwayat</a>
        <h3 class="fw-bold mb-1">Invoice <span class="text-primary">#{{ $tagihan->kode_pembayaran }}</span></h3>
        <p class="text-muted mb-0">Diterbitkan pada: {{ $tagihan->created_at->format('d F Y, H:i') }} WIB</p>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-premium mb-4"><i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}</div>
    @endif

    <div class="row g-4">
        <!-- Rincian Tagihan -->
        <div class="col-lg-7">
            <div class="card card-premium shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom pb-3 pt-4 px-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-file-invoice me-2 text-primary"></i> Rincian Tagihan Klinik</h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between mb-2 pb-2 border-bottom">
                        <span class="text-muted">Biaya Jasa Konsultasi Dokter ({{ $tagihan->kunjungan->poli->nama_poli }})</span>
                        <span class="fw-bold text-dark">Rp {{ number_format($tagihan->biaya_konsultasi, 0, ',', '.') }}</span>
                    </div>

                    @if($tagihan->kunjungan->resep)
                        <h6 class="fw-bold mt-4 mb-2">Biaya Obat (Farmasi)</h6>
                        @foreach($tagihan->kunjungan->resep->detailResep as $detail)
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <span class="text-dark">{{ $detail->obat->nama_obat }}</span>
                                    <div class="small text-muted">{{ $detail->jumlah }} {{ $detail->obat->satuan }} x Rp {{ number_format($detail->obat->harga_satuan, 0, ',', '.') }}</div>
                                </div>
                                <span class="fw-semibold text-dark">Rp {{ number_format($detail->jumlah * $detail->obat->harga_satuan, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    @endif
                </div>
                <div class="card-footer bg-light p-4 border-top">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fs-5 fw-bold text-dark">Total Pembayaran</span>
                        <span class="fs-4 fw-bold text-success">Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout / Pembayaran -->
        <div class="col-lg-5">
            <div class="card card-premium shadow-sm border-0 h-100 {{ $tagihan->status_pembayaran === 'paid' ? 'bg-success bg-opacity-10' : '' }}">
                <div class="card-body p-4 text-center">
                    @if($tagihan->status_pembayaran === 'paid')
                        <i class="fa-solid fa-circle-check text-success" style="font-size: 5rem; margin-bottom: 1rem;"></i>
                        <h4 class="fw-bold text-success mb-2">Tagihan Lunas</h4>
                        <p class="text-muted mb-4">Terima kasih, pembayaran Anda telah kami terima pada {{ $tagihan->waktu_pembayaran->format('d M Y, H:i') }} WIB menggunakan metode <strong>{{ strtoupper(str_replace('_', ' ', $tagihan->metode_pembayaran)) }}</strong>.</p>
                        
                        <div class="d-grid">
                            <button class="btn btn-outline-success" onclick="window.print()"><i class="fa-solid fa-print me-2"></i> Cetak Kuitansi</button>
                        </div>
                    @else
                        <h5 class="fw-bold mb-4 text-start"><i class="fa-solid fa-wallet me-2 text-primary"></i> Pilih Metode Pembayaran</h5>
                        
                        <form action="{{ route('pasien.tagihan.pay', $tagihan->id) }}" method="POST">
                            @csrf
                            
                            <!-- Custom Radio Buttons for Payment -->
                            <div class="payment-methods mb-4 text-start">
                                <!-- QRIS -->
                                <label class="w-100 mb-3 border rounded p-3 d-flex align-items-center cursor-pointer payment-option">
                                    <input type="radio" name="metode_pembayaran" value="qris" class="form-check-input me-3" checked onchange="togglePaymentInfo('qris')">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold d-flex justify-content-between">
                                            <span>QRIS (E-Wallet & M-Banking)</span>
                                            <i class="fa-solid fa-qrcode text-primary"></i>
                                        </div>
                                        <small class="text-muted">Gopay, OVO, Dana, BCA Mobile, dll.</small>
                                    </div>
                                </label>

                                <!-- Transfer Bank -->
                                <label class="w-100 mb-3 border rounded p-3 d-flex align-items-center cursor-pointer payment-option">
                                    <input type="radio" name="metode_pembayaran" value="transfer_bank" class="form-check-input me-3" onchange="togglePaymentInfo('transfer')">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold d-flex justify-content-between">
                                            <span>Transfer Bank (Virtual Account)</span>
                                            <i class="fa-solid fa-building-columns text-primary"></i>
                                        </div>
                                        <small class="text-muted">BCA, Mandiri, BNI, BRI.</small>
                                    </div>
                                </label>

                                <!-- Cash -->
                                <label class="w-100 border rounded p-3 d-flex align-items-center cursor-pointer payment-option">
                                    <input type="radio" name="metode_pembayaran" value="kasir" class="form-check-input me-3" onchange="togglePaymentInfo('cash')">
                                    <div class="flex-grow-1">
                                        <div class="fw-bold d-flex justify-content-between">
                                            <span>Bayar Tunai di Kasir</span>
                                            <i class="fa-solid fa-money-bill-wave text-success"></i>
                                        </div>
                                        <small class="text-muted">Lakukan pembayaran di loket klinik.</small>
                                    </div>
                                </label>
                            </div>

                            <!-- Dynamic Info Box -->
                            <div id="qris-info" class="payment-info-box mb-4">
                                <div class="bg-light p-3 rounded border text-center">
                                    <h6 class="fw-bold mb-2">Scan QR Code Berikut:</h6>
                                    <!-- Dummy Barcode Placeholder -->
                                    <div class="d-inline-block bg-white p-2 border rounded mb-2">
                                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data={{ urlencode(route('pasien.tagihan.show', $tagihan->id)) }}&color=0f172a" alt="QRIS Barcode" class="img-fluid" width="150" height="150">
                                    </div>
                                    <p class="small text-muted mb-0">Atas Nama: <strong>Klinik SIMPUS Enterprise</strong></p>
                                </div>
                            </div>

                            <div id="transfer-info" class="payment-info-box mb-4" style="display: none;">
                                <div class="bg-light p-3 rounded border text-start">
                                    <h6 class="fw-bold mb-3 border-bottom pb-2">Instruksi Transfer:</h6>
                                    <div class="mb-2">Bank: <strong>BCA</strong></div>
                                    <div class="mb-2">No. Rekening: <strong class="fs-5 text-primary">8899-01-{{ $tagihan->kunjungan->pasien_id }}</strong></div>
                                    <div class="mb-0">Atas Nama: <strong>Klinik SIMPUS</strong></div>
                                </div>
                            </div>

                            <div id="cash-info" class="payment-info-box mb-4" style="display: none;">
                                <div class="alert alert-warning text-start mb-0">
                                    <i class="fa-solid fa-circle-info me-2"></i> Silakan menuju meja Kasir di lobby depan. Tunjukkan Nomor Invoice ini kepada petugas untuk melakukan pembayaran tunai.
                                </div>
                            </div>

                            <hr>

                            <!-- Simulasi Konfirmasi Tombol (Mock) -->
                            <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold pulse-animation">
                                <i class="fa-solid fa-shield-check me-2"></i> Konfirmasi Pembayaran
                            </button>
                            <p class="small text-muted mt-2 mb-0"><i class="fa-solid fa-flask text-info"></i> *Tombol ini adalah simulasi (Bypass Payment Gateway).</p>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .payment-option { transition: all 0.2s ease-in-out; }
    .payment-option:hover { background-color: #f8fafc; border-color: #cbd5e1 !important; }
    .payment-option input:checked + div { color: #0284c7; }
</style>

<script>
    function togglePaymentInfo(type) {
        document.getElementById('qris-info').style.display = 'none';
        document.getElementById('transfer-info').style.display = 'none';
        document.getElementById('cash-info').style.display = 'none';

        if(type === 'qris') document.getElementById('qris-info').style.display = 'block';
        if(type === 'transfer') document.getElementById('transfer-info').style.display = 'block';
        if(type === 'cash') document.getElementById('cash-info').style.display = 'block';
    }
</script>
@endsection
