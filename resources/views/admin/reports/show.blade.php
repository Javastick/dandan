@extends('layouts.admin')

@section('title', 'Detail Laporan Warga - ' . $report->ticket_code)

@if($report->latitude && $report->longitude)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #adminReportMap {
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
    <div class="col-12">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Laporan
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Laporan & Foto -->
    <div class="col-lg-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <span class="fw-bold"><i class="bi bi-ticket-detailed me-2 text-primary"></i>Data Aduan Warga</span>
                @if($report->status === 'menunggu')
                    <span class="badge bg-warning text-dark px-3 py-2">Menunggu Verifikasi</span>
                @elseif($report->status === 'diterima')
                    <span class="badge bg-success px-3 py-2">Laporan Diterima</span>
                @elseif($report->status === 'ditolak')
                    <span class="badge bg-danger px-3 py-2">Laporan Ditolak</span>
                @endif
            </div>
            <div class="card-body p-4">
                @php
                    $allPhotos = $report->all_photos;
                @endphp
                @if(count($allPhotos) > 1)
                    <div class="row g-2 mb-4">
                        @foreach($allPhotos as $idx => $p)
                            <div class="col-4">
                                <a href="{{ asset('storage/' . $p) }}" target="_blank" class="d-block border rounded p-1 bg-light text-center h-100 shadow-sm text-decoration-none">
                                    <img src="{{ asset('storage/' . $p) }}" 
                                         alt="Bukti Laporan {{ $idx + 1 }}" 
                                         class="img-fluid rounded" 
                                         style="height: 120px; width: 100%; object-fit: cover;">
                                    <small class="text-muted d-block mt-1" style="font-size: 11px;">Foto {{ $idx + 1 }} <i class="bi bi-box-arrow-up-right ms-1"></i></small>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="border rounded p-2 text-center bg-light mb-4">
                        <img src="{{ asset('storage/' . ($allPhotos[0] ?? $report->photo_path)) }}" 
                             alt="Bukti Laporan" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 320px; object-fit: contain;">
                    </div>
                @endif

                <dl class="row small mb-0">
                    <dt class="col-sm-4 text-muted">Nomor Tiket</dt>
                    <dd class="col-sm-8 font-monospace fw-bold text-primary">{{ $report->ticket_code }}</dd>

                    <dt class="col-sm-4 text-muted">Nama Pelapor</dt>
                    <dd class="col-sm-8 fw-semibold">{{ $report->reporter_name }}</dd>

                    <dt class="col-sm-4 text-muted">Kontak / WA</dt>
                    <dd class="col-sm-8"><i class="bi bi-whatsapp text-success me-1"></i>{{ $report->reporter_phone }}</dd>

                    <dt class="col-sm-4 text-muted">Waktu Lapor</dt>
                    <dd class="col-sm-8">{{ $report->created_at->format('d F Y, H:i') }} WIB</dd>

                    <dt class="col-sm-4 text-muted">Kecamatan</dt>
                    <dd class="col-sm-8">{{ $report->subdistrict }}</dd>

                    <dt class="col-sm-4 text-muted">Desa/Kelurahan</dt>
                    <dd class="col-sm-8">{{ $report->village }}</dd>

                    <dt class="col-sm-4 text-muted">Patokan Lokasi</dt>
                    <dd class="col-sm-8">{{ $report->address_detail }}</dd>

                    @if($report->latitude && $report->longitude)
                        <dt class="col-sm-4 text-muted">Koordinat Titik</dt>
                        <dd class="col-sm-8 font-monospace">
                            <a href="https://www.google.com/maps?q={{ $report->latitude }},{{ $report->longitude }}" target="_blank" class="text-decoration-none">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $report->latitude }}, {{ $report->longitude }}
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </dd>
                    @endif

                    <dt class="col-sm-4 text-muted">Keterangan Sampah</dt>
                    <dd class="col-sm-8">{{ $report->description }}</dd>
                </dl>

                @if($report->latitude && $report->longitude)
                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-map me-1 text-success"></i>Peta Lokasi Sampah (Koordinat GPS Pelapor)</label>
                        <div id="adminReportMap" class="shadow-sm border"></div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Panel Keputusan Verifikasi -->
    <div class="col-lg-6">
        @if($report->status === 'menunggu')
            <div class="card shadow-sm border-0 border-top border-warning border-4 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-check2-square text-success me-2"></i>Formulir Verifikasi Admin</h5>
                    <small class="text-muted">Pilih salah satu tindakan di bawah ini untuk memproses laporan.</small>
                </div>
                <div class="card-body p-4">
                    <!-- Nav Tabs Opsi Verifikasi -->
                    <ul class="nav nav-pills mb-3 gap-2" id="verifyTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active btn-sm" id="new-point-tab" data-bs-toggle="pill" data-bs-target="#new-point" type="button" role="tab">
                                <i class="bi bi-plus-circle me-1"></i> Jadikan Titik Baru
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm" id="link-point-tab" data-bs-toggle="pill" data-bs-target="#link-point" type="button" role="tab">
                                <i class="bi bi-link-45deg me-1"></i> Tautkan ke Titik Ada
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link btn-sm text-danger" id="reject-tab" data-bs-toggle="pill" data-bs-target="#reject" type="button" role="tab">
                                <i class="bi bi-x-circle me-1"></i> Tolak Laporan
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content border-top pt-3" id="verifyTabContent">
                        <!-- Opsi 1: Jadikan Titik Baru -->
                        <div class="tab-pane fade show active" id="new-point" role="tabpanel">
                            <form action="{{ route('admin.reports.verify', $report->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="accept_new">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Nama / Sebutan Titik Lokasi <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           name="point_name" 
                                           class="form-control" 
                                           value="Tumpukan Sampah {{ $report->village }}, Kec. {{ $report->subdistrict }}" 
                                           required>
                                    <div class="form-text small">Lokasi akan didaftarkan sebagai entitas titik fisik sampah baru.</div>
                                </div>

                                <div class="card bg-light border-0 p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h6 class="fw-bold small mb-0"><i class="bi bi-sliders me-1"></i>Penilaian Kriteria Awal (Skala 1 - 5)</h6>
                                        <span class="badge bg-success-subtle text-success border border-success small"><i class="bi bi-geo-alt-fill me-1"></i>SIG Auto-Detect</span>
                                    </div>
                                    <p class="text-muted small mb-3">Nilai jarak ke pemukiman dan sungai di bawah ini <strong>terhitung otomatis oleh fitur SIG</strong> berdasarkan data geospasial Brebes.</p>

                                    @if($spatialAnalysis)
                                        <div class="alert alert-success py-2 px-3 small mb-3">
                                            <div class="fw-semibold text-success mb-1"><i class="bi bi-compass-fill me-1"></i>Hasil Analisis Spasial Titik Ini:</div>
                                            <ul class="mb-0 ps-3">
                                                <li><strong>Jarak ke Pemukiman:</strong> {{ $spatialAnalysis['settlement']['formatted'] }} (Terdekat: {{ $spatialAnalysis['settlement']['nearest_name'] }}) &rarr; <span class="badge bg-success">Skala Otomatis: {{ $spatialAnalysis['settlement']['scale'] }}</span></li>
                                                <li><strong>Jarak ke Sungai:</strong> {{ $spatialAnalysis['river']['formatted'] }} (Terdekat: {{ $spatialAnalysis['river']['nearest_name'] }}) &rarr; <span class="badge bg-success">Skala Otomatis: {{ $spatialAnalysis['river']['scale'] }}</span></li>
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="row g-2">
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-semibold">C1: Volume Sampah</label>
                                            <select name="c1_volume" class="form-select form-select-sm" required>
                                                <option value="1">1 - Sangat Kecil (&lt; 1 m³)</option>
                                                <option value="2">2 - Kecil (1 - 3 m³)</option>
                                                <option value="3" selected>3 - Sedang (3 - 5 m³)</option>
                                                <option value="4">4 - Besar (5 - 10 m³)</option>
                                                <option value="5">5 - Sangat Besar (&gt; 10 m³)</option>
                                            </select>
                                        </div>

                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-semibold">
                                                C2: Jarak Pemukiman
                                                @if($spatialAnalysis)
                                                    <span class="text-success" title="Otomatis dari SIG">({{ $spatialAnalysis['settlement']['formatted'] }})</span>
                                                @endif
                                            </label>
                                            @php
                                                $autoSettlementScale = $spatialAnalysis['settlement']['scale'] ?? 3;
                                            @endphp
                                            <select name="c2_settlement" class="form-select form-select-sm border-success" required>
                                                <option value="1" {{ $autoSettlementScale == 1 ? 'selected' : '' }}>1 - Sangat Jauh (&gt; 500 m)</option>
                                                <option value="2" {{ $autoSettlementScale == 2 ? 'selected' : '' }}>2 - Jauh (250 - 500 m)</option>
                                                <option value="3" {{ $autoSettlementScale == 3 ? 'selected' : '' }}>3 - Sedang (100 - 250 m)</option>
                                                <option value="4" {{ $autoSettlementScale == 4 ? 'selected' : '' }}>4 - Dekat (50 - 100 m)</option>
                                                <option value="5" {{ $autoSettlementScale == 5 ? 'selected' : '' }}>5 - Sangat Dekat (&lt; 50 m / pemukiman)</option>
                                            </select>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">
                                                C3: Jarak ke Sungai
                                                @if($spatialAnalysis)
                                                    <span class="text-success" title="Otomatis dari SIG">({{ $spatialAnalysis['river']['formatted'] }})</span>
                                                @endif
                                            </label>
                                            @php
                                                $autoRiverScale = $spatialAnalysis['river']['scale'] ?? 3;
                                            @endphp
                                            <select name="c3_river" class="form-select form-select-sm border-success" required>
                                                <option value="1" {{ $autoRiverScale == 1 ? 'selected' : '' }}>1 - Sangat Jauh (&gt; 500 m)</option>
                                                <option value="2" {{ $autoRiverScale == 2 ? 'selected' : '' }}>2 - Jauh (250 - 500 m)</option>
                                                <option value="3" {{ $autoRiverScale == 3 ? 'selected' : '' }}>3 - Sedang (100 - 250 m)</option>
                                                <option value="4" {{ $autoRiverScale == 4 ? 'selected' : '' }}>4 - Dekat (50 - 100 m)</option>
                                                <option value="5" {{ $autoRiverScale == 5 ? 'selected' : '' }}>5 - Sangat Dekat (&lt; 50 m / sempadan air)</option>
                                            </select>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">C4: Frekuensi Aduan</label>
                                            <select name="c4_frequency" class="form-select form-select-sm" required>
                                                <option value="1" selected>1 - 1 Laporan</option>
                                                <option value="2">2 - 2 Laporan</option>
                                                <option value="3">3 - 3 Laporan</option>
                                                <option value="4">4 - 4 Laporan</option>
                                                <option value="5">5 - &gt;= 5 Laporan</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-semibold">
                                    <i class="bi bi-check-lg me-1"></i> Terima & Buat Titik Sampah Baru
                                </button>
                            </form>
                        </div>

                        <!-- Opsi 2: Tautkan ke Titik Ada -->
                        <div class="tab-pane fade" id="link-point" role="tabpanel">
                            <form action="{{ route('admin.reports.verify', $report->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="accept_existing">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold">Pilih Titik Sampah yang Sudah Ada</label>
                                    @if($existingWastePoints->count() > 0)
                                        <select name="waste_point_id" class="form-select" required>
                                            <option value="">-- Pilih Titik di Kec. {{ $report->subdistrict }} --</option>
                                            @foreach($existingWastePoints as $pt)
                                                <option value="{{ $pt->id }}">
                                                    [{{ $pt->point_code }}] {{ $pt->name }} ({{ $pt->village }}) - Status: {{ $pt->status }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <div class="form-text small">Laporan ini akan ditautkan sebagai konfirmasi tambahan (frekuensi laporan akan bertambah).</div>
                                    @else
                                        <div class="alert alert-secondary py-2 small mb-0">
                                            Belum ada titik sampah terdaftar di Kecamatan {{ $report->subdistrict }}. Silakan gunakan opsi <strong>Jadikan Titik Baru</strong>.
                                        </div>
                                    @endif
                                </div>

                                @if($existingWastePoints->count() > 0)
                                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                                        <i class="bi bi-link-45deg me-1"></i> Tautkan Laporan Ini
                                    </button>
                                @endif
                            </form>
                        </div>

                        <!-- Opsi 3: Tolak Laporan -->
                        <div class="tab-pane fade" id="reject" role="tabpanel">
                            <form action="{{ route('admin.reports.verify', $report->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="action" value="reject">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-danger">Alasan Penolakan Laporan <span class="text-danger">*</span></label>
                                    <textarea name="rejection_reason" 
                                              rows="3" 
                                              class="form-control" 
                                              placeholder="Jelaskan alasan (misal: foto tidak jelas, lokasi bukan sampah liar, aduan berulang fiktif)" 
                                              required></textarea>
                                </div>

                                <button type="submit" class="btn btn-danger w-100 fw-semibold">
                                    <i class="bi bi-x-circle me-1"></i> Tolak Laporan Ini
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Status Laporan Sudah Diproses -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 fw-bold">
                    <i class="bi bi-info-circle text-primary me-2"></i>Status Keputusan Verifikasi
                </div>
                <div class="card-body p-4">
                    <div class="mb-3">
                        <span class="text-muted small d-block">Status Saat Ini:</span>
                        @if($report->status === 'diterima')
                            <span class="badge bg-success fs-6">Laporan Diterima & Terverifikasi</span>
                        @elseif($report->status === 'ditolak')
                            <span class="badge bg-danger fs-6">Laporan Ditolak</span>
                        @endif
                    </div>

                    @if($report->verified_at)
                        <div class="mb-3 small">
                            <span class="text-muted">Waktu Diverifikasi:</span>
                            <div class="fw-semibold">{{ $report->verified_at->format('d F Y, H:i') }} WIB</div>
                            <span class="text-muted">Diverifikasi oleh:</span>
                            <div class="fw-semibold">{{ $report->verifiedByUser->name ?? 'Admin' }}</div>
                        </div>
                    @endif

                    @if($report->status === 'ditolak')
                        <div class="alert alert-danger mb-0 small">
                            <strong>Alasan Penolakan:</strong>
                            <p class="mb-0 mt-1">{{ $report->rejection_reason }}</p>
                        </div>
                    @endif

                    @if($report->wastePoint)
                        <div class="card bg-success-subtle border border-success p-3 mt-3">
                            <h6 class="fw-bold text-success mb-2"><i class="bi bi-pin-map-fill me-1"></i>Titik Sampah Terkait:</h6>
                            <div class="small mb-2">
                                <strong>{{ $report->wastePoint->point_code }}</strong> - {{ $report->wastePoint->name }}
                            </div>
                            <a href="{{ route('admin.waste_points.show', $report->wastePoint->id) }}" class="btn btn-sm btn-success">
                                <i class="bi bi-box-arrow-up-right me-1"></i> Buka Titik Sampah & Penilaian AHP
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@if($report->latitude && $report->longitude)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $report->latitude }};
    const lng = {{ $report->longitude }};
    
    const map = L.map('adminReportMap').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    const bounds = [[lat, lng]];

    // Marker Titik Sampah (Merah)
    const trashIcon = L.divIcon({
        className: 'custom-pin-trash',
        html: '<div style="background-color: #dc3545; color: white; border: 2px solid white; border-radius: 50%; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.4);"><i class="bi bi-trash-fill fs-6"></i></div>',
        iconSize: [34, 34],
        iconAnchor: [17, 17]
    });

    L.marker([lat, lng], { icon: trashIcon })
        .addTo(map)
        .bindPopup('<strong>Titik Sampah: {{ $report->ticket_code }}</strong><br><small>{{ $report->village }}, Kec. {{ $report->subdistrict }}</small>')
        .openPopup();

    @if($spatialAnalysis)
        const riverInfo = @json($spatialAnalysis['river']);
        const settlementInfo = @json($spatialAnalysis['settlement']);

        // 1. Tampilkan Sungai Terdekat (Garis & Marker Biru)
        if (riverInfo.nearest_point) {
            const riverLat = riverInfo.nearest_point[0];
            const riverLng = riverInfo.nearest_point[1];
            bounds.push([riverLat, riverLng]);

            // Marker Titik Sungai Terdekat
            const riverIcon = L.divIcon({
                className: 'custom-pin-river',
                html: '<div style="background-color: #0d6efd; color: white; border: 2px solid white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-water fs-6"></i></div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            L.marker([riverLat, riverLng], { icon: riverIcon })
                .addTo(map)
                .bindPopup(`<strong>${riverInfo.nearest_name}</strong><br><small class="text-primary">${riverInfo.type}</small><br>Jarak ke sampah: <strong>${riverInfo.formatted}</strong>`);

            // Garis Penghubung Sampah -> Sungai (Garis Putus-Putus Biru)
            L.polyline([[lat, lng], [riverLat, riverLng]], {
                color: '#0d6efd',
                weight: 3,
                dashArray: '5, 5',
                opacity: 0.8
            }).addTo(map).bindTooltip(`Jarak ke ${riverInfo.nearest_name}: ${riverInfo.formatted}`, { sticky: true });

            // Jika ada garis alur sungai, gambar garis birunya
            if (riverInfo.line_coordinates && riverInfo.line_coordinates.length > 1) {
                const polylineCoords = riverInfo.line_coordinates.map(c => [c[1], c[0]]);
                L.polyline(polylineCoords, {
                    color: '#0dcaf0',
                    weight: 4,
                    opacity: 0.6
                }).addTo(map).bindTooltip(`Aliran ${riverInfo.nearest_name}`);
            }
        }

        // 2. Tampilkan Pemukiman Terdekat (Marker Oranye / Rumah)
        if (settlementInfo.coordinates) {
            const setLat = settlementInfo.coordinates[0];
            const setLng = settlementInfo.coordinates[1];
            bounds.push([setLat, setLng]);

            const houseIcon = L.divIcon({
                className: 'custom-pin-house',
                html: '<div style="background-color: #fd7e14; color: white; border: 2px solid white; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.3);"><i class="bi bi-houses-fill fs-6"></i></div>',
                iconSize: [30, 30],
                iconAnchor: [15, 15]
            });

            L.marker([setLat, setLng], { icon: houseIcon })
                .addTo(map)
                .bindPopup(`<strong>${settlementInfo.nearest_name}</strong><br><small class="text-muted">Kecamatan ${settlementInfo.subdistrict}</small><br>Jarak ke sampah: <strong>${settlementInfo.formatted}</strong>`);

            // Garis Penghubung Sampah -> Pemukiman (Garis Putus-Putus Oranye)
            L.polyline([[lat, lng], [setLat, setLng]], {
                color: '#fd7e14',
                weight: 3,
                dashArray: '5, 5',
                opacity: 0.8
            }).addTo(map).bindTooltip(`Jarak ke Pemukiman: ${settlementInfo.formatted}`, { sticky: true });
        }

        // Sesuaikan zoom agar sampah, sungai, dan pemukiman semuanya terlihat
        if (bounds.length > 1) {
            map.fitBounds(bounds, { padding: [50, 50], maxZoom: 16 });
        }
    @endif
});
</script>
@endpush
@endif
