<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class WorkHazard extends Model
{
    public const TYPE_PHYSICAL = 'Physical';

    public const TYPE_CHEMICAL = 'Chemical';

    public const TYPE_BIOLOGICAL = 'Biological';

    public const TYPE_MECHANICAL = 'Mechanical';

    public const TYPE_ELECTRICAL = 'Electrical';

    public const TYPE_ERGONOMIC = 'Ergonomic';

    public const TYPE_PSYCHOSOCIAL = 'Psychosocial';

    public const TYPE_FIRE_EXPLOSION = 'Fire or Explosion';

    public const TYPE_OTHER = 'Other';

    public const FREQUENCY_DAILY = 'Daily';

    public const FREQUENCY_SEVERAL_WEEKLY =
        'Several Times Per Week';

    public const FREQUENCY_WEEKLY = 'Weekly';

    public const FREQUENCY_MONTHLY = 'Monthly';

    public const FREQUENCY_SEASONAL = 'Seasonal';

    public const FREQUENCY_OCCASIONAL = 'Occasional';

    public const FREQUENCY_ONE_TIME = 'One-Time';

    protected $fillable = [
        'employment_record_id',
        'hazard_type',
        'hazard_description',
        'exposure_frequency',
        'equipment_machinery',
        'chemicals_substances',
        'heavy_work',
        'long_hours',
        'night_work',
        'unsafe_conditions',
        'ppe_provided',
        'ppe_description',
        'injuries_incidents',
        'duplicate_key',
    ];

    protected function casts(): array
    {
        return [
            'heavy_work' => 'boolean',
            'long_hours' => 'boolean',
            'night_work' => 'boolean',
            'unsafe_conditions' => 'boolean',
            'ppe_provided' => 'boolean',
        ];
    }

    public static function hazardTypes(): array
    {
        return [
            self::TYPE_PHYSICAL,
            self::TYPE_CHEMICAL,
            self::TYPE_BIOLOGICAL,
            self::TYPE_MECHANICAL,
            self::TYPE_ELECTRICAL,
            self::TYPE_ERGONOMIC,
            self::TYPE_PSYCHOSOCIAL,
            self::TYPE_FIRE_EXPLOSION,
            self::TYPE_OTHER,
        ];
    }

    public static function exposureFrequencies(): array
    {
        return [
            self::FREQUENCY_DAILY,
            self::FREQUENCY_SEVERAL_WEEKLY,
            self::FREQUENCY_WEEKLY,
            self::FREQUENCY_MONTHLY,
            self::FREQUENCY_SEASONAL,
            self::FREQUENCY_OCCASIONAL,
            self::FREQUENCY_ONE_TIME,
        ];
    }

    public function employmentRecord(): BelongsTo
    {
        return $this->belongsTo(
            EmploymentRecord::class,
            'employment_record_id',
            'id'
        );
    }

    public function getFlaggedConditionsAttribute(): array
    {
        $conditions = [];

        if ($this->heavy_work) {
            $conditions[] = 'Heavy Work';
        }

        if ($this->long_hours) {
            $conditions[] = 'Long Working Hours';
        }

        if ($this->night_work) {
            $conditions[] = 'Night Work';
        }

        if ($this->unsafe_conditions) {
            $conditions[] = 'Unsafe Conditions';
        }

        return $conditions;
    }

    public function getIndicatorCountAttribute(): int
    {
        return count(
            $this->flagged_conditions
        );
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $values = [
            self::normalizeValue(
                $attributes['hazard_type'] ?? null
            ),

            self::normalizeValue(
                $attributes['hazard_description'] ?? null
            ),

            self::normalizeValue(
                $attributes['exposure_frequency'] ?? null
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