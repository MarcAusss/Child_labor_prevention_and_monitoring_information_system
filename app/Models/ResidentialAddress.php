<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResidentialAddress extends Model
{
    protected $fillable = [
        'child_laborer_id',
        'region_id',
        'province_id',
        'locality_id',
        'barangay_id',
        'house_number',
        'street',
        'sitio_purok',
        'postal_code',
        'landmark',
    ];

    public function childLaborer(): BelongsTo
    {
        return $this->belongsTo(ChildLaborer::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function locality(): BelongsTo
    {
        return $this->belongsTo(Locality::class);
    }

    public function barangay(): BelongsTo
    {
        return $this->belongsTo(Barangay::class);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([
            $this->house_number,
            $this->street,
            $this->sitio_purok,
            $this->barangay?->name,
            $this->locality?->name,
            $this->province?->name,
            $this->region?->name,
            $this->postal_code,
        ])
            ->filter()
            ->implode(', ');
    }
}