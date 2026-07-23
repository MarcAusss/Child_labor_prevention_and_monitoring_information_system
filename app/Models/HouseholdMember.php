<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HouseholdMember extends Model
{
    protected $fillable = [
        'child_laborer_id',
        'full_name',
        'relationship',
        'sex',
        'birth_date',
        'civil_status',
        'educational_attainment',
        'occupation',
        'monthly_income',
        'duplicate_key',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'monthly_income' => 'decimal:2',
        ];
    }

    public function childLaborer(): BelongsTo
    {
        return $this->belongsTo(
            ChildLaborer::class
        );
    }

    public function getAgeAttribute(): ?int
    {
        return $this->birth_date?->age;
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $birthDate = $attributes['birth_date'] ?? null;

        if ($birthDate instanceof CarbonInterface) {
            $birthDate = $birthDate->format('Y-m-d');
        } elseif ($birthDate) {
            $birthDate = Carbon::parse(
                $birthDate
            )->format('Y-m-d');
        }

        $values = [
            self::normalizeValue(
                $attributes['full_name'] ?? null
            ),

            self::normalizeValue(
                $attributes['relationship'] ?? null
            ),

            $birthDate ?: '',
        ];

        return hash(
            'sha256',
            implode('|', $values)
        );
    }

    private static function normalizeValue(
        mixed $value
    ): string {
        $value = Str::ascii(
            trim((string) $value)
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        ) ?? '';

        return Str::lower($value);
    }
}