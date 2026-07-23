<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class EducationRecord extends Model
{
    public const STATUS_ENROLLED = 'Enrolled';

    public const STATUS_NOT_ENROLLED = 'Not Enrolled';

    public const STATUS_DROPPED_OUT = 'Dropped Out';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_GRADUATED = 'Graduated';

    protected $fillable = [
        'child_laborer_id',
        'school_name',
        'grade_year_level',
        'school_year',
        'school_address',
        'enrollment_status',
        'reason_not_attending',
        'last_grade_completed',
        'date_enrolled',
        'date_ended',
        'is_current',
        'remarks',
        'duplicate_key',
    ];

    protected function casts(): array
    {
        return [
            'date_enrolled' => 'date',
            'date_ended' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public static function enrollmentStatuses(): array
    {
        return [
            self::STATUS_ENROLLED,
            self::STATUS_NOT_ENROLLED,
            self::STATUS_DROPPED_OUT,
            self::STATUS_COMPLETED,
            self::STATUS_GRADUATED,
        ];
    }

    public static function gradeYearLevels(): array
    {
        return [
            'Day Care',
            'Kindergarten',
            'Grade 1',
            'Grade 2',
            'Grade 3',
            'Grade 4',
            'Grade 5',
            'Grade 6',
            'Grade 7',
            'Grade 8',
            'Grade 9',
            'Grade 10',
            'Grade 11',
            'Grade 12',
            'ALS Elementary',
            'ALS Junior High School',
            'Technical or Vocational',
            'College Level',
            'Not Applicable',
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

    public function requiresNonAttendanceReason(): bool
    {
        return in_array(
            $this->enrollment_status,
            [
                self::STATUS_NOT_ENROLLED,
                self::STATUS_DROPPED_OUT,
            ],
            true
        );
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $values = [
            self::normalizeValue(
                $attributes['school_name'] ?? null
            ),

            self::normalizeValue(
                $attributes['grade_year_level'] ?? null
            ),

            self::normalizeValue(
                $attributes['school_year'] ?? null
            ),

            self::normalizeValue(
                $attributes['enrollment_status'] ?? null
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