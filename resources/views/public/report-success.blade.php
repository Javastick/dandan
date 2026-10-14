@extends('layouts.app')

@section('title', 'Laporan Berhasil Terkirim - Kode Tiket')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow-sm border-0 text-center p-4">
                <div class="card-body">
                    <div class="bg-success text-white d-inline-flex p-3 rounded-circle mb-3">
                        <i class="bi bi-check-lg fs-1"></i>
                    </div>

                    <h3 class="fw-bold text-dark mb-2">Laporan Berhasil Dikirim!</h3>
                    <p class="text-muted small mb-4">
                        Terima kasih telah berpartisipasi menjaga kebersihan lingkungan di Kabupaten Brebes. Laporan Anda telah tercatat dan sedang menunggu verifikasi oleh petugas.
                    </p>

                    <!-- Kode Tiket Card -->
                    <div class="card bg-success-subtle border border-success p-3 mb-4">
                        <span class="text-success small fw-semibold text-uppercase tracking-wider">Nomor Kode Tiket Pelaporan</span>
                        <div class="display-6 fw-bold text-success font-monospace my-1" id="ticketCode">
                            {{ $report->ticket_code }}
                        </div>
                        <small class="text-muted">Simpan kode tiket ini untuk melacak perkembangan status penanganan sampah.</small>
                    </div>

                    <!-- Ringkasan Singkat -->
                    <div class="card bg-light border-0 text-start p-3 mb-4 small">
                        <div class="row mb-1">
                            <span class="col-4 text-muted">Pelapor:</span>
                            <span class="col-8 fw-semibold">{{ $report->reporter_name }} ({{ $report->reporter_phone }})</span>
                        </div>
                        <div class="row mb-1">
                            <span class="col-4 text-muted">Lokasi:</span>
                            <span class="col-8 fw-semibold">{{ $report->village }}, Kec. {{ $report->subdistrict }}</span>
                        </div>
                        <div class="row mb-1">
                            <span class="col-4 text-muted">Patokan:</span>
                            <span class="col-8">{{ $report->address_detail }}</span>
                        </div>
                        <div class="row">
                            <span class="col-4 text-muted">Waktu Lapor:</span>
                            <span class="col-8">{{ $report->created_at->format('d M Y, H:i') }} WIB</span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        <a href="{{ route('report.status', ['ticket' => $report->ticket_code]) }}" class="btn btn-success fw-semibold">
                            <i class="bi bi-search me-1"></i> Lacak Status Laporan Ini
                        </a>
                        <a href="{{ route('report.create') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-plus-circle me-1"></i> Buat Laporan Baru
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
