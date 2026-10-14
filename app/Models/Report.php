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
        'description',
        'photo_path',
        'resolution_photo',
        'status',
        'rejection_reason',
        'verified_at',
        'verified_by',
    ];

    protected function casts(): array
    {
        return [
            'verified_at' => 'datetime',
        ];
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
