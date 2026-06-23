@extends('layouts.app')

@section('title', 'Manajemen Ambulans Darurat')

@section('content')
<div class="animated-fade py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-1"><i class="fa-solid fa-truck-medical text-danger me-2"></i> Panggilan Ambulans Darurat</h3>
            <p class="text-muted mb-0">Manajemen permohonan layanan jemput ambulans pasien.</p>
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
                        <th>Waktu Panggilan</th>
                        <th>Identitas Pasien</th>
                        <th>Alamat & Kontak</th>
                        <th>Keluhan Darurat</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($panggilan as $item)
                        <tr class="{{ $item->status === 'menunggu' ? 'table-danger' : '' }}">
                            <td>
                                <div class="fw-bold">{{ $item->created_at->format('d M Y') }}</div>
                                <span class="small text-muted">{{ $item->created_at->format('H:i') }} WIB</span>
                                @if($item->created_at->diffInMinutes(now()) < 30 && $item->status === 'menunggu')
                                    <span class="badge bg-danger ms-1 pulse-animation">BARU</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $item->pasien->user->name }}</div>
                                <div class="small text-muted">NIK: {{ $item->pasien->nik }}</div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-primary"><i class="fa-solid fa-phone me-1"></i> {{ $item->no_telepon }}</div>
                                <div class="small text-muted text-wrap" style="max-width: 200px;">{{ $item->alamat_jemput }}</div>
                            </td>
                            <td>
                                <div class="small text-muted text-wrap text-truncate" style="max-width: 200px;">
                                    {{ $item->keluhan_darurat ?: '-' }}
                                </div>
                            </td>
                            <td>
                                @if($item->status === 'menunggu')
                                    <span class="badge bg-danger px-3 py-2"><i class="fa-solid fa-bell me-1"></i> Menunggu</span>
                                @elseif($item->status === 'dijemput')
                                    <span class="badge bg-warning text-dark px-3 py-2"><i class="fa-solid fa-truck-fast me-1"></i> Sedang Dijemput</span>
                                @elseif($item->status === 'selesai')
                                    <span class="badge bg-success px-3 py-2"><i class="fa-solid fa-check-double me-1"></i> Selesai</span>
                                @else
                                    <span class="badge bg-secondary px-3 py-2"><i class="fa-solid fa-ban me-1"></i> Dibatalkan</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#updateModal{{ $item->id }}">
                                    Update Status
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-truck-medical display-6 mb-3 text-slate-300"></i>
                                <p class="mb-0">Belum ada panggilan ambulans darurat.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Update Modals -->
        @foreach($panggilan as $item)
        <div class="modal fade" id="updateModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content border-0 shadow text-start">
              <form action="{{ route('admin.ambulans.status', $item->id) }}" method="POST">
                  @csrf
                  <div class="modal-header">
                      <h6 class="modal-title fw-bold">Update Status</h6>
                      <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                      <select name="status" class="form-select">
                          <option value="menunggu" {{ $item->status === 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                          <option value="dijemput" {{ $item->status === 'dijemput' ? 'selected' : '' }}>Sedang Dijemput</option>
                          <option value="selesai" {{ $item->status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                          <option value="batal" {{ $item->status === 'batal' ? 'selected' : '' }}>Batal</option>
                      </select>
                  </div>
                  <div class="modal-footer pb-2">
                      <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Tutup</button>
                      <button type="submit" class="btn btn-sm btn-primary">Simpan</button>
                  </div>
              </form>
            </div>
          </div>
        </div>
        @endforeach

        <div class="card-footer bg-white border-top-0 pt-3">
            {{ $panggilan->links() }}
        </div>
    </div>
</div>
@endsection
