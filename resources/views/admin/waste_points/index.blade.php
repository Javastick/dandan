@extends('layouts.admin')

@section('title', 'Titik Lokasi Sampah - Panel Admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-geo-alt text-success me-2"></i>Daftar Titik Lokasi Sampah Liar</h4>
                    <p class="text-muted small mb-0">Entitas fisik tempat pembuangan sampah liar yang dinilai berdasarkan metode AHP-WSM.</p>
                </div>
                <div class="d-flex gap-2 flex-wrap align-items-center">
                    <form action="{{ route('admin.ahp.recalculate') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-success fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i> Hitung Ulang Ranking
                        </button>
                    </form>
                    <div class="btn-group" role="group">
                        <a href="{{ route('admin.waste_points.index', ['status' => 'semua']) }}" class="btn btn-sm {{ $status === 'semua' ? 'btn-dark fw-bold' : 'btn-outline-secondary' }}">
                            Semua <span class="badge bg-secondary ms-1">{{ $counts['semua'] }}</span>
                        </a>
                        <a href="{{ route('admin.waste_points.index', ['status' => 'terverifikasi']) }}" class="btn btn-sm {{ $status === 'terverifikasi' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary' }}">
                            Terverifikasi <span class="badge bg-secondary ms-1">{{ $counts['terverifikasi'] }}</span>
                        </a>
                        <a href="{{ route('admin.waste_points.index', ['status' => 'diproses']) }}" class="btn btn-sm {{ $status === 'diproses' ? 'btn-primary fw-bold' : 'btn-outline-secondary' }}">
                            Diproses <span class="badge bg-secondary ms-1">{{ $counts['diproses'] }}</span>
                        </a>
                        <a href="{{ route('admin.waste_points.index', ['status' => 'selesai']) }}" class="btn btn-sm {{ $status === 'selesai' ? 'btn-success fw-bold' : 'btn-outline-secondary' }}">
                            Selesai <span class="badge bg-secondary ms-1">{{ $counts['selesai'] }}</span>
                        </a>
                    </div>
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
                        <th class="ps-3 text-center" style="width: 70px;">Rank</th>
                        <th>Kode & Nama Titik</th>
                        <th>Kecamatan & Desa</th>
                        <th class="text-center">Jml Laporan</th>
                        <th class="text-center">Skor Prioritas (WSM)</th>
                        <th>Status Penanganan</th>
                        <th class="text-end pe-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wastePoints as $wp)
                        <tr>
                            <td class="ps-3 text-center">
                                @if($wp->rank)
                                    <span class="badge {{ $wp->rank <= 3 ? 'bg-danger fs-6' : 'bg-dark' }}">
                                        #{{ $wp->rank }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-monospace fw-bold text-success">{{ $wp->point_code }}</div>
                                <div class="fw-semibold text-dark">{{ $wp->name }}</div>
                                <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">{{ $wp->address_detail }}</small>
                            </td>
                            <td>
                                <span class="fw-semibold">{{ $wp->subdistrict }}</span>
                                <div class="small text-muted">{{ $wp->village }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1">
                                    <i class="bi bi-ticket-perforated me-1 text-primary"></i>{{ $wp->reports_count }} aduan
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold font-monospace fs-6 {{ $wp->total_score >= 0.7 ? 'text-danger' : 'text-primary' }}">
                                    {{ number_format($wp->total_score, 4) }}
                                </span>
                            </td>
                            <td>
                                @if($wp->status === 'terverifikasi')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-hourglass me-1"></i>Terverifikasi</span>
                                @elseif($wp->status === 'diproses')
                                    <span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Diproses</span>
                                @elseif($wp->status === 'selesai')
                                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Selesai</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.waste_points.show', $wp->id) }}" class="btn btn-outline-secondary" title="Detail Titik">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.waste_points.edit_assessment', $wp->id) }}" class="btn btn-outline-primary" title="Ubah Penilaian Kriteria">
                                        <i class="bi bi-sliders"></i> Nilai
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-geo-alt fs-2 d-block mb-2"></i>
                                Belum ada titik sampah dengan status ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($wastePoints->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $wastePoints->links() }}
        </div>
    @endif
</div>
@endsection
