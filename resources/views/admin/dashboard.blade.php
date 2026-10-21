@extends('layouts.admin')

@section('title', 'Dasbor Administrator - DLH Brebes')

@section('content')
<!-- Header Selamat Datang -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-dark text-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1">Selamat Datang, {{ auth()->user()->name }} 👋</h4>
                    <p class="mb-0 opacity-75 small">
                        Sistem Prioritas Penanganan Sampah Liar — Metode AHP | Kabupaten Brebes
                        <span class="badge bg-warning text-dark ms-2">DATA DEMO / PROTOTIPE</span>
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('admin.reports.index', ['status' => 'menunggu']) }}" class="btn btn-warning btn-sm fw-semibold">
                        <i class="bi bi-inbox-fill me-1"></i> Verifikasi Laporan
                        @if($stats['pending_reports_count'] > 0)
                            <span class="badge bg-danger ms-1">{{ $stats['pending_reports_count'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.ahp.rankings') }}" class="btn btn-success btn-sm fw-semibold">
                        <i class="bi bi-trophy me-1"></i> Lihat Ranking Prioritas
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Kartu Statistik Ringkasan -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-5 border-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Laporan Menunggu</span>
                    <span class="p-2 bg-warning-subtle rounded-3 text-warning"><i class="bi bi-clock-history fs-5"></i></span>
                </div>
                <h3 class="fw-bold text-warning mb-0">{{ $stats['pending_reports_count'] }}</h3>
                <small class="text-muted">Belum diverifikasi admin</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-5 border-success">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Titik Terverifikasi</span>
                    <span class="p-2 bg-success-subtle rounded-3 text-success"><i class="bi bi-geo-alt-fill fs-5"></i></span>
                </div>
                <h3 class="fw-bold text-success mb-0">{{ $stats['verified_points_count'] }}</h3>
                <small class="text-muted">Dalam antrian prioritas AHP</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-5 border-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Sedang Ditangani</span>
                    <span class="p-2 bg-primary-subtle rounded-3 text-primary"><i class="bi bi-truck fs-5"></i></span>
                </div>
                <h3 class="fw-bold text-primary mb-0">{{ $stats['in_progress_points_count'] }}</h3>
                <small class="text-muted">Armada lapangan bertugas</small>
            </div>
        </div>
    </div>

    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 border-start border-5 border-secondary">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-semibold">Selesai Ditangani</span>
                    <span class="p-2 bg-secondary-subtle rounded-3 text-secondary"><i class="bi bi-check2-all fs-5"></i></span>
                </div>
                <h3 class="fw-bold mb-0">{{ $stats['resolved_points_count'] }}</h3>
                <small class="text-muted">Sampah liar sudah dibersihkan</small>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Top 5 Prioritas Penanganan -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-trophy-fill text-warning me-2"></i>5 Titik Prioritas Utama (AHP)</h6>
                <a href="{{ route('admin.ahp.rankings') }}" class="btn btn-sm btn-outline-success">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse($topPriorities as $wp)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom hover-bg">
                        <div class="text-center" style="min-width: 55px;">
                            @if($wp->rank && $wp->rank <= 3)
                                <span class="badge {{ $wp->rank === 1 ? 'bg-danger' : ($wp->rank === 2 ? 'bg-warning text-dark' : 'bg-secondary') }} fs-6 px-2">
                                    #{{ $wp->rank }}
                                </span>
                            @else
                                <span class="badge bg-dark fs-6">#{{ $wp->rank ?? '-' }}</span>
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold text-dark small">{{ $wp->name }}</div>
                            <div class="text-muted" style="font-size:11px;">
                                <i class="bi bi-geo-alt me-1"></i>{{ $wp->village }}, {{ $wp->subdistrict }}
                                &bull; <i class="bi bi-ticket-perforated me-1"></i>{{ $wp->reports_count }} aduan
                            </div>
                        </div>
                        <div class="text-end">
                            <div class="fw-bold font-monospace {{ $wp->total_score >= 0.7 ? 'text-danger' : 'text-primary' }}">
                                {{ number_format($wp->total_score, 4) }}
                            </div>
                            <div style="font-size: 10px;" class="text-muted">Skor AHP</div>
                        </div>
                        <a href="{{ route('admin.waste_points.show', $wp->id) }}" class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-eye"></i>
                        </a>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-trophy fs-2 d-block mb-2"></i>
                        <small>Belum ada data titik sampah yang dinilai.<br>Proses laporan warga untuk melihat ranking.</small>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Laporan Masuk Terbaru -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-inbox-fill text-warning me-2"></i>Laporan Menunggu Verifikasi</h6>
                <a href="{{ route('admin.reports.index', ['status' => 'menunggu']) }}" class="btn btn-sm btn-outline-warning">Buka Antrian</a>
            </div>
            <div class="card-body p-0">
                @forelse($latestReports as $r)
                    <div class="d-flex align-items-center gap-3 p-3 border-bottom">
                        <a href="{{ asset('storage/' . $r->photo_path) }}" target="_blank">
                            <img src="{{ asset('storage/' . $r->photo_path) }}"
                                 class="rounded border"
                                 style="width: 45px; height: 45px; object-fit: cover;">
                        </a>
                        <div class="flex-grow-1">
                            <div class="font-monospace fw-bold text-primary" style="font-size: 11px;">{{ $r->ticket_code }}</div>
                            <div class="small fw-semibold">{{ $r->reporter_name }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ $r->village }}, {{ $r->subdistrict }} &bull; {{ $r->created_at->diffForHumans() }}</div>
                        </div>
                        <a href="{{ route('admin.reports.show', $r->id) }}" class="btn btn-sm btn-warning text-dark fw-bold">
                            Verifikasi
                        </a>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                        <small>Tidak ada laporan menunggu.<br>Semua laporan sudah diproses. 🎉</small>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
