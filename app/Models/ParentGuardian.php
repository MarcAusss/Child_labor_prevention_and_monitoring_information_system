<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentGuardian extends Model
{
    protected $fillable = [
        'child_laborer_id',
        'full_name',
        'relationship',
        'contact_number',
        'occupation',
        'educational_attainment',
        'monthly_income',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'monthly_income' => 'decimal:2',
            'is_primary' => 'boolean',
        ];
    }

    public function childLaborer(): BelongsTo
    {
        return $this->belongsTo(
            ChildLaborer::class
        );
    }

    public function scopePrimary(
        Builder $query
    ): Builder {
        return $query->where(
            'is_primary',
            true
        );
    }
}