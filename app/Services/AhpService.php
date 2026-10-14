<?php

namespace App\Services;

use App\Models\Criterion;
use App\Models\CriterionComparison;
use App\Models\PointAssessment;
use App\Models\WastePoint;
use Illuminate\Support\Facades\DB;

class AhpService
{
    /**
     * Nilai Random Index (RI) Saaty untuk n = 1 s.d. 10.
     */
    protected array $randomIndices = [
        1 => 0.00,
        2 => 0.00,
        3 => 0.58,
        4 => 0.90,
        5 => 1.12,
        6 => 1.24,
        7 => 1.32,
        8 => 1.41,
        9 => 1.45,
        10 => 1.49,
    ];

    /**
     * Hitung bobot kriteria, lambda max, CI, dan CR dari matriks perbandingan berpasangan.
     *
     * @param array $matrix Matriks berpasangan [ 'C1' => ['C1' => 1, 'C2' => 0.5, ...], ... ]
     * @return array
     */
    public function calculateWeightsFromMatrix(array $matrix): array
    {
        $codes = array_keys($matrix);
        $n = count($codes);

        if ($n === 0) {
            return [
                'weights' => [],
                'lambda_max' => 0,
                'ci' => 0,
                'cr' => 0,
                'is_consistent' => false,
                'column_sums' => [],
                'normalized_matrix' => [],
            ];
        }

        // 1. Hitung jumlah setiap kolom (S_j)
        $columnSums = [];
        foreach ($codes as $col) {
            $sum = 0.0;
            foreach ($codes as $row) {
                $sum += (float) ($matrix[$row][$col] ?? 1.0);
            }
            $columnSums[$col] = $sum > 0 ? $sum : 1.0;
        }

        // 2. Normalisasi matriks dan hitung bobot rata-rata baris (w_i)
        $normalizedMatrix = [];
        $weights = [];
        foreach ($codes as $row) {
            $rowSum = 0.0;
            $normalizedMatrix[$row] = [];
            foreach ($codes as $col) {
                $val = (float) ($matrix[$row][$col] ?? 1.0);
                $normVal = $val / $columnSums[$col];
                $normalizedMatrix[$row][$col] = round($normVal, 4);
                $rowSum += $normVal;
            }
            $weights[$row] = round($rowSum / $n, 4);
        }

        // Pastikan total bobot tepat 1.0000 jika ada selisih pembulatan
        $totalWeight = array_sum($weights);
        if ($totalWeight > 0 && abs($totalWeight - 1.0) > 0.0001) {
            $diff = 1.0 - $totalWeight;
            $firstKey = $codes[0];
            $weights[$firstKey] = round($weights[$firstKey] + $diff, 4);
        }

        // 3. Uji Konsistensi (v = A * w, t_i = v_i / w_i, lambda_max, CI, CR)
        $v = [];
        $t = [];
        foreach ($codes as $row) {
            $sumV = 0.0;
            foreach ($codes as $col) {
                $sumV += ((float) ($matrix[$row][$col] ?? 1.0)) * $weights[$col];
            }
            $v[$row] = $sumV;
            $t[$row] = $weights[$row] > 0 ? ($sumV / $weights[$row]) : 0;
        }

        $lambdaMax = round(array_sum($t) / $n, 4);
        $ci = $n > 1 ? round(($lambdaMax - $n) / ($n - 1), 4) : 0.0;
        $ri = $this->randomIndices[$n] ?? 1.49;
        $cr = ($ri > 0) ? round($ci / $ri, 4) : 0.0;
        $isConsistent = ($cr <= 0.10);

        return [
            'weights' => $weights,
            'lambda_max' => $lambdaMax,
            'ci' => $ci,
            'cr' => $cr,
            'ri' => $ri,
            'is_consistent' => $isConsistent,
            'column_sums' => $columnSums,
            'normalized_matrix' => $normalizedMatrix,
        ];
    }

    /**
     * Ambil matriks perbandingan aktif dari database.
     */
    public function getMatrixFromDatabase(): array
    {
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $codes = $criteria->pluck('code')->toArray();
        $critMap = $criteria->keyBy('id');
        $codeToId = $criteria->pluck('id', 'code')->toArray();

        $comparisons = CriterionComparison::all();
        $matrix = [];

        foreach ($codes as $r) {
            $matrix[$r] = [];
            foreach ($codes as $c) {
                if ($r === $c) {
                    $matrix[$r][$c] = 1.0;
                } else {
                    $matrix[$r][$c] = 1.0;
                }
            }
        }

        foreach ($comparisons as $comp) {
            $src = $critMap[$comp->criterion_source_id]->code ?? null;
            $tgt = $critMap[$comp->criterion_target_id]->code ?? null;
            if ($src && $tgt && in_array($src, $codes) && in_array($tgt, $codes)) {
                $matrix[$src][$tgt] = (float) $comp->value;
            }
        }

        return $matrix;
    }

    /**
     * Simpan matriks baru dan perbarui bobot jika konsisten.
     */
    public function saveMatrixAndWeights(array $inputMatrix): array
    {
        $calculation = $this->calculateWeightsFromMatrix($inputMatrix);
        $criteria = Criterion::where('is_active', true)->get()->keyBy('code');

        DB::transaction(function () use ($inputMatrix, $calculation, $criteria) {
            foreach ($inputMatrix as $sourceCode => $targets) {
                if (!isset($criteria[$sourceCode])) continue;
                $sourceId = $criteria[$sourceCode]->id;

                foreach ($targets as $targetCode => $val) {
                    if (!isset($criteria[$targetCode])) continue;
                    $targetId = $criteria[$targetCode]->id;
                    $numVal = (float) $val;

                    CriterionComparison::updateOrCreate(
                        [
                            'criterion_source_id' => $sourceId,
                            'criterion_target_id' => $targetId,
                        ],
                        [
                            'value' => $numVal,
                        ]
                    );

                    // Pastikan elemen kebalikan bernilai 1 / val jika val > 0
                    if ($sourceId !== $targetId && $numVal > 0) {
                        CriterionComparison::updateOrCreate(
                            [
                                'criterion_source_id' => $targetId,
                                'criterion_target_id' => $sourceId,
                            ],
                            [
                                'value' => round(1.0 / $numVal, 4),
                            ]
                        );
                    }
                }
            }

            // Jika konsisten (CR <= 0.10), update bobot kriteria di tabel criteria
            if ($calculation['is_consistent']) {
                foreach ($calculation['weights'] as $code => $weight) {
                    if (isset($criteria[$code])) {
                        $criteria[$code]->update(['weight' => $weight]);
                    }
                }
            }
        });

        return $calculation;
    }

    /**
     * Hitung skor akhir WSM dan pemeringkatan untuk seluruh titik sampah aktif.
     */
    public function calculateAndRankWastePoints(): int
    {
        $criteria = Criterion::where('is_active', true)->orderBy('code')->get();
        $weights = $criteria->pluck('weight', 'code')->toArray();
        $criteriaIds = $criteria->pluck('id', 'code')->toArray();

        // Ambil titik sampah yang belum berstatus 'selesai'
        $wastePoints = WastePoint::whereIn('status', ['terverifikasi', 'diproses'])
            ->with('assessments')
            ->get();

        $scoredPoints = [];

        foreach ($wastePoints as $point) {
            $assessmentsMap = $point->assessments->keyBy('criterion_id');
            $totalScore = 0.0;

            foreach ($criteria as $criterion) {
                $assessment = $assessmentsMap[$criterion->id] ?? null;
                $rawValue = $assessment ? $assessment->raw_value : 1; // Default 1 jika belum dinilai
                $normalizedScore = round($rawValue / 5.0, 4);

                // Perbarui nilai normalisasi jika tersimpan
                if ($assessment && (float) $assessment->normalized_score !== $normalizedScore) {
                    $assessment->update(['normalized_score' => $normalizedScore]);
                }

                $totalScore += ((float) $criterion->weight) * $normalizedScore;
            }

            $scoredPoints[] = [
                'model' => $point,
                'score' => round($totalScore, 4),
            ];
        }

        // Urutkan menurun berdasarkan skor total
        usort($scoredPoints, function ($a, $b) {
            return $b['score'] <=> $a['score'];
        });

        // Simpan peringkat dan snapshot bobot ke database
        DB::transaction(function () use ($scoredPoints, $weights) {
            $rank = 1;
            foreach ($scoredPoints as $item) {
                /** @var WastePoint $point */
                $point = $item['model'];
                $point->update([
                    'total_score' => $item['score'],
                    'rank' => $rank++,
                    'weights_snapshot' => $weights,
                    'calculated_at' => now(),
                ]);
            }
        });

        return count($scoredPoints);
    }
}
