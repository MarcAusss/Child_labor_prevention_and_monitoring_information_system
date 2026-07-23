<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Locality extends Model
{
    public const LEVEL_CITY = 'City';

    public const LEVEL_MUNICIPALITY = 'Municipality';

    public const LEVEL_SUB_MUNICIPALITY = 'Sub-Municipality';

    protected $fillable = [
        'region_id',
        'province_id',
        'parent_id',
        'psgc_code',
        'correspondence_code',
        'name',
        'geographic_level',
        'old_names',
        'city_class',
        'income_classification',
        'status',
        'population',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'population' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(
            Locality::class,
            'parent_id'
        );
    }

    public function children(): HasMany
    {
        return $this->hasMany(
            Locality::class,
            'parent_id'
        );
    }

    public function barangays(): HasMany
    {
        return $this->hasMany(Barangay::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeTopLevel(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->geographic_level})";
    }
}