<?php

namespace App\Policies;

use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use App\Models\User;

class AuditEvaluationPolicy
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
        AuditEvaluation $auditEvaluation
    ): bool {
        return $user->isAdmin();
    }

    public function create(
        User $user,
        AuditSchedule $auditSchedule
    ): bool {
        return $user->isAdmin()
            && $auditSchedule->isEditable();
    }

    public function update(
        User $user,
        AuditEvaluation $auditEvaluation
    ): bool {
        return $user->isAdmin()
            && $auditEvaluation->isEditable()
            && $auditEvaluation
                ->auditSchedule
                ->isEditable();
    }

    public function delete(
        User $user,
        AuditEvaluation $auditEvaluation
    ): bool {
        return false;
    }
}