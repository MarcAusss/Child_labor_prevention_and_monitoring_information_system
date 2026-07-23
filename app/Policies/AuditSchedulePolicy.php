<?php

namespace App\Policies;

use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\User;

class AuditSchedulePolicy
{
    public function before(
        User $user,
        string $ability
    ): ?bool {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(
        User $user
    ): bool {
        return $user->isAdmin();
    }

    public function view(
        User $user,
        AuditSchedule $auditSchedule
    ): bool {
        return $user->isAdmin();
    }

    public function create(
        User $user
    ): bool {
        return $user->isAdmin();
    }

    public function update(
        User $user,
        AuditSchedule $auditSchedule
    ): bool {
        if (! $user->isAdmin()) {
            return false;
        }

        if (! $auditSchedule->isEditable()) {
            return false;
        }

        return $auditSchedule
            ->childLaborer?->status
            !== ChildLaborer::STATUS_ARCHIVED;
    }

    public function delete(
        User $user,
        AuditSchedule $auditSchedule
    ): bool {
        return false;
    }
}