<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PointAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'waste_point_id',
        'criterion_id',
        'raw_value',
        'normalized_score',
        'assessed_by',
    ];

    protected function casts(): array
    {
        return [
            'raw_value' => 'integer',
            'normalized_score' => 'decimal:4',
        ];
    }

    public function wastePoint(): BelongsTo
    {
        return $this->belongsTo(WastePoint::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(Criterion::class);
    }

    public function assessedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
