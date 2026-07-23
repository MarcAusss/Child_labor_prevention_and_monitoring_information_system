<?php

namespace App\Models;

use App\Policies\AuditSchedulePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[UsePolicy(AuditSchedulePolicy::class)]
class AuditSchedule extends Model
{
    public const STATUS_SCHEDULED = 'Scheduled';

    public const STATUS_IN_PROGRESS = 'In Progress';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_CANCELLED = 'Cancelled';

    protected $fillable = [
        'child_laborer_id',
        'created_by',
        'assigned_to',
        'scheduled_at',
        'location',
        'status',
        'remarks',
        'started_at',
        'completed_at',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_COMPLETED,
            self::STATUS_CANCELLED,
        ];
    }

    public static function editableStatuses(): array
    {
        return [
            self::STATUS_SCHEDULED,
            self::STATUS_IN_PROGRESS,
            self::STATUS_CANCELLED,
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

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by',
            'id'
        );
    }

    public function assignedAdministrator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to',
            'id'
        );
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(
            AuditEvaluation::class,
            'audit_schedule_id',
            'id'
        );
    }

    public function latestEvaluation(): HasOne
    {
        return $this->hasOne(
            AuditEvaluation::class,
            'audit_schedule_id',
            'id'
        )->latestOfMany();
    }

    public function finalEvaluation(): HasOne
    {
        return $this->hasOne(
            AuditEvaluation::class,
            'audit_schedule_id',
            'id'
        )->where(
            'status',
            AuditEvaluation::STATUS_FINALIZED
        );
    }

    public function scopeUpcoming(
        Builder $query
    ): Builder {
        return $query
            ->where(
                'status',
                self::STATUS_SCHEDULED
            )
            ->where(
                'scheduled_at',
                '>=',
                now()
            );
    }

    public function scopeActive(
        Builder $query
    ): Builder {
        return $query->whereIn(
            'status',
            [
                self::STATUS_SCHEDULED,
                self::STATUS_IN_PROGRESS,
            ]
        );
    }

    public function isScheduled(): bool
    {
        return $this->status
            === self::STATUS_SCHEDULED;
    }

    public function isInProgress(): bool
    {
        return $this->status
            === self::STATUS_IN_PROGRESS;
    }

    public function isCompleted(): bool
    {
        return $this->status
            === self::STATUS_COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this->status
            === self::STATUS_CANCELLED;
    }

    public function isEditable(): bool
    {
        return ! $this->isCompleted()
            && ! $this->isCancelled();
    }
}