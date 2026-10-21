<?php

namespace Database\Seeders;

use App\Models\Criterion;
use App\Models\CriterionComparison;
use Illuminate\Database\Seeder;

class CriterionSeeder extends Seeder
{
    public function run(): void
    {
        $criteriaData = [
            [
                'code' => 'C1',
                'name' => 'Volume Sampah',
                'description' => 'Estimasi timbulan atau besaran tumpukan sampah liar berdasarkan luasan dan ketebalan timbunan.',
                'weight' => 0.4658,
                'is_active' => true,
            ],
            [
                'code' => 'C2',
                'name' => 'Jarak dari Pemukiman',
                'description' => 'Kedekatan lokasi timbunan sampah dengan kawasan permukiman penduduk atau aktivitas warga.',
                'weight' => 0.2771,
                'is_active' => true,
            ],
            [
                'code' => 'C3',
                'name' => 'Jarak ke Sungai',
                'description' => 'Kedekatan lokasi sampah terhadap sempadan sungai, saluran irigasi, atau badan air aktif.',
                'weight' => 0.1611,
                'is_active' => true,
            ],
            [
                'code' => 'C4',
                'name' => 'Frekuensi Laporan Masyarakat',
                'description' => 'Jumlah akumulasi tiket aduan masyarakat yang mengonfirmasi keberadaan titik sampah tersebut.',
                'weight' => 0.0960,
                'is_active' => true,
            ],
        ];

        $criteriaMap = [];
        foreach ($criteriaData as $item) {
            $criterion = Criterion::updateOrCreate(
                ['code' => $item['code']],
                $item
            );
            $criteriaMap[$item['code']] = $criterion->id;
        }

        // Matriks perbandingan berpasangan konsisten (CR <= 0.10)
        // C1 (Volume) > C2 (Jarak Pemukiman) > C3 (Jarak Sungai) > C4 (Frekuensi Laporan)
        $matrix = [
            'C1' => ['C1' => 1.0000, 'C2' => 2.0000, 'C3' => 3.0000, 'C4' => 4.0000],
            'C2' => ['C1' => 0.5000, 'C2' => 1.0000, 'C3' => 2.0000, 'C4' => 3.0000],
            'C3' => ['C1' => 0.3333, 'C2' => 0.5000, 'C3' => 1.0000, 'C4' => 2.0000],
            'C4' => ['C1' => 0.2500, 'C2' => 0.3333, 'C3' => 0.5000, 'C4' => 1.0000],
        ];

        foreach ($matrix as $sourceCode => $targets) {
            foreach ($targets as $targetCode => $value) {
                CriterionComparison::updateOrCreate(
                    [
                        'criterion_source_id' => $criteriaMap[$sourceCode],
                        'criterion_target_id' => $criteriaMap[$targetCode],
                    ],
                    [
                        'value' => $value,
                    ]
                );
            }
        }
    }
}
