@extends('layouts.admin')

@section('title', 'Matriks Perbandingan AHP - Panel Admin')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h4 class="fw-bold mb-1"><i class="bi bi-diagram-3 text-success me-2"></i>Matriks Perbandingan Berpasangan AHP</h4>
                    <p class="text-muted small mb-0">Isi nilai perbandingan antar kriteria menggunakan skala Saaty (1/9 s.d. 9). Diagonal bernilai 1 (tidak dapat diubah). Syarat konsisten: CR ≤ 0.10.</p>
                </div>
                <a href="{{ route('admin.ahp.rankings') }}" class="btn btn-outline-success btn-sm fw-semibold">
                    <i class="bi bi-sort-numeric-down me-1"></i> Lihat Ranking Prioritas
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Hasil Konsistensi Saat Ini -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold mb-1">Lambda Max (λmax)</div>
                <div class="fw-bold fs-5 font-monospace">{{ number_format($calculation['lambda_max'], 4) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold mb-1">Consistency Index (CI)</div>
                <div class="fw-bold fs-5 font-monospace">{{ number_format($calculation['ci'], 4) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold mb-1">Random Index (RI)</div>
                <div class="fw-bold fs-5 font-monospace">{{ number_format($calculation['ri'], 2) }}</div>
                <div class="text-muted" style="font-size:10px;">n = {{ count($criteria) }}</div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-lg-3">
        <div class="card shadow-sm border-0 h-100 {{ $calculation['is_consistent'] ? 'border-success border-2' : 'border-danger border-2' }}">
            <div class="card-body text-center">
                <div class="text-muted small fw-semibold mb-1">Consistency Ratio (CR)</div>
                <div class="fw-bold fs-5 font-monospace {{ $calculation['is_consistent'] ? 'text-success' : 'text-danger' }}">
                    {{ number_format($calculation['cr'], 4) }}
                </div>
                @if($calculation['is_consistent'])
                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>KONSISTEN</span>
                @else
                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>TIDAK KONSISTEN</span>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Bobot Prioritas Kriteria Saat Ini -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-bar-chart-line text-primary me-2"></i>Bobot Prioritas Kriteria Aktif (w<sub>j</sub>)
        @if($calculation['is_consistent'])
            <span class="badge bg-success ms-2">CR = {{ number_format($calculation['cr'], 4) }} ≤ 0.10 — Valid</span>
        @else
            <span class="badge bg-danger ms-2">CR = {{ number_format($calculation['cr'], 4) }} > 0.10 — Tidak Valid</span>
        @endif
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 text-center small">
                <thead class="table-light">
                    <tr>
                        @foreach($criteria as $c)
                            <th class="py-2">{{ $c->code }}: {{ $c->name }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        @foreach($criteria as $c)
                            @php $w = $calculation['weights'][$c->code] ?? 0; @endphp
                            <td class="py-2">
                                <div class="fw-bold font-monospace text-success fs-6">{{ number_format($w, 4) }}</div>
                                <div class="text-muted" style="font-size: 11px;">{{ round($w * 100, 2) }}%</div>
                                <div class="progress mt-1" style="height: 5px;">
                                    <div class="progress-bar bg-success" style="width: {{ round($w * 100, 1) }}%"></div>
                                </div>
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Form Input Matriks Perbandingan Berpasangan -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white py-3 fw-bold">
        <i class="bi bi-table text-dark me-2"></i>Input Matriks Perbandingan Berpasangan (Skala Saaty 1/9 — 9)
    </div>
    <div class="card-body p-4">
        <div class="alert alert-info small py-2 px-3 mb-4">
            <i class="bi bi-lightbulb me-1"></i>
            <strong>Petunjuk Pengisian:</strong> Isi hanya sel bagian kanan atas dari diagonal (segitiga atas). Nilai kebalikan ($1/a_{jk}$) dihitung otomatis oleh sistem. Contoh: Jika C2 dianggap 3× lebih penting dari C1, isi sel baris C1/kolom C2 dengan nilai <code>3</code>.
        </div>

        <form action="{{ route('admin.ahp.update_matrix') }}" method="POST">
            @csrf
            <div class="table-responsive">
                <table class="table table-bordered text-center align-middle small" style="min-width: 600px;">
                    <thead class="table-dark">
                        <tr>
                            <th class="py-3">Kriteria</th>
                            @foreach($criteria as $c)
                                <th class="py-3">{{ $c->code }}<br><small class="fw-normal opacity-75">{{ $c->name }}</small></th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criteria as $rowCrit)
                            <tr>
                                <th class="table-light text-start ps-3">
                                    {{ $rowCrit->code }}<br>
                                    <small class="fw-normal text-muted">{{ $rowCrit->name }}</small>
                                </th>
                                @foreach($criteria as $colCrit)
                                    @php
                                        $currentVal = $matrix[$rowCrit->code][$colCrit->code] ?? 1.0;
                                        $isSelf = $rowCrit->code === $colCrit->code;
                                        $isLower = $rowCrit->id > $colCrit->id;
                                    @endphp
                                    <td class="{{ $isSelf ? 'table-secondary' : ($isLower ? 'table-light' : '') }}">
                                        @if($isSelf)
                                            <span class="fw-bold text-secondary">1</span>
                                            <input type="hidden" name="matrix[{{ $rowCrit->code }}][{{ $colCrit->code }}]" value="1">
                                        @elseif($isLower)
                                            <span class="text-muted font-monospace" style="font-size: 11px;">
                                                (1/{{ number_format($matrix[$colCrit->code][$rowCrit->code] ?? 1, 4) }})
                                            </span>
                                        @else
                                            <select name="matrix[{{ $rowCrit->code }}][{{ $colCrit->code }}]" class="form-select form-select-sm text-center font-monospace" style="min-width: 110px;">
                                                <option value="0.1111" {{ abs($currentVal - 0.1111) < 0.001 ? 'selected' : '' }}>1/9 = 0.111</option>
                                                <option value="0.1250" {{ abs($currentVal - 0.125) < 0.001 ? 'selected' : '' }}>1/8 = 0.125</option>
                                                <option value="0.1429" {{ abs($currentVal - 0.1429) < 0.001 ? 'selected' : '' }}>1/7 = 0.143</option>
                                                <option value="0.1667" {{ abs($currentVal - 0.1667) < 0.001 ? 'selected' : '' }}>1/6 = 0.167</option>
                                                <option value="0.2000" {{ abs($currentVal - 0.2) < 0.001 ? 'selected' : '' }}>1/5 = 0.200</option>
                                                <option value="0.2500" {{ abs($currentVal - 0.25) < 0.001 ? 'selected' : '' }}>1/4 = 0.250</option>
                                                <option value="0.3333" {{ abs($currentVal - 0.3333) < 0.001 ? 'selected' : '' }}>1/3 = 0.333</option>
                                                <option value="0.5000" {{ abs($currentVal - 0.5) < 0.001 ? 'selected' : '' }}>1/2 = 0.500</option>
                                                <option value="1.0000" {{ abs($currentVal - 1.0) < 0.001 ? 'selected' : '' }}>1 = Sama Penting</option>
                                                <option value="2.0000" {{ abs($currentVal - 2.0) < 0.001 ? 'selected' : '' }}>2</option>
                                                <option value="3.0000" {{ abs($currentVal - 3.0) < 0.001 ? 'selected' : '' }}>3 = Sedikit Lebih Penting</option>
                                                <option value="4.0000" {{ abs($currentVal - 4.0) < 0.001 ? 'selected' : '' }}>4</option>
                                                <option value="5.0000" {{ abs($currentVal - 5.0) < 0.001 ? 'selected' : '' }}>5 = Jelas Lebih Penting</option>
                                                <option value="6.0000" {{ abs($currentVal - 6.0) < 0.001 ? 'selected' : '' }}>6</option>
                                                <option value="7.0000" {{ abs($currentVal - 7.0) < 0.001 ? 'selected' : '' }}>7 = Sangat Lebih Penting</option>
                                                <option value="8.0000" {{ abs($currentVal - 8.0) < 0.001 ? 'selected' : '' }}>8</option>
                                                <option value="9.0000" {{ abs($currentVal - 9.0) < 0.001 ? 'selected' : '' }}>9 = Mutlak Lebih Penting</option>
                                            </select>
                                        @endif
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-end mt-3">
                <button type="submit" class="btn btn-success px-5 py-2 fw-semibold">
                    <i class="bi bi-calculator me-1"></i> Hitung CR & Simpan Bobot AHP
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
