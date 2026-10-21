<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'ticket_code',
        'waste_point_id',
        'reporter_name',
        'reporter_phone',
        'subdistrict',
        'village',
        'address_detail',
        'latitude',
        'longitude',
        'description',
        'photo_path',
        'photos',
        'resolution_photo',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'photos' => 'array',
            'verified_at' => 'datetime',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    /**
     * Dapatkan semua foto laporan (termasuk legacy photo_path)
     */
    public function getAllPhotosAttribute(): array
    {
        if (!empty($this->photos) && is_array($this->photos)) {
            return $this->photos;
        }

        return $this->photo_path ? [$this->photo_path] : [];
    }

    public function wastePoint(): BelongsTo
    {
        return $this->belongsTo(WastePoint::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
