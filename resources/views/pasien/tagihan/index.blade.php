@extends('layouts.app')

@section('title', 'Riwayat Tagihan & Pembayaran')

@section('content')
<div class="animated-fade py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> Tagihan & Pembayaran</h3>
            <p class="text-muted mb-0">Kelola riwayat tagihan klinik dan apotek Anda.</p>
        </div>
    </div>

    @if(session('status'))
        <div class="alert alert-success alert-premium">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('status') }}
        </div>
    @endif

    <div class="card card-premium shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Tagihan</th>
                        <th>Tanggal</th>
                        <th>Klinik / Poli</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihans as $tagihan)
                        <tr>
                            <td>
                                <span class="fw-bold text-dark">{{ $tagihan->kode_pembayaran }}</span>
                                <div class="small text-muted">ID Kunjungan: {{ $tagihan->kunjungan->no_kunjungan }}</div>
                            </td>
                            <td>{{ $tagihan->created_at->format('d M Y, H:i') }}</td>
                            <td>
                                <div class="fw-bold">{{ $tagihan->kunjungan->poli->nama_poli }}</div>
                                <span class="small text-muted">Dr. {{ $tagihan->kunjungan->dokter->user->name }}</span>
                            </td>
                            <td class="fw-bold text-success">
                                Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}
                            </td>
                            <td>
                                @if($tagihan->status_pembayaran === 'paid')
                                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-double me-1"></i> Lunas</span>
                                @elseif($tagihan->status_pembayaran === 'pending')
                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-clock me-1"></i> Menunggu Pembayaran</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2">{{ ucfirst($tagihan->status_pembayaran) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('pasien.tagihan.show', $tagihan->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-eye me-1"></i> Detail Tagihan
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-receipt display-6 mb-3 text-slate-300"></i>
                                <p class="mb-0">Belum ada riwayat tagihan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 pt-3">
            {{ $tagihans->links() }}
        </div>
    </div>
</div>
@endsection
