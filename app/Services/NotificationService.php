<?php

namespace App\Services;

use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\Intervention;
use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;

class NotificationService
{
    public const TYPE_PROFILE = 'profile';

    public const TYPE_AUDIT = 'audit';

    public const TYPE_INTERVENTION = 'intervention';

    public const TYPE_DOCUMENT = 'document';

    public const TYPE_SYSTEM = 'system';

    public const SEVERITY_INFO = 'info';

    public const SEVERITY_SUCCESS = 'success';

    public const SEVERITY_WARNING = 'warning';

    public const SEVERITY_DANGER = 'danger';

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            self::TYPE_PROFILE =>
                'Profile Workflow',

            self::TYPE_AUDIT =>
                'Audit and Evaluation',

            self::TYPE_INTERVENTION =>
                'Intervention',

            self::TYPE_DOCUMENT =>
                'Document',

            self::TYPE_SYSTEM =>
                'System',
        ];
    }

    /**
     * @param iterable<int, User> $recipients
     * @param array<string, mixed> $routeParameters
     * @param array<string, mixed> $metadata
     */
    public function send(
        iterable $recipients,
        string $title,
        string $message,
        string $type = self::TYPE_SYSTEM,
        string $severity = self::SEVERITY_INFO,
        ?string $routeName = null,
        array $routeParameters = [],
        ?int $childLaborerId = null,
        ?User $actor = null,
        array $metadata = []
    ): void {
        $users = collect($recipients)
            ->filter(
                fn (mixed $user): bool =>
                    $user instanceof User
                    && $user->exists
                    && $this->isActive($user)
            )
            ->unique(
                fn (User $user): int =>
                    (int) $user->id
            );

        if ($actor) {
            $users = $users->reject(
                fn (User $user): bool =>
                    (int) $user->id
                    === (int) $actor->id
            );
        }

        if ($users->isEmpty()) {
            return;
        }

        Notification::send(
            $users,
            new SystemNotification(
                title: $title,
                message: $message,
                notificationType: $type,
                severity: $severity,
                routeName: $routeName,
                routeParameters: $routeParameters,
                childLaborerId: $childLaborerId,
                actorName: $actor?->name,
                metadata: $metadata
            )
        );
    }

    public function notifyProfileSubmitted(
        ChildLaborer $childLaborer,
        ?User $actor = null
    ): void {
        $this->send(
            recipients: $this->administrators(),
            title: 'Profile submitted for review',
            message:
                $childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .' is ready for administrative review.',
            type: self::TYPE_PROFILE,
            severity: self::SEVERITY_INFO,
            routeName: 'child-laborers.show',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'status' =>
                    $childLaborer->status,
            ]
        );
    }

    public function notifyProfileReturned(
        ChildLaborer $childLaborer,
        ?User $actor = null
    ): void {
        $this->send(
            recipients: $this->profileWorkers(
                $childLaborer
            ),
            title: 'Profile returned for correction',
            message:
                $childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .' was returned and requires correction.'
                .(
                    $childLaborer->return_reason
                    ? ' Reason: '
                        .$childLaborer->return_reason
                    : ''
                ),
            type: self::TYPE_PROFILE,
            severity: self::SEVERITY_WARNING,
            routeName: 'child-laborers.show',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'status' =>
                    $childLaborer->status,

                'return_reason' =>
                    $childLaborer->return_reason,
            ]
        );
    }

    public function notifyProfileApproved(
        ChildLaborer $childLaborer,
        ?User $actor = null
    ): void {
        $this->send(
            recipients: $this->profileWorkers(
                $childLaborer
            ),
            title: 'Profile approved',
            message:
                $childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .' was approved.',
            type: self::TYPE_PROFILE,
            severity: self::SEVERITY_SUCCESS,
            routeName: 'child-laborers.show',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'status' =>
                    $childLaborer->status,
            ]
        );
    }

    public function notifyProfileArchived(
        ChildLaborer $childLaborer,
        ?User $actor = null
    ): void {
        $this->send(
            recipients: $this
                ->profileWorkers($childLaborer)
                ->merge(
                    $this->administrators()
                ),
            title: 'Profile archived',
            message:
                $childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .' was archived.'
                .(
                    $childLaborer->archive_reason
                    ? ' Reason: '
                        .$childLaborer->archive_reason
                    : ''
                ),
            type: self::TYPE_PROFILE,
            severity: self::SEVERITY_DANGER,
            routeName: 'child-laborers.show',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'status' =>
                    $childLaborer->status,

                'archive_reason' =>
                    $childLaborer->archive_reason,
            ]
        );
    }

    public function notifyProfileRestored(
        ChildLaborer $childLaborer,
        ?User $actor = null
    ): void {
        $this->send(
            recipients: $this
                ->profileWorkers($childLaborer)
                ->merge(
                    $this->administrators()
                ),
            title: 'Profile restored',
            message:
                $childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .' was restored to active records.',
            type: self::TYPE_PROFILE,
            severity: self::SEVERITY_SUCCESS,
            routeName: 'child-laborers.show',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'status' =>
                    $childLaborer->status,
            ]
        );
    }

    public function notifyAuditAssigned(
        AuditSchedule $auditSchedule,
        ?User $actor = null
    ): void {
        $auditSchedule->loadMissing([
            'childLaborer:id,profile_number,first_name,middle_name,last_name,suffix',
            'assignedAdministrator:id,name,email',
        ]);

        $assignedAdministrator =
            $auditSchedule
                ->assignedAdministrator;

        if (! $assignedAdministrator) {
            return;
        }

        $childLaborer =
            $auditSchedule->childLaborer;

        $this->send(
            recipients: [
                $assignedAdministrator,
            ],
            title: 'Audit schedule assigned',
            message:
                'You were assigned to audit '
                .($childLaborer?->profile_number
                    ?: 'a child laborer profile')
                .' on '
                .$auditSchedule
                    ->scheduled_at
                    ->format('F d, Y h:i A')
                .'.',
            type: self::TYPE_AUDIT,
            severity: self::SEVERITY_INFO,
            routeName: 'audit-schedules.show',
            routeParameters: [
                'auditSchedule' =>
                    $auditSchedule->id,
            ],
            childLaborerId:
                $auditSchedule
                    ->child_laborer_id,
            actor: $actor,
            metadata: [
                'scheduled_at' =>
                    $auditSchedule
                        ->scheduled_at
                        ->toAtomString(),

                'location' =>
                    $auditSchedule->location,

                'status' =>
                    $auditSchedule->status,
            ]
        );
    }

    public function notifyAuditFinalized(
        AuditEvaluation $evaluation,
        ?User $actor = null
    ): void {
        $evaluation->loadMissing([
            'auditSchedule.childLaborer',
        ]);

        $auditSchedule =
            $evaluation->auditSchedule;

        $childLaborer =
            $auditSchedule?->childLaborer;

        if (! $auditSchedule || ! $childLaborer) {
            return;
        }

        $this->send(
            recipients: $this
                ->profileWorkers($childLaborer)
                ->merge(
                    $this->administrators()
                ),
            title: 'Audit evaluation finalized',
            message:
                'The audit evaluation for '
                .$childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .' was finalized.',
            type: self::TYPE_AUDIT,
            severity: self::SEVERITY_SUCCESS,
            routeName: 'audit-schedules.show',
            routeParameters: [
                'auditSchedule' =>
                    $auditSchedule->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'evaluation_id' =>
                    $evaluation->id,

                'evaluation_date' =>
                    $evaluation
                        ->evaluation_date
                        ?->format('Y-m-d'),

                'status' =>
                    $evaluation->status,
            ]
        );
    }

    public function notifyInterventionChanged(
        Intervention $intervention,
        string $event,
        ?User $actor = null
    ): void {
        $intervention->loadMissing([
            'childLaborer',
        ]);

        $childLaborer =
            $intervention->childLaborer;

        if (! $childLaborer) {
            return;
        }

        $severity = match (
            $intervention->status
        ) {
            Intervention::STATUS_COMPLETED =>
                self::SEVERITY_SUCCESS,

            Intervention::STATUS_CANCELLED,
            Intervention::STATUS_DISCONTINUED =>
                self::SEVERITY_WARNING,

            default =>
                self::SEVERITY_INFO,
        };

        $this->send(
            recipients: $this
                ->profileWorkers($childLaborer)
                ->merge(
                    $this->administrators()
                ),
            title: 'Intervention '.$event,
            message:
                $intervention
                    ->intervention_type
                .' for '
                .$childLaborer
                    ->profile_number
                .' is now '
                .$intervention->status
                .'.',
            type: self::TYPE_INTERVENTION,
            severity: $severity,
            routeName:
                'child-laborers.interventions.index',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'intervention_id' =>
                    $intervention->id,

                'intervention_type' =>
                    $intervention
                        ->intervention_type,

                'status' =>
                    $intervention->status,
            ]
        );
    }

    public function notifyDocumentUploaded(
        ChildLaborerDocument $document,
        ?User $actor = null
    ): void {
        $document->loadMissing([
            'childLaborer',
        ]);

        $childLaborer =
            $document->childLaborer;

        if (! $childLaborer) {
            return;
        }

        $this->send(
            recipients: $this
                ->profileWorkers($childLaborer)
                ->merge(
                    $this->administrators()
                ),
            title: 'New profile document',
            message:
                $document->original_name
                .' was uploaded to '
                .$childLaborer->profile_number
                .' — '
                .$childLaborer->full_name
                .'.',
            type: self::TYPE_DOCUMENT,
            severity: self::SEVERITY_INFO,
            routeName:
                'child-laborers.documents.index',
            routeParameters: [
                'childLaborer' =>
                    $childLaborer->id,
            ],
            childLaborerId:
                $childLaborer->id,
            actor: $actor,
            metadata: [
                'document_id' =>
                    $document->id,

                'document_type' =>
                    $document->document_type,

                'is_confidential' =>
                    $document->is_confidential,
            ]
        );
    }

    /**
     * @return Collection<int, User>
     */
    public function administrators(): Collection
    {
        return User::query()
            ->with('role')
            ->get()
            ->filter(
                fn (User $user): bool =>
                    $this->isActive($user)
                    && (
                        $user->isAdmin()
                        || $user->isSuperAdmin()
                    )
            )
            ->values();
    }

    /**
     * @return Collection<int, User>
     */
    private function profileWorkers(
        ChildLaborer $childLaborer
    ): Collection {
        $userIds = collect([
            $childLaborer->created_by,
            $childLaborer->assigned_to,
        ])
            ->filter()
            ->map(
                fn (mixed $id): int =>
                    (int) $id
            )
            ->unique()
            ->values();

        if ($userIds->isEmpty()) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $userIds)
            ->get()
            ->filter(
                fn (User $user): bool =>
                    $this->isActive($user)
            )
            ->values();
    }

    private function isActive(User $user): bool
    {
        if (! array_key_exists(
            'is_active',
            $user->getAttributes()
        )) {
            return true;
        }

        return (bool) $user->is_active;
    }
}
