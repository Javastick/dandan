@extends('layouts.app')

@section('title', 'Sistem Pelaporan Sampah Liar - Kabupaten Brebes')

@section('content')
<!-- Hero Section -->
<div class="bg-success text-white py-5">
    <div class="container py-3">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <span class="badge bg-white text-success fw-semibold mb-3 px-3 py-2">
                    <i class="bi bi-mortarboard-fill me-1"></i> Prototipe Aplikasi Skripsi Informatika
                </span>
                <h1 class="display-5 fw-bold mb-3">
                    Laporkan Sampah Liar di<br>Kabupaten Brebes
                </h1>
                <p class="lead opacity-90 mb-4">
                    Bantu DLH Brebes menentukan prioritas penanganan menggunakan metode AHP-WSM. Setiap laporan Anda membantu mengalokasikan armada kebersihan lebih efisien.
                </p>
                <div class="d-flex gap-3 flex-wrap">
                    <a href="{{ route('report.create') }}" class="btn btn-warning btn-lg fw-bold px-4 shadow">
                        <i class="bi bi-megaphone-fill me-2"></i>Buat Laporan Sekarang
                    </a>
                    <a href="{{ route('report.status') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="bi bi-search me-1"></i>Lacak Status Tiket
                    </a>
                </div>
            </div>
            <div class="col-lg-5 d-none d-lg-flex justify-content-center mt-4 mt-lg-0">
                <div class="bg-white bg-opacity-10 rounded-4 p-4 text-center border border-white border-opacity-25" style="max-width: 300px;">
                    <i class="bi bi-trash2-fill display-1 opacity-75"></i>
                    <p class="mt-3 mb-0 small opacity-75">Sistem Penentuan Prioritas Penanganan Sampah Liar berbasis Analytic Hierarchy Process</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Alur Cara Kerja -->
<div class="py-5 bg-white">
    <div class="container">
        <h2 class="text-center fw-bold mb-5">Bagaimana Cara Kerjanya?</h2>
        <div class="row g-4 text-center">
            <div class="col-md-3">
                <div class="card border-0 bg-success-subtle h-100 p-3">
                    <div class="p-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex p-3 mb-3"><i class="bi bi-megaphone-fill fs-3"></i></div>
                        <h5 class="fw-bold">1. Lapor</h5>
                        <p class="text-muted small mb-0">Warga mengirim foto dan lokasi tumpukan sampah liar melalui formulir publik tanpa perlu membuat akun.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-warning-subtle h-100 p-3">
                    <div class="p-3">
                        <div class="bg-warning text-dark rounded-circle d-inline-flex p-3 mb-3"><i class="bi bi-shield-check fs-3"></i></div>
                        <h5 class="fw-bold">2. Verifikasi</h5>
                        <p class="text-muted small mb-0">Admin DLH memverifikasi laporan, meninjau foto bukti, dan menilai setiap kriteria prioritas.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-primary-subtle h-100 p-3">
                    <div class="p-3">
                        <div class="bg-primary text-white rounded-circle d-inline-flex p-3 mb-3"><i class="bi bi-calculator fs-3"></i></div>
                        <h5 class="fw-bold">3. Prioritas AHP</h5>
                        <p class="text-muted small mb-0">Sistem menghitung skor prioritas menggunakan metode AHP (bobot kriteria) + WSM (skor alternatif).</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 bg-secondary-subtle h-100 p-3">
                    <div class="p-3">
                        <div class="bg-secondary text-white rounded-circle d-inline-flex p-3 mb-3"><i class="bi bi-truck fs-3"></i></div>
                        <h5 class="fw-bold">4. Penanganan</h5>
                        <p class="text-muted small mb-0">Armada kebersihan ditugaskan berdasarkan daftar prioritas. Warga dapat memantau progres via kode tiket.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CTA Cek Tiket -->
<div class="bg-light py-5 border-top">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-7 text-center">
                <h4 class="fw-bold mb-3">Sudah Punya Kode Tiket?</h4>
                <p class="text-muted mb-4">Masukkan kode tiket yang diterima saat mengirim laporan untuk melihat tahapan penanganan terkini.</p>
                <form action="{{ route('report.status') }}" method="GET" class="d-flex justify-content-center gap-2">
                    <input type="text" name="ticket" class="form-control form-control-lg font-monospace text-uppercase" style="max-width: 360px;" placeholder="Contoh: LPR-261012-ABCDE" required>
                    <button type="submit" class="btn btn-success btn-lg px-4 fw-semibold">Lacak</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
