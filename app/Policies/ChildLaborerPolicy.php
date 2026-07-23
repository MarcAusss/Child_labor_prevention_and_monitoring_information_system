<?php

namespace App\Policies;

use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\Role;
use App\Models\User;

class ChildLaborerPolicy
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

    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole([
            Role::ADMIN,
            Role::PROFILING_OFFICER,
            Role::VIEWER,
        ]);
    }

    public function view(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if (
            $user->isAdmin()
            || $user->isViewer()
        ) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (
                $childLaborer->created_by === $user->id
                || $childLaborer->assigned_to === $user->id
            );
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole([
            Role::ADMIN,
            Role::PROFILING_OFFICER,
        ]);
    }

    public function update(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if ($childLaborer->status === ChildLaborer::STATUS_ARCHIVED) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && $childLaborer->isEditableByProfilingOfficer()
            && (
                $childLaborer->created_by === $user->id
                || $childLaborer->assigned_to === $user->id
            );
    }

    public function submit(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $this->update(
            $user,
            $childLaborer
        ) && in_array(
            $childLaborer->status,
            [
                ChildLaborer::STATUS_DRAFT,
                ChildLaborer::STATUS_RETURNED,
            ],
            true
        );
    }

    public function approve(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $user->isAdmin()
            && $childLaborer->status
            === ChildLaborer::STATUS_SUBMITTED;
    }

    public function return(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $user->isAdmin()
            && $childLaborer->status
            === ChildLaborer::STATUS_SUBMITTED;
    }

    public function archive(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $user->isAdmin()
            && $childLaborer->status
            !== ChildLaborer::STATUS_ARCHIVED;
    }

    public function restore(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $user->isAdmin()
            && $childLaborer->status
            === ChildLaborer::STATUS_ARCHIVED;
    }

    public function assign(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $user->isAdmin();
    }

    public function viewPhoto(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $this->view(
            $user,
            $childLaborer
        );
    }

    public function viewHealth(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (
                $childLaborer->created_by === $user->id
                || $childLaborer->assigned_to === $user->id
            );
    }

    public function updateHealth(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if (
            $childLaborer->status
            === ChildLaborer::STATUS_ARCHIVED
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && $childLaborer->isEditableByProfilingOfficer()
            && (
                $childLaborer->created_by === $user->id
                || $childLaborer->assigned_to === $user->id
            );
    }

    public function viewInterventions(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if (
            $user->isAdmin()
            || $user->isViewer()
        ) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (
                (int) $childLaborer->created_by
                === (int) $user->id

                || (int) $childLaborer->assigned_to
                === (int) $user->id
            );
    }

    public function manageInterventions(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if (
            $childLaborer->status
            === ChildLaborer::STATUS_ARCHIVED
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (
                (int) $childLaborer->created_by
                === (int) $user->id

                || (int) $childLaborer->assigned_to
                === (int) $user->id
            );
    }

    public function viewDocuments(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        return $this->view(
            $user,
            $childLaborer
        );
    }

    public function uploadDocuments(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if (
            $childLaborer->status
            === ChildLaborer::STATUS_ARCHIVED
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (
                (int) $childLaborer->created_by
                === (int) $user->id

                || (int) $childLaborer->assigned_to
                === (int) $user->id
            );
    }

    public function downloadDocument(
        User $user,
        ChildLaborer $childLaborer,
        ChildLaborerDocument $document
    ): bool {
        if (
            (int) $document->child_laborer_id
            !== (int) $childLaborer->id
        ) {
            return false;
        }

        if (
            $user->isViewer()
            && $document->is_confidential
        ) {
            return false;
        }

        return $this->viewDocuments(
            $user,
            $childLaborer
        );
    }

    public function deleteDocument(
        User $user,
        ChildLaborer $childLaborer,
        ChildLaborerDocument $document
    ): bool {
        if (
            (int) $document->child_laborer_id
            !== (int) $childLaborer->id
        ) {
            return false;
        }

        if (
            $childLaborer->status
            === ChildLaborer::STATUS_ARCHIVED
        ) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (int) $document->uploaded_by
            === (int) $user->id
            && (
                (int) $childLaborer->created_by
                === (int) $user->id

                || (int) $childLaborer->assigned_to
                === (int) $user->id
            );
    }

    public function viewActivity(
        User $user,
        ChildLaborer $childLaborer
    ): bool {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isProfilingOfficer()
            && (
                (int) $childLaborer->created_by
                === (int) $user->id

                || (int) $childLaborer->assigned_to
                === (int) $user->id
            );
    }
}