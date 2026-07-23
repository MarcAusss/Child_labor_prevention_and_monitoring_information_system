<?php

namespace App\Observers;

use App\Models\ActivityLog;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\Intervention;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ActivityObserver
{
    public function __construct(
        private readonly ActivityLogger
            $activityLogger
    ) {
    }

    public function created(
        Model $model
    ): void {
        if ($model instanceof ActivityLog) {
            return;
        }

        $this->activityLogger->modelChange(
            subject: $model,
            action: ActivityLog::ACTION_CREATED,
            description:
                'Created '.$this->subjectLabel($model),
            newValues: $this->auditableAttributes(
                $model->getAttributes()
            )
        );
    }

    public function updated(
        Model $model
    ): void {
        if ($model instanceof ActivityLog) {
            return;
        }

        $changes = $this->auditableAttributes(
            $model->getChanges()
        );

        if ($changes === []) {
            return;
        }

        $oldValues = [];

        foreach (
            array_keys($changes) as $attribute
        ) {
            $oldValues[$attribute] =
                $model->getRawOriginal(
                    $attribute
                );
        }

        [
            $action,
            $description,
        ] = $this->resolveUpdateAction(
            $model,
            $oldValues,
            $changes
        );

        $this->activityLogger->modelChange(
            subject: $model,
            action: $action,
            description: $description,
            oldValues: $oldValues,
            newValues: $changes
        );
    }

    public function deleted(
        Model $model
    ): void {
        if ($model instanceof ActivityLog) {
            return;
        }

        $this->activityLogger->modelChange(
            subject: $model,
            action: ActivityLog::ACTION_REMOVED,
            description:
                'Removed '.$this->subjectLabel($model),
            oldValues: $this->auditableAttributes(
                $model->getAttributes()
            )
        );
    }

    public function restored(
        Model $model
    ): void {
        if ($model instanceof ActivityLog) {
            return;
        }

        $this->activityLogger->modelChange(
            subject: $model,
            action: ActivityLog::ACTION_RESTORED,
            description:
                'Restored '.$this->subjectLabel($model),
            newValues: $this->auditableAttributes(
                $model->getAttributes()
            )
        );
    }

    /**
     * @param array<string, mixed> $oldValues
     * @param array<string, mixed> $newValues
     *
     * @return array{0: string, 1: string}
     */
    private function resolveUpdateAction(
        Model $model,
        array $oldValues,
        array $newValues
    ): array {
        if (
            $model instanceof ChildLaborer
            && array_key_exists(
                'status',
                $newValues
            )
        ) {
            $oldStatus =
                $oldValues['status'] ?? null;

            $newStatus =
                $newValues['status'] ?? null;

            $action = match (true) {
                $oldStatus
                    === ChildLaborer::STATUS_ARCHIVED
                    && $newStatus
                    !== ChildLaborer::STATUS_ARCHIVED
                    => ActivityLog::ACTION_RESTORED,

                $newStatus
                    === ChildLaborer::STATUS_SUBMITTED
                    => ActivityLog::ACTION_SUBMITTED,

                $newStatus
                    === ChildLaborer::STATUS_RETURNED
                    => ActivityLog::ACTION_RETURNED,

                $newStatus
                    === ChildLaborer::STATUS_APPROVED
                    => ActivityLog::ACTION_APPROVED,

                $newStatus
                    === ChildLaborer::STATUS_ARCHIVED
                    => ActivityLog::ACTION_ARCHIVED,

                default =>
                    ActivityLog::ACTION_UPDATED,
            };

            return [
                $action,

                Str::headline($action)
                    .' child laborer profile '
                    .$this->profileIdentifier(
                        $model
                    ),
            ];
        }

        return [
            ActivityLog::ACTION_UPDATED,

            'Updated '.$this->subjectLabel(
                $model
            ),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     *
     * @return array<string, mixed>
     */
    private function auditableAttributes(
        array $attributes
    ): array {
        return Arr::except(
            $attributes,
            config(
                'activity-log.ignored_model_fields',
                []
            )
        );
    }

    private function subjectLabel(
        Model $model
    ): string {
        return match (true) {
            $model instanceof ChildLaborer =>
                'child laborer profile '
                .$this->profileIdentifier($model),

            $model instanceof User =>
                'user account '
                .($model->email ?: '#'.$model->id),

            $model instanceof Intervention =>
                'intervention '
                .($model->intervention_type
                    ?: '#'.$model->id),

            $model instanceof ChildLaborerDocument =>
                'document '
                .($model->original_name
                    ?: '#'.$model->id),

            default =>
                Str::lower(
                    Str::headline(
                        class_basename($model)
                    )
                )
                .' #'.$model->getKey(),
        };
    }

    private function profileIdentifier(
        ChildLaborer $childLaborer
    ): string {
        return $childLaborer->profile_number
            ?: '#'.$childLaborer->id;
    }
}