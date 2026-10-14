@extends('layouts.admin')

@section('title', 'Verifikasi Laporan Masuk - Panel Admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-inbox text-success me-2"></i>Verifikasi Laporan Warga</h4>
                    <p class="text-muted small mb-0">Tinjau aduan masyarakat, periksa foto bukti, lalu tautkan ke titik sampah fisik atau tolak laporan.</p>
                </div>
                <div class="btn-group" role="group">
                    <a href="{{ route('admin.reports.index', ['status' => 'menunggu']) }}" class="btn btn-sm {{ $status === 'menunggu' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">
                        Menunggu <span class="badge bg-danger ms-1">{{ $counts['menunggu'] }}</span>
                    </a>
                    <a href="{{ route('admin.reports.index', ['status' => 'diterima']) }}" class="btn btn-sm {{ $status === 'diterima' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">
                        Diterima <span class="badge bg-secondary ms-1">{{ $counts['diterima'] }}</span>
                    </a>
                    <a href="{{ route('admin.reports.index', ['status' => 'ditolak']) }}" class="btn btn-sm {{ $status === 'ditolak' ? 'btn-danger fw-bold' : 'btn-outline-secondary' }}">
                        Ditolak <span class="badge bg-secondary ms-1">{{ $counts['ditolak'] }}</span>
                    </a>
                    <a href="{{ route('admin.reports.index', ['status' => 'semua']) }}" class="btn btn-sm {{ $status === 'semua' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}">
                        Semua <span class="badge bg-secondary ms-1">{{ $counts['semua'] }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small text-uppercase">
                    <tr>
                        <th class="ps-3">Kode Tiket</th>
                        <th>Waktu Aduan</th>
                        <th>Pelapor</th>
                        <th>Lokasi Kejadian</th>
                        <th>Bukti Foto</th>
                        <th>Status</th>
                        <th>Tautan Titik Sampah</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reports as $r)
                        <tr>
                            <td class="ps-3 font-monospace fw-semibold text-primary">
                                {{ $r->ticket_code }}
                            </td>
                            <td class="small text-muted">
                                {{ $r->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $r->reporter_name }}</div>
                                <div class="small text-muted"><i class="bi bi-whatsapp text-success me-1"></i>{{ $r->reporter_phone }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $r->village }}, Kec. {{ $r->subdistrict }}</div>
                                <div class="small text-muted text-truncate" style="max-width: 200px;" title="{{ $r->address_detail }}">
                                    {{ $r->address_detail }}
                                </div>
                            </td>
                            <td>
                                <a href="{{ asset('storage/' . $r->photo_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $r->photo_path) }}" 
                                         alt="Thumbnail" 
                                         class="rounded border" 
                                         style="width: 50px; height: 50px; object-fit: cover;">
                                </a>
                            </td>
                            <td>
                                @if($r->status === 'menunggu')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Menunggu</span>
                                @elseif($r->status === 'diterima')
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Diterima</span>
                                @elseif($r->status === 'ditolak')
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Ditolak</span>
                                @endif
                            </td>
                            <td>
                                @if($r->wastePoint)
                                    <a href="{{ route('admin.waste_points.show', $r->wastePoint->id) }}" class="badge bg-info text-dark text-decoration-none">
                                        <i class="bi bi-pin-map-fill me-1"></i>{{ $r->wastePoint->point_code }}
                                    </a>
                                @else
                                    <span class="text-muted small">- Belum Ditautkan -</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('admin.reports.show', $r->id) }}" class="btn btn-sm btn-primary">
                                    <i class="bi bi-eye me-1"></i> Detail / Verifikasi
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                Tidak ada laporan dengan status ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reports->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $reports->links() }}
        </div>
    @endif
</div>
@endsection
