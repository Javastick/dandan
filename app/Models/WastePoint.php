<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WastePoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'point_code',
        'name',
        'subdistrict',
        'village',
        'address_detail',
        'latitude',
        'longitude',
        'status',
        'total_score',
        'rank',
        'weights_snapshot',
        'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'decimal:4',
            'rank' => 'integer',
            'weights_snapshot' => 'array',
            'calculated_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(PointAssessment::class);
    }
}
