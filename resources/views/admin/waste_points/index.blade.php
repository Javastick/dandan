@extends('layouts.admin')

@section('title', 'Titik Lokasi Sampah - Panel Admin')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #wastePointsMap {
        height: 380px;
        width: 100%;
        border-radius: 0.5rem;
        z-index: 1;
    }
    .custom-marker-pin {
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        color: white;
        font-weight: bold;
        box-shadow: 0 2px 6px rgba(0,0,0,0.3);
        border: 2px solid white;
    }
</style>
@endpush

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 bg-white">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-geo-alt text-success me-2"></i>Daftar Titik Lokasi Sampah Liar</h4>
                    <p class="text-muted small mb-0">Entitas fisik tempat pembuangan sampah liar yang dinilai berdasarkan metode AHP.</p>
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

<!-- Peta Sebaran Titik Sampah Terverifikasi -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h6 class="fw-bold mb-0">
                <i class="bi bi-map-fill text-success me-2"></i>Peta Persebaran Titik Lokasi Sampah (Kab. Brebes)
            </h6>
            <small class="text-muted">Klik pada pin lokasi untuk melihat ringkasan skor prioritas AHP dan status penanganannya.</small>
        </div>
        <div class="d-flex align-items-center gap-2 small">
            <span class="badge bg-warning text-dark"><i class="bi bi-dot"></i> Terverifikasi (Antrian)</span>
            <span class="badge bg-primary"><i class="bi bi-dot"></i> Sedang Diproses</span>
            <span class="badge bg-success"><i class="bi bi-dot"></i> Selesai Ditangani</span>
        </div>
    </div>
    <div class="card-body p-2">
        <div id="wastePointsMap" class="shadow-sm border"></div>
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
                        <th class="text-center">Skor Prioritas (AHP)</th>
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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const mapPoints = @json($mapPoints ?? []);

    // Default koordinat Brebes
    const DEFAULT_LAT = -6.8703;
    const DEFAULT_LNG = 109.0436;

    const map = L.map('wastePointsMap').setView([DEFAULT_LAT, DEFAULT_LNG], 11);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank">OpenStreetMap</a> contributors',
        maxZoom: 19
    }).addTo(map);

    const bounds = [];

    // Warna marker sesuai status titik
    const statusColors = {
        'terverifikasi': '#ffc107', // Warning / kuning
        'diproses': '#0d6efd',      // Primary / biru
        'selesai': '#198754'        // Success / hijau
    };

    const riversData = @json($riversGeoJson ?? []);
    const settlementsData = @json($settlementsGeoJson ?? []);

    // 1. Layer Sungai (Biru)
    const riverLayer = L.geoJSON(riversData, {
        style: function (feature) {
            return {
                color: '#0d6efd',
                weight: 4,
                opacity: 0.75
            };
        },
        onEachFeature: function (feature, layer) {
            const p = feature.properties || {};
            layer.bindPopup(`<strong><i class="bi bi-water text-primary me-1"></i>${p.name || 'Aliran Sungai'}</strong><br><small class="text-muted">${p.type || 'Sungai/Saluran'}</small>`);
        }
    }).addTo(map);

    // 2. Layer Titik Pemukiman (Oranye)
    const settlementLayer = L.geoJSON(settlementsData, {
        pointToLayer: function (feature, latlng) {
            const p = feature.properties || {};
            const homeIcon = L.divIcon({
                className: 'custom-pin-home',
                html: '<div style="background-color: #fd7e14; color: white; border: 1.5px solid white; border-radius: 50%; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center; font-size: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.3);"><i class="bi bi-houses-fill"></i></div>',
                iconSize: [22, 22],
                iconAnchor: [11, 11]
            });
            return L.marker(latlng, { icon: homeIcon });
        },
        onEachFeature: function (feature, layer) {
            const p = feature.properties || {};
            layer.bindPopup(`<strong><i class="bi bi-houses-fill text-warning me-1"></i>${p.name || 'Permukiman'}</strong><br><small class="text-muted">Kecamatan ${p.subdistrict || 'Brebes'}</small>`);
        }
    }).addTo(map);

    // 3. Layer Titik Sampah
    const wastePointLayer = L.layerGroup().addTo(map);

    mapPoints.forEach(function (pt) {
        if (!pt.latitude || !pt.longitude) return;

        const color = statusColors[pt.status] || '#6c757d';
        const rankLabel = pt.rank ? `#${pt.rank}` : '•';

        // Custom HTML DivIcon dengan indikator nomor peringkat
        const customIcon = L.divIcon({
            className: 'custom-map-icon',
            html: `<div class="custom-marker-pin" style="background-color: ${color}; width: 28px; height: 28px; font-size: 11px;">${rankLabel}</div>`,
            iconSize: [28, 28],
            iconAnchor: [14, 14],
            popupAnchor: [0, -14]
        });

        const marker = L.marker([pt.latitude, pt.longitude], { icon: customIcon }).addTo(wastePointLayer);
        bounds.push([pt.latitude, pt.longitude]);

        // Status badge label
        let statusBadge = '<span class="badge bg-warning text-dark">Terverifikasi</span>';
        if (pt.status === 'diproses') {
            statusBadge = '<span class="badge bg-primary">Sedang Diproses</span>';
        } else if (pt.status === 'selesai') {
            statusBadge = '<span class="badge bg-success">Selesai Ditangani</span>';
        }

        const officerInfo = pt.officer_name 
            ? `<div class="mt-1 small"><strong>Petugas:</strong> <span class="badge bg-info text-dark">${pt.officer_name}</span></div>` 
            : '';

        const popupContent = `
            <div style="min-width: 200px;">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="badge bg-secondary font-monospace">${pt.point_code}</span>
                    ${pt.rank ? `<span class="badge bg-danger">Prioritas #${pt.rank}</span>` : ''}
                </div>
                <strong class="d-block text-dark fs-6">${pt.name || 'Titik Sampah'}</strong>
                <div class="small text-muted mb-1"><i class="bi bi-geo-alt me-1"></i>${pt.village}, Kec. ${pt.subdistrict}</div>
                <div class="small mb-1"><strong>Status:</strong> ${statusBadge}</div>
                <div class="small"><strong>Skor AHP:</strong> <span class="fw-bold text-danger font-monospace">${pt.total_score.toFixed(4)}</span> (${pt.reports_count} aduan)</div>
                ${officerInfo}
                <div class="mt-2 pt-2 border-top text-end">
                    <a href="${pt.url}" class="btn btn-sm btn-primary py-0 px-2 fw-semibold" style="font-size: 11px;">
                        Detail Titik <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        `;

        marker.bindPopup(popupContent);
    });

    // Kontrol Layer Leaflet (bisa dicentang/sembunyikan)
    const overlayLayers = {
        '<span class="fw-bold text-danger"><i class="bi bi-pin-map-fill me-1"></i>Titik Sampah Liar</span>': wastePointLayer,
        '<span class="fw-bold text-primary"><i class="bi bi-water me-1"></i>Jaringan Sungai & Irigasi</span>': riverLayer,
        '<span class="fw-bold text-warning"><i class="bi bi-houses-fill me-1"></i>Kawasan Permukiman</span>': settlementLayer
    };
    L.control.layers(null, overlayLayers, { collapsed: false, position: 'topright' }).addTo(map);

    // Sesuaikan zoom dan batas area peta jika ada pin
    if (bounds.length > 0) {
        map.fitBounds(bounds, { padding: [40, 40], maxZoom: 15 });
    }

    setTimeout(function () {
        map.invalidateSize();
    }, 400);
});
</script>
@endpush

