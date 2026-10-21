@extends('layouts.app')

@section('title', 'Lacak Status Laporan Sampah - Kab. Brebes')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Form Pencarian Tiket -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body p-4 text-center">
                    <h4 class="fw-bold mb-2"><i class="bi bi-search text-success me-2"></i>Lacak Progres Laporan Sampah</h4>
                    <p class="text-muted small mb-4">Masukkan kode tiket unik yang Anda peroleh saat mengirim laporan untuk melihat tahapan penanganannya.</p>

                    <form action="{{ route('report.status') }}" method="GET" class="d-flex justify-content-center gap-2">
                        <input type="text" 
                               name="ticket" 
                               class="form-control form-control-lg font-monospace text-uppercase" 
                               style="max-width: 380px;" 
                               placeholder="Contoh: LPR-261012-ABCDE" 
                               value="{{ $ticket }}" 
                               required>
                        <button type="submit" class="btn btn-success px-4 fw-semibold">
                            Cari Tiket
                        </button>
                    </form>
                </div>
            </div>

            <!-- Hasil Pencarian -->
            @if($ticket)
                @if($report)
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <span class="badge bg-secondary font-monospace me-2">{{ $report->ticket_code }}</span>
                                <span class="text-muted small">Dikirim: {{ $report->created_at->format('d M Y, H:i') }} WIB</span>
                            </div>
                            <div>
                                @if($report->status === 'menunggu')
                                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                        <i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi
                                    </span>
                                @elseif($report->status === 'diterima')
                                    <span class="badge bg-primary px-3 py-2 fs-6">
                                        <i class="bi bi-check-circle me-1"></i> Terverifikasi / Dalam Antrian
                                    </span>
                                @elseif($report->status === 'ditolak')
                                    <span class="badge bg-danger px-3 py-2 fs-6">
                                        <i class="bi bi-x-circle me-1"></i> Laporan Ditolak
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body p-4">
                            <!-- Timeline Status Visual -->
                            <div class="row g-2 text-center mb-4">
                                <div class="col-4">
                                    <div class="p-2 rounded {{ $report->status !== 'ditolak' ? 'bg-success text-white' : 'bg-secondary text-white' }}">
                                        <i class="bi bi-envelope-check d-block fs-4 mb-1"></i>
                                        <small class="fw-semibold">1. Laporan Dikirim</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded {{ in_array($report->status, ['diterima']) ? 'bg-success text-white' : ($report->status === 'ditolak' ? 'bg-danger text-white' : 'bg-light text-muted') }}">
                                        <i class="bi bi-shield-check d-block fs-4 mb-1"></i>
                                        <small class="fw-semibold">2. {{ $report->status === 'ditolak' ? 'Ditolak Petugas' : 'Verifikasi DLH' }}</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    @php
                                        $pointStatus = $report->wastePoint->status ?? null;
                                        $isResolved = ($pointStatus === 'selesai');
                                        $inProgress = ($pointStatus === 'diproses');
                                    @endphp
                                    <div class="p-2 rounded {{ $isResolved ? 'bg-success text-white' : ($inProgress ? 'bg-primary text-white' : 'bg-light text-muted') }}">
                                        <i class="bi bi-trash-fill d-block fs-4 mb-1"></i>
                                        <small class="fw-semibold">3. {{ $isResolved ? 'Selesai Dibersihkan' : ($inProgress ? 'Sedang Diproses' : 'Penanganan Lapangan') }}</small>
                                    </div>
                                </div>
                            </div>

                            @if($report->status === 'ditolak')
                                <div class="alert alert-danger mb-4">
                                    <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-1"></i> Alasan Penolakan Laporan:</h6>
                                    <p class="mb-0 small">{{ $report->rejection_reason ?? 'Tidak memenuhi kriteria sampah liar yang dapat ditindaklanjuti.' }}</p>
                                </div>
                            @endif

                            @if($report->wastePoint)
                                <div class="card bg-success-subtle border border-success p-3 mb-4">
                                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-pin-map-fill me-2"></i>Titik Penanganan DLH Terkait</h6>
                                    <div class="small">
                                        <div><strong>Kode Titik:</strong> {{ $report->wastePoint->point_code }} - {{ $report->wastePoint->name ?? 'Titik Sampah' }}</div>
                                        <div><strong>Status Titik:</strong> 
                                            @if($report->wastePoint->status === 'terverifikasi')
                                                <span class="badge bg-secondary">Masuk Antrian Prioritas</span>
                                            @elseif($report->wastePoint->status === 'diproses')
                                                <span class="badge bg-primary">Armada Lapangan Sedang Menangani</span>
                                            @elseif($report->wastePoint->status === 'selesai')
                                                <span class="badge bg-success">Tuntas Ditangani / Bersih</span>
                                            @endif
                                        </div>
                                        @if($report->wastePoint->assignedOfficer)
                                            <div class="mt-1">
                                                <strong>Petugas Penanganan:</strong> 
                                                <span class="badge bg-info text-dark"><i class="bi bi-person-badge me-1"></i>{{ $report->wastePoint->assignedOfficer->name }}</span>
                                            </div>
                                        @endif
                                        @if($report->wastePoint->rank)
                                            <div class="mt-1">
                                                <strong>Peringkat Prioritas AHP:</strong> 
                                                <span class="badge bg-dark">Peringkat #{{ $report->wastePoint->rank }}</span>
                                                (Skor: {{ number_format($report->wastePoint->total_score, 4) }})
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Detail Laporan -->
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <h6 class="fw-bold border-bottom pb-2">Informasi Lokasi & Aduan</h6>
                                    <dl class="row small mb-0">
                                        <dt class="col-4 text-muted">Pelapor</dt>
                                        <dd class="col-8">{{ $report->reporter_name }}</dd>

                                        <dt class="col-4 text-muted">Kecamatan</dt>
                                        <dd class="col-8">{{ $report->subdistrict }}</dd>

                                        <dt class="col-4 text-muted">Desa/Kelurahan</dt>
                                        <dd class="col-8">{{ $report->village }}</dd>

                                        <dt class="col-4 text-muted">Patokan</dt>
                                        <dd class="col-8">{{ $report->address_detail }}</dd>

                                        @if($report->latitude && $report->longitude)
                                            <dt class="col-4 text-muted">Koordinat</dt>
                                            <dd class="col-8">
                                                <a href="https://www.google.com/maps?q={{ $report->latitude }},{{ $report->longitude }}" target="_blank" class="text-decoration-none font-monospace small">
                                                    <i class="bi bi-geo-alt-fill text-danger me-1"></i>{{ $report->latitude }}, {{ $report->longitude }}
                                                    <i class="bi bi-box-arrow-up-right ms-1"></i>
                                                </a>
                                            </dd>
                                        @endif

                                        <dt class="col-4 text-muted">Keterangan</dt>
                                        <dd class="col-8">{{ $report->description }}</dd>
                                    </dl>
                                </div>

                                <div class="col-md-6">
                                    <h6 class="fw-bold border-bottom pb-2">Foto Bukti Aduan Warga</h6>
                                    @php
                                        $allPhotos = $report->all_photos;
                                    @endphp
                                    @if(count($allPhotos) > 1)
                                        <div class="row g-2 mb-3">
                                            @foreach($allPhotos as $idx => $p)
                                                <div class="col-4">
                                                    <a href="{{ asset('storage/' . $p) }}" target="_blank" class="d-block border rounded p-1 bg-light text-center h-100 shadow-sm text-decoration-none">
                                                        <img src="{{ asset('storage/' . $p) }}" 
                                                             alt="Bukti Aduan {{ $idx + 1 }}" 
                                                             class="img-fluid rounded" 
                                                             style="height: 100px; width: 100%; object-fit: cover;">
                                                        <small class="text-muted d-block mt-1" style="font-size: 10px;">Foto {{ $idx + 1 }}</small>
                                                    </a>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="border rounded p-1 text-center bg-light mb-3">
                                            <a href="{{ asset('storage/' . ($allPhotos[0] ?? $report->photo_path)) }}" target="_blank">
                                                <img src="{{ asset('storage/' . ($allPhotos[0] ?? $report->photo_path)) }}" 
                                                     alt="Foto Bukti Sampah" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 220px; object-fit: cover;">
                                            </a>
                                        </div>
                                    @endif

                                    @if($report->resolution_photo || ($report->wastePoint && $report->wastePoint->status === 'selesai'))
                                        <h6 class="fw-bold border-bottom pb-2 text-success"><i class="bi bi-check2-circle me-1"></i> Foto Bukti Penyelesaian</h6>
                                        <div class="border rounded p-1 text-center bg-success-subtle">
                                            @php
                                                $resPhoto = $report->resolution_photo;
                                            @endphp
                                            @if($resPhoto)
                                                <img src="{{ asset('storage/' . $resPhoto) }}" 
                                                     alt="Foto Penyelesaian" 
                                                     class="img-fluid rounded" 
                                                     style="max-height: 220px; object-fit: cover;">
                                            @else
                                                <p class="text-success small mb-0 p-3">Penanganan telah diselesaikan oleh tim kebersihan DLH Brebes.</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm border-0 text-center p-5">
                        <i class="bi bi-file-earmark-x fs-1 text-muted mb-3"></i>
                        <h5 class="fw-bold">Nomor Tiket Tidak Ditemukan</h5>
                        <p class="text-muted small mb-3">Tidak ditemukan data laporan dengan kode tiket <code>{{ $ticket }}</code>. Pastikan kode tiket yang dimasukkan sudah benar.</p>
                        <div>
                            <a href="{{ route('report.create') }}" class="btn btn-outline-success btn-sm">Buat Laporan Baru</a>
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
