<?php

namespace App\Observers;

use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\Intervention;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class NotificationObserver
{
    public function __construct(
        private readonly NotificationService
            $notificationService
    ) {
    }

    public function created(Model $model): void
    {
        $actor = $this->actor();

        match (true) {
            $model instanceof AuditSchedule =>
                $this->notificationService
                    ->notifyAuditAssigned(
                        $model,
                        $actor
                    ),

            $model instanceof Intervention =>
                $this->notificationService
                    ->notifyInterventionChanged(
                        $model,
                        'created',
                        $actor
                    ),

            $model instanceof ChildLaborerDocument =>
                $this->notificationService
                    ->notifyDocumentUploaded(
                        $model,
                        $actor
                    ),

            default => null,
        };
    }

    public function updated(Model $model): void
    {
        $actor = $this->actor();

        if (
            $model instanceof ChildLaborer
            && $model->wasChanged('status')
        ) {
            $this->notifyProfileStatus(
                $model,
                $actor
            );

            return;
        }

        if (
            $model instanceof AuditSchedule
            && $model->wasChanged('assigned_to')
        ) {
            $this->notificationService
                ->notifyAuditAssigned(
                    $model,
                    $actor
                );

            return;
        }

        if (
            $model instanceof AuditEvaluation
            && $model->wasChanged('status')
            && $model->status
                === AuditEvaluation::STATUS_FINALIZED
        ) {
            $this->notificationService
                ->notifyAuditFinalized(
                    $model,
                    $actor
                );

            return;
        }

        if (
            $model instanceof Intervention
            && $model->wasChanged('status')
        ) {
            $this->notificationService
                ->notifyInterventionChanged(
                    $model,
                    'updated',
                    $actor
                );
        }
    }

    private function notifyProfileStatus(
        ChildLaborer $childLaborer,
        ?User $actor
    ): void {
        $oldStatus = $childLaborer
            ->getOriginal('status');

        if (
            $oldStatus
                === ChildLaborer::STATUS_ARCHIVED
            && $childLaborer->status
                !== ChildLaborer::STATUS_ARCHIVED
        ) {
            $this->notificationService
                ->notifyProfileRestored(
                    $childLaborer,
                    $actor
                );

            return;
        }

        match ($childLaborer->status) {
            ChildLaborer::STATUS_SUBMITTED =>
                $this->notificationService
                    ->notifyProfileSubmitted(
                        $childLaborer,
                        $actor
                    ),

            ChildLaborer::STATUS_RETURNED =>
                $this->notificationService
                    ->notifyProfileReturned(
                        $childLaborer,
                        $actor
                    ),

            ChildLaborer::STATUS_APPROVED =>
                $this->notificationService
                    ->notifyProfileApproved(
                        $childLaborer,
                        $actor
                    ),

            ChildLaborer::STATUS_ARCHIVED =>
                $this->notificationService
                    ->notifyProfileArchived(
                        $childLaborer,
                        $actor
                    ),

            default => null,
        };
    }

    private function actor(): ?User
    {
        $user = Auth::user();

        return $user instanceof User
            ? $user
            : null;
    }
}
