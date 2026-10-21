@extends('layouts.officer')

@section('title', 'Daftar Tugas Penanganan - Petugas Kebersihan')

@section('content')
<!-- Header Petugas -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-success text-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Halo, {{ $officer->name }} 👷‍♂️</h4>
                    <p class="mb-0 opacity-75 small">
                        Daftar penugasan pembersihan titik sampah liar yang didelegasikan oleh Administrator DLH Brebes.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Statistik Tugas -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('officer.dashboard') }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-5 border-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Total Tugas</span>
                        <span class="p-2 bg-secondary-subtle rounded-3 text-secondary"><i class="bi bi-list-task fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark">{{ $stats['total'] }}</h3>
                    <small class="text-muted">Semua titik sampah ditugaskan</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('officer.dashboard', ['status' => 'terverifikasi']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-5 border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Perlu Ditangani</span>
                        <span class="p-2 bg-warning-subtle rounded-3 text-warning"><i class="bi bi-exclamation-circle fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold text-warning mb-0">{{ $stats['terverifikasi'] }}</h3>
                    <small class="text-muted">Menunggu petugas berangkat</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('officer.dashboard', ['status' => 'diproses']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-5 border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Sedang Diproses</span>
                        <span class="p-2 bg-primary-subtle rounded-3 text-primary"><i class="bi bi-truck fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold text-primary mb-0">{{ $stats['diproses'] }}</h3>
                    <small class="text-muted">Proses pengangkutan / lapangan</small>
                </div>
            </div>
        </a>
    </div>

    <div class="col-sm-6 col-lg-3">
        <a href="{{ route('officer.dashboard', ['status' => 'selesai']) }}" class="text-decoration-none">
            <div class="card shadow-sm border-0 h-100 border-start border-5 border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="text-muted small fw-semibold">Selesai Ditangani</span>
                        <span class="p-2 bg-success-subtle rounded-3 text-success"><i class="bi bi-check2-circle fs-5"></i></span>
                    </div>
                    <h3 class="fw-bold text-success mb-0">{{ $stats['selesai'] }}</h3>
                    <small class="text-muted">Sudah tuntas dibersihkan</small>
                </div>
            </div>
        </a>
    </div>
</div>

<!-- Filter dan Daftar Tugas -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h6 class="fw-bold mb-0">
            <i class="bi bi-card-checklist text-success me-2"></i>Daftar Tugas Penanganan Lapangan
        </h6>
        <div class="btn-group btn-group-sm">
            <a href="{{ route('officer.dashboard', ['status' => 'semua']) }}" class="btn {{ $status === 'semua' ? 'btn-success' : 'btn-outline-secondary' }}">Semua ({{ $stats['total'] }})</a>
            <a href="{{ route('officer.dashboard', ['status' => 'terverifikasi']) }}" class="btn {{ $status === 'terverifikasi' ? 'btn-warning text-dark' : 'btn-outline-secondary' }}">Perlu Ditangani ({{ $stats['terverifikasi'] }})</a>
            <a href="{{ route('officer.dashboard', ['status' => 'diproses']) }}" class="btn {{ $status === 'diproses' ? 'btn-primary' : 'btn-outline-secondary' }}">Sedang Diproses ({{ $stats['diproses'] }})</a>
            <a href="{{ route('officer.dashboard', ['status' => 'selesai']) }}" class="btn {{ $status === 'selesai' ? 'btn-success' : 'btn-outline-secondary' }}">Selesai ({{ $stats['selesai'] }})</a>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th class="text-center" style="width: 70px;">Prioritas</th>
                        <th>Kode & Lokasi</th>
                        <th>Wilayah</th>
                        <th class="text-center">Total Aduan</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tasks as $t)
                        <tr>
                            <td class="text-center">
                                @if($t->rank)
                                    <span class="badge {{ $t->rank <= 3 ? 'bg-danger' : 'bg-dark' }} fs-6">
                                        #{{ $t->rank }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">-</span>
                                @endif
                                <div class="text-muted" style="font-size: 10px;">Skor: {{ number_format($t->total_score, 3) }}</div>
                            </td>
                            <td>
                                <span class="badge bg-secondary font-monospace">{{ $t->point_code }}</span>
                                <div class="fw-bold text-dark mt-1">{{ $t->name }}</div>
                                <div class="text-muted small">{{ Str::limit($t->address_detail, 45) }}</div>
                            </td>
                            <td class="small">
                                <div><i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $t->village }}</div>
                                <div class="text-muted">Kec. {{ $t->subdistrict }}</div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $t->reports_count }} aduan</span>
                            </td>
                            <td class="text-center">
                                @if($t->status === 'terverifikasi')
                                    <span class="badge bg-warning text-dark"><i class="bi bi-clock me-1"></i>Perlu Ditangani</span>
                                @elseif($t->status === 'diproses')
                                    <span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Sedang Diproses</span>
                                @elseif($t->status === 'selesai')
                                    <span class="badge bg-success"><i class="bi bi-check2-all me-1"></i>Selesai</span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <a href="{{ route('officer.tasks.show', $t->id) }}" class="btn btn-sm btn-outline-success fw-semibold">
                                    <i class="bi bi-pencil-square me-1"></i> Tangani
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada tugas penanganan sampah yang ditugaskan kepada Anda.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($tasks->hasPages())
        <div class="card-footer bg-white py-3">
            {{ $tasks->links() }}
        </div>
    @endif
</div>
@endsection
