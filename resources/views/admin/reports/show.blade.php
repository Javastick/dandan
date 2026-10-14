@extends('layouts.admin')

@section('title', 'Detail Laporan Warga - ' . $report->ticket_code)

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
                <div class="border rounded p-2 text-center bg-light mb-4">
                    <img src="{{ asset('storage/' . $report->photo_path) }}" 
                         alt="Bukti Laporan" 
                         class="img-fluid rounded shadow-sm" 
                         style="max-height: 320px; object-fit: contain;">
                </div>

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

                    <dt class="col-sm-4 text-muted">Keterangan Sampah</dt>
                    <dd class="col-sm-8">{{ $report->description }}</dd>
                </dl>
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
                                    <h6 class="fw-bold small mb-2"><i class="bi bi-sliders me-1"></i>Penilaian Kriteria Awal (Skala 1 - 5)</h6>
                                    <p class="text-muted small mb-3">Tentukan penilaian awal berdasarkan foto dan kondisi lapangan.</p>

                                    <div class="row g-2">
                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-semibold">C1: Volume Sampah</label>
                                            <select name="c1_volume" class="form-select form-select-sm" required>
                                                <option value="1">1 - Sangat Kecil (< 1 m³)</option>
                                                <option value="2">2 - Kecil (1 - 3 m³)</option>
                                                <option value="3" selected>3 - Sedang (3 - 5 m³)</option>
                                                <option value="4">4 - Besar (5 - 10 m³)</option>
                                                <option value="5">5 - Sangat Besar (> 10 m³)</option>
                                            </select>
                                        </div>

                                        <div class="col-6 mb-2">
                                            <label class="form-label small fw-semibold">C2: Dampak Lingkungan</label>
                                            <select name="c2_impact" class="form-select form-select-sm" required>
                                                <option value="1">1 - Rendah (Lahan kosong)</option>
                                                <option value="2">2 - Ringan (Dekat sawah)</option>
                                                <option value="3" selected>3 - Sedang (Dekat jalan/warung)</option>
                                                <option value="4">4 - Tinggi (Saluran irigasi)</option>
                                                <option value="5">5 - Kritis (Dekat sekolah/faskes)</option>
                                            </select>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">C3: Frekuensi Laporan</label>
                                            <select name="c3_frequency" class="form-select form-select-sm" required>
                                                <option value="1" selected>1 - 1 Laporan</option>
                                                <option value="2">2 - 2 Laporan</option>
                                                <option value="3">3 - 3 Laporan</option>
                                                <option value="4">4 - 4 Laporan</option>
                                                <option value="5">5 - >= 5 Laporan</option>
                                            </select>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-semibold">C4: Lama Waktu</label>
                                            <select name="c4_duration" class="form-select form-select-sm" required>
                                                <option value="1" selected>1 - < 2 Hari</option>
                                                <option value="2">2 - 2 s.d. 4 Hari</option>
                                                <option value="3">3 - 5 s.d. 7 Hari</option>
                                                <option value="4">4 - 8 s.d. 14 Hari</option>
                                                <option value="5">5 - > 14 Hari</option>
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
