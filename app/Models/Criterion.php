<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Criterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'weight',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'weight' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function sourceComparisons(): HasMany
    {
        return $this->hasMany(CriterionComparison::class, 'criterion_source_id');
    }

    public function targetComparisons(): HasMany
    {
        return $this->hasMany(CriterionComparison::class, 'criterion_target_id');
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(PointAssessment::class);
    }
}
