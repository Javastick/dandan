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
            'C1' => ['C1' => 1.0, 'C2' => 0.5, 'C3' => 3.0, 'C4' => 2.0],
            'C2' => ['C1' => 2.0, 'C2' => 1.0, 'C3' => 4.0, 'C4' => 3.0],
            'C3' => ['C1' => 0.3333, 'C2' => 0.25, 'C3' => 1.0, 'C4' => 0.5],
            'C4' => ['C1' => 0.5, 'C2' => 0.3333, 'C3' => 2.0, 'C4' => 1.0],
        ];

        $result = $service->calculateWeightsFromMatrix($matrix);

        $this->assertTrue($result['is_consistent']);
        $this->assertLessThanOrEqual(0.10, $result['cr']);

        // Uji kedekatan bobot dengan presisi desimal
        $this->assertEqualsWithDelta(0.2771, $result['weights']['C1'], 0.01);
        $this->assertEqualsWithDelta(0.4658, $result['weights']['C2'], 0.01);
        $this->assertEqualsWithDelta(0.0960, $result['weights']['C3'], 0.01);
        $this->assertEqualsWithDelta(0.1611, $result['weights']['C4'], 0.01);

        // Uji lambda_max, CI, dan CR
        $this->assertEqualsWithDelta(4.0315, $result['lambda_max'], 0.02);
        $this->assertEqualsWithDelta(0.0105, $result['ci'], 0.005);
        $this->assertEqualsWithDelta(0.0117, $result['cr'], 0.005);
    }
}
