<?php

namespace Tests\Unit;

use App\Services\AhpService;
use PHPUnit\Framework\TestCase;

class AhpServiceTest extends TestCase
{
    public function test_ahp_calculation_matches_numerical_simulation(): void
    {
        $service = new AhpService();

        $matrix = [
            'C1' => ['C1' => 1.0000, 'C2' => 2.0000, 'C3' => 3.0000, 'C4' => 4.0000],
            'C2' => ['C1' => 0.5000, 'C2' => 1.0000, 'C3' => 2.0000, 'C4' => 3.0000],
            'C3' => ['C1' => 0.3333, 'C2' => 0.5000, 'C3' => 1.0000, 'C4' => 2.0000],
            'C4' => ['C1' => 0.2500, 'C2' => 0.3333, 'C3' => 0.5000, 'C4' => 1.0000],
        ];

        $result = $service->calculateWeightsFromMatrix($matrix);

        $this->assertTrue($result['is_consistent']);
        $this->assertLessThanOrEqual(0.10, $result['cr']);

        // Uji kedekatan bobot kriteria skripsi
        $this->assertEqualsWithDelta(0.4658, $result['weights']['C1'], 0.02);
        $this->assertEqualsWithDelta(0.2771, $result['weights']['C2'], 0.02);
        $this->assertEqualsWithDelta(0.1611, $result['weights']['C3'], 0.02);
        $this->assertEqualsWithDelta(0.0960, $result['weights']['C4'], 0.02);
    }
}
