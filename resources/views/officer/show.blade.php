@extends('layouts.officer')

@section('title', 'Penanganan Tugas - ' . $task->point_code)

@if($task->latitude && $task->longitude)
@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
<style>
    #taskMap {
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
        <a href="{{ route('officer.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Tugas
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Informasi Titik Sampah & Lokasi Peta -->
    <div class="col-lg-7">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <span class="badge bg-secondary font-monospace me-2">{{ $task->point_code }}</span>
                    <strong class="fs-6">{{ $task->name }}</strong>
                </div>
                <div>
                    @if($task->status === 'terverifikasi')
                        <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-hourglass me-1"></i>Perlu Ditangani</span>
                    @elseif($task->status === 'diproses')
                        <span class="badge bg-primary px-3 py-2"><i class="bi bi-truck me-1"></i>Sedang Diproses</span>
                    @elseif($task->status === 'selesai')
                        <span class="badge bg-success px-3 py-2"><i class="bi bi-check2-all me-1"></i>Selesai Ditangani</span>
                    @endif
                </div>
            </div>
            <div class="card-body p-4">
                <dl class="row small mb-0">
                    <dt class="col-sm-3 text-muted">Kecamatan</dt>
                    <dd class="col-sm-9 fw-bold">{{ $task->subdistrict }}</dd>

                    <dt class="col-sm-3 text-muted">Desa / Kelurahan</dt>
                    <dd class="col-sm-9">{{ $task->village }}</dd>

                    <dt class="col-sm-3 text-muted">Patokan Lokasi</dt>
                    <dd class="col-sm-9">{{ $task->address_detail }}</dd>

                    @if($task->latitude && $task->longitude)
                        <dt class="col-sm-3 text-muted">Navigasi Rute</dt>
                        <dd class="col-sm-9">
                            <a href="https://www.google.com/maps/dir/?api=1&destination={{ $task->latitude }},{{ $task->longitude }}" target="_blank" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-geo-alt-fill me-1"></i>Buka Rute di Google Maps
                                <i class="bi bi-box-arrow-up-right ms-1 small"></i>
                            </a>
                        </dd>
                    @endif

                    <dt class="col-sm-3 text-muted">Prioritas (AHP)</dt>
                    <dd class="col-sm-9 font-monospace fw-bold text-danger">
                        @if($task->rank)
                            <span class="badge bg-dark me-1">Peringkat #{{ $task->rank }}</span>
                        @endif
                        Skor: {{ number_format($task->total_score, 4) }}
                    </dd>

                    <dt class="col-sm-3 text-muted">Jumlah Aduan</dt>
                    <dd class="col-sm-9"><span class="badge bg-light text-dark border">{{ $task->reports->count() }} Aduan Warga</span></dd>
                </dl>

                @if($task->latitude && $task->longitude)
                    <div class="mt-3">
                        <label class="form-label small fw-semibold text-muted mb-1"><i class="bi bi-map me-1 text-success"></i>Peta Lokasi Titik Sampah</label>
                        <div id="taskMap" class="shadow-sm border"></div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Foto Laporan Awal dari Warga -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-images text-primary me-2"></i>Foto Laporan Sampah Masuk dari Warga
            </div>
            <div class="card-body p-3">
                <div class="row g-3">
                    @forelse($task->reports as $rep)
                        <div class="col-sm-6">
                            <div class="border rounded p-2 bg-light text-center h-100">
                                <a href="{{ asset('storage/' . $rep->photo_path) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $rep->photo_path) }}" alt="Foto Laporan" class="img-fluid rounded mb-2" style="max-height: 160px; object-fit: cover;">
                                </a>
                                <div class="small fw-semibold">{{ $rep->ticket_code }}</div>
                                <div class="text-muted" style="font-size: 11px;">Oleh {{ $rep->reporter_name }} &bull; {{ $rep->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center text-muted py-3 small">Tidak ada foto laporan terlampir.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Form Penanganan & Upload Bukti Lapangan -->
    <div class="col-lg-5">
        <div class="card shadow-sm border-0 mb-4 border-top border-4 border-success">
            <div class="card-header bg-white py-3 fw-bold">
                <i class="bi bi-clipboard-check text-success me-2"></i>Form Penanganan Lapangan
            </div>
            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger small p-2">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('officer.tasks.update', $task->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Status Penanganan Petugas</label>
                        <select name="status" class="form-select" required>
                            <option value="diproses" {{ $task->status === 'diproses' ? 'selected' : '' }}>
                                🚛 Sedang Diproses (Armada/Petugas Melakukan Pengangkutan)
                            </option>
                            <option value="selesai" {{ $task->status === 'selesai' ? 'selected' : '' }}>
                                ✅ Selesai (Sampah Tuntas Diangkut & Bersih)
                            </option>
                        </select>
                        <div class="form-text small">Pilih "Sedang Diproses" saat mulai bekerja, dan "Selesai" setelah lokasi bersih.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Catatan Lapangan / Tindak Lanjut</label>
                        <textarea name="handling_notes" class="form-control" rows="3" placeholder="Contoh: Mengangkut 2 truk sampah liar bersama tim DLH, lokasi disemprot desinfektan...">{{ old('handling_notes', $task->handling_notes) }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold">
                            Unggah Foto Bukti Selesai Penanganan
                            <span class="text-danger">* (Wajib jika status Selesai)</span>
                        </label>
                        <input type="file" name="resolution_photo" class="form-control" accept="image/*">
                        <div class="form-text small">Unggah foto kondisi lokasi setelah sampah dibersihkan tuntas.</div>

                        @if($task->resolution_photo)
                            <div class="mt-3 border rounded p-2 text-center bg-light">
                                <label class="small fw-semibold text-success d-block mb-1"><i class="bi bi-check-circle me-1"></i>Foto Bukti Penanganan Saat Ini:</label>
                                <a href="{{ asset('storage/' . $task->resolution_photo) }}" target="_blank">
                                    <img src="{{ asset('storage/' . $task->resolution_photo) }}" alt="Foto Bukti Penanganan" class="img-fluid rounded" style="max-height: 180px;">
                                </a>
                            </div>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2 fw-semibold">
                        <i class="bi bi-save me-1"></i> Simpan Hasil Penanganan
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@if($task->latitude && $task->longitude)
@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lat = {{ $task->latitude }};
    const lng = {{ $task->longitude }};
    
    const map = L.map('taskMap').setView([lat, lng], 16);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    L.marker([lat, lng])
        .addTo(map)
        .bindPopup('<strong>{{ $task->point_code }}</strong><br>{{ $task->name }}<br><small>{{ $task->village }}, Kec. {{ $task->subdistrict }}</small>')
        .openPopup();
});
</script>
@endpush
@endif
