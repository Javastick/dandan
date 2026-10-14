@extends('layouts.admin')

@section('title', 'Detail Titik Sampah - ' . $wastePoint->point_code)

@if($wastePoint->latitude && $wastePoint->longitude)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #wastePointMap {
        height: 240px;
        width: 100%;
        border-radius: 0.5rem;
        z-index: 1;
    }
</style>
@endpush
@endif

@section('content')
<div class="row mb-3">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <a href="{{ route('admin.waste_points.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Titik Sampah
        </a>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.waste_points.edit_assessment', $wastePoint->id) }}" class="btn btn-primary btn-sm fw-semibold">
                <i class="bi bi-sliders me-1"></i> Ubah Penilaian Kriteria
            </a>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Informasi Titik Sampah & Penilaian Kriteria -->
    <div class="col-lg-7">
        <!-- Info Titik Sampah -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="badge bg-success font-monospace me-2">{{ $wastePoint->point_code }}</span>
                    <strong class="fs-6">{{ $wastePoint->name }}</strong>
                </div>
                <div>
                    @if($wastePoint->status === 'terverifikasi')
                        <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-hourglass me-1"></i>Terverifikasi</span>
                    @elseif($wastePoint->status === 'diproses')
                        <span class="badge bg-primary px-3 py-2"><i class="bi bi-truck me-1"></i>Sedang Diproses</span>
                    @elseif($wastePoint->status === 'selesai')
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check2-all me-1"></i>Selesai Ditangani</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <dl class="row small mb-0">
                    <dt class="col-sm-3 text-muted">Kecamatan</dt>
                    <dd class="col-sm-9 fw-bold">{{ $wastePoint->subdistrict }}</dd>

                    <dt class="col-sm-3 text-muted">Desa / Kelurahan</dt>
                    <dd class="col-sm-9">{{ $wastePoint->village }}</dd>

                    <dt class="col-sm-3 text-muted">Patokan Lokasi</dt>
                    <dd class="col-sm-9">{{ $wastePoint->address_detail }}</dd>

                    @if($wastePoint->latitude && $wastePoint->longitude)
                        <dt class="col-sm-3 text-muted">Koordinat Titik</dt>
                        <dd class="col-sm-9 font-monospace">
                            <a href="https://www.google.com/maps?q={{ $wastePoint->latitude }},{{ $wastePoint->longitude }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $wastePoint->latitude }}, {{ $wastePoint->longitude }}
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </dd>
                    @endif

                    <dt class="col-sm-3 text-muted">Total Aduan Warga</dt>
                    <dd class="col-sm-9"><span class="badge bg-light text-dark border">{{ $wastePoint->reports_count }} Laporan</span></dd>

                    <dt class="col-sm-3 text-muted">Skor Prioritas (WSM)</dt>
                    <dd class="col-sm-9 font-monospace fw-bold fs-6 text-danger">
                        {{ number_format($wastePoint->total_score, 4) }}
                        @if($wastePoint->rank)
                            <span class="badge bg-dark ms-2">Peringkat #{{ $wastePoint->rank }}</span>
                        @endif
                    </dd>

                    <dt class="col-sm-3 text-muted">Waktu Dihitung</dt>
                    <dd class="col-sm-9 text-muted">{{ $wastePoint->calculated_at ? $wastePoint->calculated_at->format('d M Y, H:i:s') : 'Belum dihitung' }}</dd>
                </dl>

                @if($wastePoint->latitude && $wastePoint->longitude)
                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-map me-1 text-success"></i>Peta Lokasi Titik Sampah</label>
                        <div id="wastePointMap" class="shadow-sm border"></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Tabel Rincian Penilaian Kriteria AHP-WSM -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-calculator text-primary me-2"></i>Rincian Komputasi Penilaian Kriteria</h6>
                <a href="{{ route('admin.waste_points.edit_assessment', $wastePoint->id) }}" class="btn btn-outline-primary btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Nilai
                </a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-0 small text-center">
                        <thead class="table-light">
                            <tr>
                                <th>Kode</th>
                                <th class="text-start">Kriteria</th>
                                <th>Nilai Skala (1-5)</th>
                                <th>Ternormalisasi (R_ij)</th>
                                <th>Bobot AHP (w_j)</th>
                                <th>Subtotal (w_j * R_ij)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $assessMap = $wastePoint->assessments->keyBy('criterion_id');
                                $calcSum = 0;
                            @endphp
                            @foreach($criteria as $c)
                                @php
                                    $assess = $assessMap[$c->id] ?? null;
                                    $raw = $assess ? $assess->raw_value : 1;
                                    $norm = round($raw / 5.0, 4);
                                    $subtotal = round(((float)$c->weight) * $norm, 4);
                                    $calcSum += $subtotal;
                                @endphp
                                <tr>
                                    <td class="font-monospace fw-bold">{{ $c->code }}</td>
                                    <td class="text-start">{{ $c->name }}</td>
                                    <td><span class="badge bg-primary px-2 py-1 fs-6">{{ $raw }}</span></td>
                                    <td class="font-monospace">{{ number_format($norm, 4) }}</td>
                                    <td class="font-monospace">{{ number_format($c->weight, 4) }}</td>
                                    <td class="font-monospace fw-bold text-success">{{ number_format($subtotal, 4) }}</td>
                                </tr>
                            @endforeach
                            <tr class="table-light fw-bold">
                                <td colspan="5" class="text-end pe-3">Total Skor Komposit Prioritas (WSM):</td>
                                <td class="font-monospace text-danger fs-6">{{ number_format($calcSum, 4) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            @if($wastePoint->weights_snapshot)
                <div class="card-footer bg-light py-2 small text-muted">
                    <i class="bi bi-clock-history me-1"></i> <strong>Histori Bobot Saat Perhitungan:</strong>
                    <code>{{ json_encode($wastePoint->weights_snapshot) }}</code>
                </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Pembaruan Status Penanganan & Bukti Penyelesaian -->
    <div class="col-lg-5">
        <!-- Form Pembaruan Status -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-arrow-repeat text-success me-2"></i>Pembaruan Status Penanganan Lapangan
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.waste_points.update_status', $wastePoint->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Pilih Status Penanganan Baru</label>
                        <select name="status" id="statusSelect" class="form-select" required>
                            <option value="terverifikasi" {{ $wastePoint->status === 'terverifikasi' ? 'selected' : '' }}>
                                1. Terverifikasi (Masuk Antrian Prioritas)
                            </option>
                            <option value="diproses" {{ $wastePoint->status === 'diproses' ? 'selected' : '' }}>
                                2. Diproses (Armada/Petugas Ditugaskan)
                            </option>
                            <option value="selesai" {{ $wastePoint->status === 'selesai' ? 'selected' : '' }}>
                                3. Selesai (Sampah Dibersihkan Tuntas)
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Unggah Foto Bukti Penanganan (Opsional/Selesai)</label>
                        <input type="file" name="resolution_photo" class="form-control form-control-sm" accept="image/*">
                        <div class="form-text small">Unggah foto kondisi lokasi setelah tumpukan sampah liar diangkut dan dibersihkan.</div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 fw-semibold">
                        <i class="bi bi-save me-1"></i> Perbarui Status Penanganan
                    </button>
                </form>
            </div>
        </div>

        <!-- Daftar Aduan Laporan Warga yang Tertaut -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-people text-primary me-2"></i>Aduan Warga yang Mengonfirmasi Lokasi Ini ({{ $wastePoint->reports->count() }})
            </div>
            <div class="card-body p-3">
                @forelse($wastePoint->reports as $r)
                    <div class="border rounded p-2 mb-2 d-flex align-items-center gap-3 bg-light">
                        <img src="{{ asset('storage/' . $r->photo_path) }}" 
                             alt="Foto Aduan" 
                             class="rounded border" 
                             style="width: 50px; height: 50px; object-fit: cover;">
                        <div class="flex-grow-1 small">
                            <div class="fw-bold font-monospace text-primary">{{ $r->ticket_code }}</div>
                            <div>{{ $r->reporter_name }} ({{ $r->created_at->format('d/m/Y') }})</div>
                        </div>
                        <a href="{{ route('admin.reports.show', $r->id) }}" class="btn btn-sm btn-outline-secondary">
                            Detail
                        </a>
                    </div>
                @empty
                    <p class="text-muted small mb-0 text-center py-2">Belum ada aduan warga yang tertaut ke titik ini.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection

@if($wastePoint->latitude && $wastePoint->longitude)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $wastePoint->latitude }};
    const lng = {{ $wastePoint->longitude }};
    
    const map = L.map('wastePointMap').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup('<strong>{{ $wastePoint->point_code }}</strong><br>{{ $wastePoint->name }}<br><small>{{ $wastePoint->village }}, Kec. {{ $wastePoint->subdistrict }}</small>')
        .openPopup();
});
</script>
@endpush
@endif
