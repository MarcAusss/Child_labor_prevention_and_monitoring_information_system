<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class HealthInformation extends Model
{
    protected $table = 'health_information';

    protected $fillable = [
        'child_laborer_id',
        'assessment_date',
        'health_condition',
        'has_disability',
        'disability_details',
        'injury_history',
        'treatment_received',
        'health_facility',
        'current_complaints',
        'mental_health_concerns',
        'remarks',
        'is_current',
        'duplicate_key',
    ];

    protected function casts(): array
    {
        return [
            'assessment_date' => 'date',
            'has_disability' => 'boolean',
            'is_current' => 'boolean',
        ];
    }

    public function childLaborer(): BelongsTo
    {
        return $this->belongsTo(
            ChildLaborer::class,
            'child_laborer_id',
            'id'
        );
    }

    public function scopeCurrent(
        Builder $query
    ): Builder {
        return $query->where(
            'is_current',
            true
        );
    }

    public function getConcernIndicatorsAttribute(): array
    {
        $indicators = [];

        if ($this->health_condition) {
            $indicators[] = 'Reported Health Condition';
        }

        if ($this->has_disability) {
            $indicators[] = 'Disability Reported';
        }

        if ($this->injury_history) {
            $indicators[] = 'Injury History';
        }

        if ($this->current_complaints) {
            $indicators[] = 'Current Health Complaints';
        }

        if ($this->mental_health_concerns) {
            $indicators[] = 'Mental-Health Concerns';
        }

        return $indicators;
    }

    public function getConcernCountAttribute(): int
    {
        return count(
            $this->concern_indicators
        );
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $assessmentDate =
            $attributes['assessment_date'] ?? null;

        if ($assessmentDate instanceof CarbonInterface) {
            $assessmentDate =
                $assessmentDate->format('Y-m-d');
        } elseif ($assessmentDate) {
            $assessmentDate = Carbon::parse(
                $assessmentDate
            )->format('Y-m-d');
        }

        $values = [
            $assessmentDate ?: '',

            self::normalizeValue(
                $attributes['health_condition'] ?? null
            ),

            self::normalizeValue(
                $attributes['disability_details'] ?? null
            ),

            self::normalizeValue(
                $attributes['current_complaints'] ?? null
            ),

            self::normalizeValue(
                $attributes['mental_health_concerns'] ?? null
            ),
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