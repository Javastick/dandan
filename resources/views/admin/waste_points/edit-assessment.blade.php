@extends('layouts.admin')

@section('title', 'Penilaian Kriteria - ' . $wastePoint->point_code)

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <a href="{{ route('admin.waste_points.show', $wastePoint->id) }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Batal / Kembali ke Detail Titik
        </a>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-sliders text-primary me-2"></i>Form Penilaian Kriteria Titik Sampah</h5>
                    <small class="text-muted">Titik Lokasi: <strong>{{ $wastePoint->point_code }}</strong> - {{ $wastePoint->name }} ({{ $wastePoint->village }}, Kec. {{ $wastePoint->subdistrict }})</small>
                </div>
                <span class="badge bg-info-subtle text-info border border-info px-3 py-2">
                    Skala Diskrit Ordinal 1 s.d. 5
                </span>
            </div>

            <div class="card-body p-4">
                @if($spatialAnalysis)
                    <div class="alert alert-success py-2 px-3 small mb-4">
                        <div class="fw-semibold text-success mb-1"><i class="bi bi-compass-fill me-1"></i>Rekomendasi Analisis Geospasial (SIG Brebes):</div>
                        <ul class="mb-0 ps-3">
                            <li><strong>Jarak ke Pemukiman:</strong> {{ $spatialAnalysis['settlement']['formatted'] }} (Terdekat: {{ $spatialAnalysis['settlement']['nearest_name'] }}) &rarr; <span class="badge bg-success">Rekomendasi Skala: {{ $spatialAnalysis['settlement']['scale'] }}</span></li>
                            <li><strong>Jarak ke Sungai:</strong> {{ $spatialAnalysis['river']['formatted'] }} (Terdekat: {{ $spatialAnalysis['river']['nearest_name'] }}) &rarr; <span class="badge bg-success">Rekomendasi Skala: {{ $spatialAnalysis['river']['scale'] }}</span></li>
                        </ul>
                    </div>
                @endif

                <div class="alert alert-warning py-2 px-3 small mb-4">
                    <i class="bi bi-info-circle-fill me-1"></i> 
                    <strong>Catatan Akademik:</strong> Penilaian skala 1–5 di bawah ini merupakan instrumen operasional penentuan prioritas. Setelah disimpan, sistem akan menormalisasi nilai ($S_{ij} = x_{ij} / 5$) dan menghitung skor akhir preferensi AHP ($V_i = \sum w_j \cdot S_{ij}$) secara otomatis.
                </div>

                <form action="{{ route('admin.waste_points.update_assessment', $wastePoint->id) }}" method="POST">
                    @csrf

                    @foreach($criteria as $c)
                        @php
                            $currentRating = $assessmentsMap[$c->id]->raw_value ?? 1;
                            $rubric = $rubrics[$c->code] ?? [];
                        @endphp

                        <div class="card bg-light border-0 mb-4 p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-success mb-0">
                                    <span class="badge bg-success me-1">{{ $c->code }}</span> {{ $c->name }}
                                </h6>
                                <span class="badge bg-dark">Bobot AHP: {{ number_format($c->weight, 4) }} ({{ round($c->weight * 100, 2) }}%)</span>
                            </div>
                            <p class="text-muted small mb-3">{{ $c->description }}</p>

                            <div class="table-responsive bg-white rounded border p-2 mb-3">
                                <table class="table table-sm table-hover mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 80px;" class="text-center">Pilih</th>
                                            <th style="width: 70px;" class="text-center">Skala</th>
                                            <th>Indikator / Deskripsi Operasional Demonstrasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @for($val = 1; $val <= 5; $val++)
                                            <tr class="{{ $currentRating == $val ? 'table-success' : '' }}">
                                                <td class="text-center align-middle">
                                                    <input class="form-check-input" 
                                                           type="radio" 
                                                           name="rating_{{ $c->code }}" 
                                                           id="rating_{{ $c->code }}_{{ $val }}" 
                                                           value="{{ $val }}" 
                                                           {{ $currentRating == $val ? 'checked' : '' }} 
                                                           required>
                                                </td>
                                                <td class="text-center align-middle">
                                                    <label class="badge {{ $val >= 4 ? 'bg-danger' : ($val >= 3 ? 'bg-warning text-dark' : 'bg-secondary') }} px-2 py-1 fs-6 mb-0" 
                                                           for="rating_{{ $c->code }}_{{ $val }}">
                                                        {{ $val }}
                                                    </label>
                                                </td>
                                                <td class="align-middle">
                                                    <label class="form-check-label d-block text-dark fw-semibold" for="rating_{{ $c->code }}_{{ $val }}">
                                                        {{ $rubric[$val] ?? "Tingkat {$val}" }}
                                                    </label>
                                                </td>
                                            </tr>
                                        @endfor
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.waste_points.show', $wastePoint->id) }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-success px-4 py-2 fw-semibold">
                            <i class="bi bi-save me-1"></i> Simpan Penilaian & Hitung Ulang Ranking
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
