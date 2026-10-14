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
                'description' => 'Estimasi timbulan tumpukan sampah liar berdasarkan luas sebaran dan ketinggian timbunan.',
                'weight' => 0.2771,
                'is_active' => true,
            ],
            [
                'code' => 'C2',
                'name' => 'Dampak Lingkungan',
                'description' => 'Tingkat ancaman terhadap bau, pencemaran saluran irigasi/air, dan kedekatan dengan fasilitas umum.',
                'weight' => 0.4658,
                'is_active' => true,
            ],
            [
                'code' => 'C3',
                'name' => 'Frekuensi Laporan',
                'description' => 'Jumlah akumulasi aduan tiket laporan dari masyarakat pada titik lokasi sampah yang sama.',
                'weight' => 0.0960,
                'is_active' => true,
            ],
            [
                'code' => 'C4',
                'name' => 'Lama Belum Ditangani',
                'description' => 'Durasi waktu sejak pertama kali laporan diverifikasi hingga tanggal evaluasi saat ini.',
                'weight' => 0.1611,
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

        // Matriks perbandingan simulasi awal terverifikasi (CR <= 0.10)
        $matrix = [
            'C1' => ['C1' => 1.0000, 'C2' => 0.5000, 'C3' => 3.0000, 'C4' => 2.0000],
            'C2' => ['C1' => 2.0000, 'C2' => 1.0000, 'C3' => 4.0000, 'C4' => 3.0000],
            'C3' => ['C1' => 0.3333, 'C2' => 0.2500, 'C3' => 1.0000, 'C4' => 0.5000],
            'C4' => ['C1' => 0.5000, 'C2' => 0.3333, 'C3' => 2.0000, 'C4' => 1.0000],
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
