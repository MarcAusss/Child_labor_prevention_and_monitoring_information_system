<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Intervention extends Model
{
    public const STATUS_PENDING = 'Pending';

    public const STATUS_ONGOING = 'Ongoing';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    public const STATUS_DISCONTINUED = 'Discontinued';

    public const TYPE_EDUCATIONAL =
        'Educational Assistance';

    public const TYPE_FINANCIAL =
        'Financial Assistance';

    public const TYPE_LIVELIHOOD =
        'Livelihood Assistance';

    public const TYPE_MEDICAL =
        'Health and Medical Assistance';

    public const TYPE_PSYCHOSOCIAL =
        'Psychosocial Support';

    public const TYPE_SKILLS =
        'Skills Training';

    public const TYPE_EMPLOYMENT =
        'Employment Assistance';

    public const TYPE_RESCUE =
        'Rescue and Removal';

    public const TYPE_LEGAL =
        'Legal Assistance';

    public const TYPE_SOCIAL_PROTECTION =
        'Social Protection';

    public const TYPE_FOOD =
        'Food and Basic Needs';

    public const TYPE_SHELTER =
        'Shelter Assistance';

    public const TYPE_REFERRAL =
        'Referral to Another Agency';

    public const TYPE_OTHER = 'Other';

    protected $fillable = [
        'child_laborer_id',
        'created_by',
        'updated_by',
        'intervention_type',
        'provider',
        'description',
        'date_provided',
        'date_completed',
        'amount',
        'status',
        'remarks',
        'duplicate_key',
    ];

    protected function casts(): array
    {
        return [
            'date_provided' => 'date',
            'date_completed' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_ONGOING,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
            self::STATUS_DISCONTINUED,
        ];
    }

    public static function interventionTypes(): array
    {
        return [
            self::TYPE_EDUCATIONAL,
            self::TYPE_FINANCIAL,
            self::TYPE_LIVELIHOOD,
            self::TYPE_MEDICAL,
            self::TYPE_PSYCHOSOCIAL,
            self::TYPE_SKILLS,
            self::TYPE_EMPLOYMENT,
            self::TYPE_RESCUE,
            self::TYPE_LEGAL,
            self::TYPE_SOCIAL_PROTECTION,
            self::TYPE_FOOD,
            self::TYPE_SHELTER,
            self::TYPE_REFERRAL,
            self::TYPE_OTHER,
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

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'id'
        );
    }

    public function lastUpdater(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'updated_by',
            'id'
        );
    }

    public function scopePending(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_PENDING
        );
    }

    public function scopeOngoing(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_ONGOING
        );
    }

    public function scopeCompleted(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_COMPLETED
        );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                self::STATUS_PENDING,
                self::STATUS_ONGOING,
            ]
        );
    }

    public function isActive(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_PENDING,
                self::STATUS_ONGOING,
            ],
            true
        );
    }

    public function isCompleted(): bool
    {
        return $this->status
            === self::STATUS_COMPLETED;
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $dateProvided =
            $attributes['date_provided'] ?? null;

        if ($dateProvided instanceof CarbonInterface) {
            $dateProvided = $dateProvided->format(
                'Y-m-d'
            );
        } elseif ($dateProvided) {
            $dateProvided = Carbon::parse(
                $dateProvided
            )->format('Y-m-d');
        }

        $values = [
            self::normalizeValue(
                $attributes['intervention_type']
                    ?? null
            ),

            self::normalizeValue(
                $attributes['provider'] ?? null
            ),

            self::normalizeValue(
                $attributes['description'] ?? null
            ),

            $dateProvided ?: '',
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