@extends('layouts.admin')

@section('title', 'Ranking Prioritas Penanganan Sampah - Metode AHP')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-trophy text-warning me-2"></i>Daftar Prioritas Penanganan Sampah Liar</h4>
                    <p class="text-muted small mb-0">
                        Peringkat dihitung menggunakan metode <strong>AHP (Analytic Hierarchy Process)</strong> dengan pendekatan pengukuran absolut (*Rating Model*).
                        Skor preferensi lebih tinggi = prioritas penanganan lebih mendesak.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.ahp.matrix') }}" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-diagram-3 me-1"></i> Kelola Matriks AHP
                    </a>
                    <form action="{{ route('admin.ahp.recalculate') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-success btn-sm fw-semibold">
                            <i class="bi bi-arrow-repeat me-1"></i> Hitung Ulang Ranking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bobot Kriteria Aktif -->
<div class="row g-2 mb-4">
    @foreach($criteria as $c)
        <div class="col-sm-6 col-lg-3">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-body text-center py-3">
                    <span class="badge bg-success mb-1">{{ $c->code }}</span>
                    <div class="small fw-semibold text-dark">{{ $c->name }}</div>
                    <div class="fw-bold fs-5 font-monospace text-success">{{ number_format($c->weight, 4) }}</div>
                    <div class="progress mt-1" style="height: 4px;">
                        <div class="progress-bar bg-success" style="width: {{ round($c->weight * 100, 1) }}%"></div>
                    </div>
                    <div class="text-muted mt-1" style="font-size: 11px;">{{ round($c->weight * 100, 2) }}% dari total bobot</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Tabel Ranking Prioritas -->
@if($wastePoints->count() > 0)
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-dark small text-uppercase">
                        <tr>
                            <th class="ps-3 text-center" style="width: 70px;">Rank</th>
                            <th>Titik Lokasi Sampah</th>
                            <th>Kecamatan</th>
                            @foreach($criteria as $c)
                                <th class="text-center">{{ $c->code }}<br><span class="fw-normal opacity-75">(w={{ number_format($c->weight, 3) }})</span></th>
                            @endforeach
                            <th class="text-center">Skor Akhir AHP</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wastePoints as $wp)
                            @php
                                $assessMap = $wp->assessments->keyBy('criterion_id');
                            @endphp
                            <tr class="{{ $wp->rank && $wp->rank <= 3 ? 'table-warning' : '' }}">
                                <td class="ps-3 text-center">
                                    @if($wp->rank)
                                        @if($wp->rank === 1)
                                            <span class="badge bg-danger fs-5">#1</span>
                                        @elseif($wp->rank === 2)
                                            <span class="badge bg-warning text-dark fs-6">#2</span>
                                        @elseif($wp->rank === 3)
                                            <span class="badge bg-secondary fs-6">#3</span>
                                        @else
                                            <span class="badge bg-dark">#{{ $wp->rank }}</span>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="font-monospace fw-bold text-success">{{ $wp->point_code }}</div>
                                    <div class="fw-semibold">{{ $wp->name }}</div>
                                    <small class="text-muted">{{ $wp->village }}</small>
                                </td>
                                <td>{{ $wp->subdistrict }}</td>

                                @foreach($criteria as $c)
                                    @php
                                        $assess = $assessMap[$c->id] ?? null;
                                        $raw = $assess ? $assess->raw_value : 1;
                                        $norm = round($raw / 5.0, 4);
                                        $sub = round(((float)$c->weight) * $norm, 4);
                                    @endphp
                                    <td class="text-center">
                                        <span class="badge {{ $raw >= 4 ? 'bg-danger' : ($raw >= 3 ? 'bg-warning text-dark' : 'bg-secondary') }} mb-1">{{ $raw }}</span>
                                        <div class="font-monospace" style="font-size: 10px;">
                                            {{ number_format($norm, 3) }} × {{ number_format($c->weight, 3) }}<br>
                                            = <strong>{{ number_format($sub, 4) }}</strong>
                                        </div>
                                    </td>
                                @endforeach

                                <td class="text-center">
                                    <div class="fw-bold font-monospace fs-5 {{ $wp->total_score >= 0.7 ? 'text-danger' : ($wp->total_score >= 0.5 ? 'text-warning' : 'text-secondary') }}">
                                        {{ number_format($wp->total_score, 4) }}
                                    </div>
                                    @if($wp->calculated_at)
                                        <div class="text-muted" style="font-size: 10px;">
                                            {{ $wp->calculated_at->format('d/m/Y H:i') }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if($wp->status === 'terverifikasi')
                                        <span class="badge bg-warning text-dark"><i class="bi bi-hourglass me-1"></i>Antrian</span>
                                    @elseif($wp->status === 'diproses')
                                        <span class="badge bg-primary"><i class="bi bi-truck me-1"></i>Diproses</span>
                                    @endif
                                </td>

                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.waste_points.show', $wp->id) }}" class="btn btn-outline-secondary" title="Detail">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.waste_points.edit_assessment', $wp->id) }}" class="btn btn-outline-primary" title="Ubah Penilaian">
                                            <i class="bi bi-sliders"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light py-2 px-3 small text-muted">
            <i class="bi bi-info-circle me-1"></i>
            <strong>Sintesis Nilai AHP:</strong> V<sub>i</sub> = Σ (w<sub>j</sub> × S<sub>ij</sub>), di mana S<sub>ij</sub> = Nilai Skala / 5 dan w<sub>j</sub> = Bobot Kriteria AHP.
            Data di atas adalah <span class="badge bg-warning text-dark">DATA DEMO</span> — bukan data penelitian final. Bobot kriteria dapat diubah melalui halaman Matriks AHP.
        </div>
    </div>
@else
    <div class="card shadow-sm border-0 text-center p-5">
        <i class="bi bi-trophy fs-1 text-muted mb-3"></i>
        <h5 class="fw-bold">Belum Ada Data Titik Sampah untuk Diperingkat</h5>
        <p class="text-muted small mb-3">Proses laporan warga terlebih dahulu dan buat titik sampah dari menu Verifikasi Laporan.</p>
        <div>
            <a href="{{ route('admin.reports.index') }}" class="btn btn-success btn-sm">
                <i class="bi bi-inbox me-1"></i> Buka Verifikasi Laporan
            </a>
        </div>
    </div>
@endif
@endsection
