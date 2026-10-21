<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
        'assigned_to',
        'resolution_photo',
        'handling_notes',
        'handled_at',
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
            'handled_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
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
