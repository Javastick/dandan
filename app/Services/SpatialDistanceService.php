<?php

namespace App\Services;

class SpatialDistanceService
{
    protected string $riversPath;
    protected string $settlementsPath;

    public function __construct()
    {
        $baseGis = (function_exists('app') && app()->has('path.storage'))
            ? storage_path('gis')
            : dirname(__DIR__, 2) . '/storage/gis';

        $this->riversPath = $baseGis . '/brebes_rivers.geojson';
        $this->settlementsPath = $baseGis . '/brebes_settlements.geojson';
    }

    /**
     * Hitung jarak terdekat (meter) dari titik koordinat ke sungai dan permukiman,
     * serta tentukan skala ordinal AHP (1 s.d. 5) secara otomatis.
     */
    public function analyzeLocation(float $latitude, float $longitude): array
    {
        $riverResult = $this->calculateNearestRiver($latitude, $longitude);
        $settlementResult = $this->calculateNearestSettlement($latitude, $longitude);

        return [
            'latitude' => $latitude,
            'longitude' => $longitude,
            'settlement' => [
                'distance_meters' => $settlementResult['distance'],
                'nearest_name' => $settlementResult['name'],
                'subdistrict' => $settlementResult['subdistrict'],
                'scale' => $this->mapSettlementDistanceToScale($settlementResult['distance']),
                'formatted' => $this->formatDistance($settlementResult['distance']),
            ],
            'river' => [
                'distance_meters' => $riverResult['distance'],
                'nearest_name' => $riverResult['name'],
                'type' => $riverResult['type'],
                'scale' => $this->mapRiverDistanceToScale($riverResult['distance']),
                'formatted' => $this->formatDistance($riverResult['distance']),
            ],
        ];
    }

    /**
     * Hitung jarak terdekat ke jaringan garis sungai (LineString)
     */
    public function calculateNearestRiver(float $lat, float $lng): array
    {
        if (!file_exists($this->riversPath)) {
            return ['distance' => 300, 'name' => 'Sungai (Estimasi Default)', 'type' => 'Sungai'];
        }

        $geoJson = json_decode(file_get_contents($this->riversPath), true);
        $minDist = PHP_FLOAT_MAX;
        $nearestRiver = [
            'name' => 'Sungai Tidak Diketahui',
            'type' => 'Sungai',
            'nearest_point' => null,
            'line_coordinates' => [],
        ];

        foreach ($geoJson['features'] ?? [] as $feat) {
            $coords = $feat['geometry']['coordinates'] ?? [];
            $name = $feat['properties']['name'] ?? 'Sungai';
            $type = $feat['properties']['type'] ?? 'Aliran Air';

            $result = $this->distanceToLineStringDetail($lat, $lng, $coords);
            if ($result['distance'] < $minDist) {
                $minDist = $result['distance'];
                $nearestRiver = [
                    'name' => $name,
                    'type' => $type,
                    'nearest_point' => $result['nearest_point'],
                    'line_coordinates' => $coords,
                ];
            }
        }

        return [
            'distance' => round($minDist, 1),
            'name' => $nearestRiver['name'],
            'type' => $nearestRiver['type'],
            'nearest_point' => $nearestRiver['nearest_point'], // [lat, lng]
            'line_coordinates' => $nearestRiver['line_coordinates'],
        ];
    }

    /**
     * Hitung jarak terdekat ke titik kawasan permukiman warga
     */
    public function calculateNearestSettlement(float $lat, float $lng): array
    {
        if (!file_exists($this->settlementsPath)) {
            return [
                'distance' => 150,
                'name' => 'Kawasan Permukiman (Default)',
                'subdistrict' => 'Brebes',
                'coordinates' => [$lat, $lng],
            ];
        }

        $geoJson = json_decode(file_get_contents($this->settlementsPath), true);
        $minDist = PHP_FLOAT_MAX;
        $nearestSettlement = [
            'name' => 'Permukiman',
            'subdistrict' => 'Brebes',
            'coordinates' => null,
        ];

        foreach ($geoJson['features'] ?? [] as $feat) {
            $coord = $feat['geometry']['coordinates'] ?? [0, 0];
            $name = $feat['properties']['name'] ?? 'Desa';
            $subdistrict = $feat['properties']['subdistrict'] ?? '';

            // [lng, lat]
            $dist = $this->haversineGreatCircleDistance($lat, $lng, $coord[1], $coord[0]);
            if ($dist < $minDist) {
                $minDist = $dist;
                $nearestSettlement = [
                    'name' => $name,
                    'subdistrict' => $subdistrict,
                    'coordinates' => [$coord[1], $coord[0]], // [lat, lng]
                ];
            }
        }

        return [
            'distance' => round($minDist, 1),
            'name' => $nearestSettlement['name'],
            'subdistrict' => $nearestSettlement['subdistrict'],
            'coordinates' => $nearestSettlement['coordinates'], // [lat, lng]
        ];
    }

    /**
     * Konversi jarak ke pemukiman ke Skala AHP (1 - 5)
     * Semakin dekat dengan pemukiman, tingkat bahaya & prioritas semakin tinggi (Skala 5)
     */
    public function mapSettlementDistanceToScale(float $distanceMeters): int
    {
        if ($distanceMeters < 50) {
            return 5; // Sangat Dekat (< 50 meter)
        } elseif ($distanceMeters <= 100) {
            return 4; // Dekat (50 - 100 meter)
        } elseif ($distanceMeters <= 250) {
            return 3; // Sedang (100 - 250 meter)
        } elseif ($distanceMeters <= 500) {
            return 2; // Jauh (250 - 500 meter)
        } else {
            return 1; // Sangat Jauh (> 500 meter)
        }
    }

    /**
     * Konversi jarak ke sungai/saluran air ke Skala AHP (1 - 5)
     * Semakin dekat dengan badan air, potensi mencemari irigasi/sungai semakin kritis (Skala 5)
     */
    public function mapRiverDistanceToScale(float $distanceMeters): int
    {
        if ($distanceMeters < 50) {
            return 5; // Sangat Dekat (< 50 meter / bantaran sungai)
        } elseif ($distanceMeters <= 100) {
            return 4; // Dekat (50 - 100 meter)
        } elseif ($distanceMeters <= 250) {
            return 3; // Sedang (100 - 250 meter)
        } elseif ($distanceMeters <= 500) {
            return 2; // Jauh (250 - 500 meter)
        } else {
            return 1; // Sangat Jauh (> 500 meter)
        }
    }

    /**
     * Format tampilan meter / kilometer
     */
    protected function formatDistance(float $meters): string
    {
        if ($meters >= 1000) {
            return number_format($meters / 1000, 2) . ' km';
        }
        return round($meters) . ' meter';
    }

    /**
     * Jarak dari sebuah titik ke Polyline beserta koordinat titik terdekatnya
     */
    protected function distanceToLineStringDetail(float $lat, float $lng, array $coordinates): array
    {
        $min = PHP_FLOAT_MAX;
        $nearestPoint = null;
        $count = count($coordinates);

        if ($count < 2) {
            if ($count === 1) {
                return [
                    'distance' => $this->haversineGreatCircleDistance($lat, $lng, $coordinates[0][1], $coordinates[0][0]),
                    'nearest_point' => [$coordinates[0][1], $coordinates[0][0]],
                ];
            }
            return ['distance' => $min, 'nearest_point' => null];
        }

        for ($i = 0; $i < $count - 1; $i++) {
            $p1 = $coordinates[$i];     // [lng, lat]
            $p2 = $coordinates[$i + 1]; // [lng, lat]

            $seg = $this->distanceToSegmentDetail($lat, $lng, $p1[1], $p1[0], $p2[1], $p2[0]);
            if ($seg['distance'] < $min) {
                $min = $seg['distance'];
                $nearestPoint = $seg['point'];
            }
        }

        return [
            'distance' => $min,
            'nearest_point' => $nearestPoint, // [lat, lng]
        ];
    }

    /**
     * Jarak titik ke ruas garis beserta koordinat titik proyeksi terdekat [lat, lng]
     */
    protected function distanceToSegmentDetail(float $lat, float $lng, float $lat1, float $lng1, float $lat2, float $lng2): array
    {
        $x = $lng;
        $y = $lat;
        $x1 = $lng1;
        $y1 = $lat1;
        $x2 = $lng2;
        $y2 = $lat2;

        $dx = $x2 - $x1;
        $dy = $y2 - $y1;

        if ($dx == 0 && $dy == 0) {
            return [
                'distance' => $this->haversineGreatCircleDistance($lat, $lng, $lat1, $lng1),
                'point' => [$lat1, $lng1],
            ];
        }

        // Proyeksi titik ke ruas garis (parameter t: 0..1)
        $t = (($x - $x1) * $dx + ($y - $y1) * $dy) / ($dx * $dx + $dy * $dy);
        $t = max(0, min(1, $t));

        $projLng = $x1 + $t * $dx;
        $projLat = $y1 + $t * $dy;

        return [
            'distance' => $this->haversineGreatCircleDistance($lat, $lng, $projLat, $projLng),
            'point' => [$projLat, $projLng],
        ];
    }

    /**
     * Rumus Haversine menghitung jarak melingkar bumi (Great-circle distance) dalam satuan meter
     */
    public function haversineGreatCircleDistance(float $latitudeFrom, float $longitudeFrom, float $latitudeTo, float $longitudeTo, float $earthRadius = 6371000): float
    {
        $latFrom = deg2rad($latitudeFrom);
        $lonFrom = deg2rad($longitudeFrom);
        $latTo = deg2rad($latitudeTo);
        $lonTo = deg2rad($longitudeTo);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}
