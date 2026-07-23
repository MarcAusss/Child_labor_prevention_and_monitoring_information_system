<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BirthInformation extends Model
{
    protected $table = 'birth_information';

    protected $fillable = [
        'child_laborer_id',
        'region_id',
        'province_id',
        'locality_id',
        'barangay_id',
        'place_of_birth',
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

    public function getLocationLabelAttribute(): string
    {
        return collect([
            $this->barangay?->name,
            $this->locality?->name,
            $this->province?->name,
            $this->region?->name,
        ])
            ->filter()
            ->implode(', ');
    }
}