<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class EmploymentRecord extends Model
{
    public const WORK_FULL_TIME = 'Full-Time';

    public const WORK_PART_TIME = 'Part-Time';

    public const WORK_SEASONAL = 'Seasonal';

    public const WORK_OCCASIONAL = 'Occasional';

    public const WORK_ON_CALL = 'On-Call';

    public const WORK_PIECE_RATE = 'Piece-Rate';

    public const WORK_UNPAID_FAMILY = 'Unpaid Family Work';

    public const WORK_OTHER = 'Other';

    public const ARRANGEMENT_FORMAL = 'Formal';

    public const ARRANGEMENT_INFORMAL = 'Informal';

    public const ARRANGEMENT_SELF_EMPLOYED = 'Self-Employed';

    public const ARRANGEMENT_FAMILY_WORK = 'Family Work';

    public const ARRANGEMENT_CONTRACTUAL = 'Contractual';

    public const ARRANGEMENT_SEASONAL = 'Seasonal';

    public const ARRANGEMENT_CASUAL = 'Casual';

    public const ARRANGEMENT_APPRENTICESHIP = 'Apprenticeship';

    public const ARRANGEMENT_OTHER = 'Other';

    protected $fillable = [
        'child_laborer_id',
        'employer_name',
        'employer_address',
        'work_type',
        'occupation',
        'industry',
        'employment_arrangement',
        'start_date',
        'end_date',
        'days_per_week',
        'hours_per_day',
        'income_amount',
        'income_frequency',
        'is_current',
        'remarks',
        'duplicate_key',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'days_per_week' => 'integer',
            'hours_per_day' => 'decimal:2',
            'income_amount' => 'decimal:2',
            'is_current' => 'boolean',
        ];
    }

    public static function workTypes(): array
    {
        return [
            self::WORK_FULL_TIME,
            self::WORK_PART_TIME,
            self::WORK_SEASONAL,
            self::WORK_OCCASIONAL,
            self::WORK_ON_CALL,
            self::WORK_PIECE_RATE,
            self::WORK_UNPAID_FAMILY,
            self::WORK_OTHER,
        ];
    }

    public static function employmentArrangements(): array
    {
        return [
            self::ARRANGEMENT_FORMAL,
            self::ARRANGEMENT_INFORMAL,
            self::ARRANGEMENT_SELF_EMPLOYED,
            self::ARRANGEMENT_FAMILY_WORK,
            self::ARRANGEMENT_CONTRACTUAL,
            self::ARRANGEMENT_SEASONAL,
            self::ARRANGEMENT_CASUAL,
            self::ARRANGEMENT_APPRENTICESHIP,
            self::ARRANGEMENT_OTHER,
        ];
    }

    public static function incomeFrequencies(): array
    {
        return [
            'Daily',
            'Weekly',
            'Every Two Weeks',
            'Monthly',
            'Per Piece',
            'Per Task',
            'Irregular',
            'Unpaid',
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

    public function getWeeklyHoursAttribute(): float
    {
        return round(
            (float) $this->hours_per_day
            * (int) $this->days_per_week,
            2
        );
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $startDate = $attributes['start_date'] ?? null;

        if ($startDate instanceof CarbonInterface) {
            $startDate = $startDate->format('Y-m-d');
        } elseif ($startDate) {
            $startDate = Carbon::parse(
                $startDate
            )->format('Y-m-d');
        }

        $values = [
            self::normalizeValue(
                $attributes['employer_name'] ?? null
            ),

            self::normalizeValue(
                $attributes['occupation'] ?? null
            ),

            self::normalizeValue(
                $attributes['work_type'] ?? null
            ),

            $startDate ?: '',
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
    public function workHazards(): HasMany
    {
        return $this->hasMany(
            WorkHazard::class,
            'employment_record_id',
            'id'
        );
    }
}