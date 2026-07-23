<?php

namespace App\Models;

use App\Policies\AuditEvaluationPolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[UsePolicy(AuditEvaluationPolicy::class)]
class AuditEvaluation extends Model
{
    public const STATUS_DRAFT = 'Draft';

    public const STATUS_SUBMITTED = 'Submitted';

    public const STATUS_FINALIZED = 'Finalized';

    protected $fillable = [
        'audit_schedule_id',
        'evaluated_by',
        'updated_by',
        'evaluation_date',
        'findings',
        'recommendations',
        'status',
        'submitted_at',
        'finalized_at',
    ];

    protected function casts(): array
    {
        return [
            'evaluation_date' => 'date',
            'submitted_at' => 'datetime',
            'finalized_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_FINALIZED,
        ];
    }

    public function auditSchedule(): BelongsTo
    {
        return $this->belongsTo(
            AuditSchedule::class,
            'audit_schedule_id',
            'id'
        );
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'evaluated_by',
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

    public function scopeFinalized(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_FINALIZED
        );
    }

    public function isDraft(): bool
    {
        return $this->status
            === self::STATUS_DRAFT;
    }

    public function isSubmitted(): bool
    {
        return $this->status
            === self::STATUS_SUBMITTED;
    }

    public function isFinalized(): bool
    {
        return $this->status
            === self::STATUS_FINALIZED;
    }

    public function isEditable(): bool
    {
        return ! $this->isFinalized();
    }
}