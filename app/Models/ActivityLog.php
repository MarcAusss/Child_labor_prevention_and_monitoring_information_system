<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use LogicException;

class ActivityLog extends Model
{
    public const ACTION_CREATED = 'created';

    public const ACTION_UPDATED = 'updated';

    public const ACTION_REMOVED = 'removed';

    public const ACTION_RESTORED = 'restored';

    public const ACTION_SUBMITTED = 'submitted';

    public const ACTION_RETURNED = 'returned';

    public const ACTION_APPROVED = 'approved';

    public const ACTION_ARCHIVED = 'archived';

    public const ACTION_DOWNLOADED = 'downloaded';

    public const ACTION_LOGIN = 'login';

    public const ACTION_LOGOUT = 'logout';

    public const ACTION_LOGIN_FAILED = 'login_failed';

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'actor_name',
        'role_name',
        'child_laborer_id',
        'action',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'request_method',
        'route_name',
        'url',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        /*
         * Activity logs must not be changed or removed through
         * normal Eloquent operations.
         */
        static::updating(function (): never {
            throw new LogicException(
                'Activity log records are immutable.'
            );
        });

        static::deleting(function (): never {
            throw new LogicException(
                'Activity log records cannot be deleted.'
            );
        });
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id',
            'id'
        );
    }

    public function childLaborer(): BelongsTo
    {
        return $this->belongsTo(
            ChildLaborer::class,
            'child_laborer_id',
            'id'
        );
    }

    public function scopeForAction(
        Builder $query,
        ?string $action
    ): Builder {
        return $query->when(
            filled($action),
            fn (Builder $query) =>
                $query->where(
                    'action',
                    $action
                )
        );
    }

    public function scopeForEntityType(
        Builder $query,
        ?string $entityType
    ): Builder {
        return $query->when(
            filled($entityType),
            fn (Builder $query) =>
                $query->where(
                    'entity_type',
                    $entityType
                )
        );
    }

    public function getActionLabelAttribute(): string
    {
        return Str::headline(
            $this->action
        );
    }

    public function getEntityLabelAttribute(): string
    {
        if (! $this->entity_type) {
            return 'System';
        }

        return Str::headline(
            class_basename(
                $this->entity_type
            )
        );
    }

    public function getActorDisplayAttribute(): string
    {
        return $this->actor_name
            ?: $this->actor?->name
            ?: 'System or unauthenticated user';
    }

    public function getHasChangesAttribute(): bool
    {
        return filled($this->old_values)
            || filled($this->new_values);
    }
}