<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CriterionComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'criterion_source_id',
        'criterion_target_id',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:4',
        ];
    }

    public function sourceCriterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criterion_source_id');
    }

    public function targetCriterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class, 'criterion_target_id');
    }
}
