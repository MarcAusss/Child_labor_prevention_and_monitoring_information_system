<?php

namespace App\Services;

use App\Models\ChildLaborer;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ChildLaborerProfileService
{
    public function create(
        array $data,
        User $actor
    ): ChildLaborer {
        $this->ensureNoDuplicate($data);

        return DB::transaction(function () use (
            $data,
            $actor
        ): ChildLaborer {
            $attributes = $this->profileAttributes(
                $data
            );

            $attributes['created_by'] = $actor->id;

            $attributes['assigned_to'] =
                $actor->isProfilingOfficer()
                    ? $actor->id
                    : ($data['assigned_to'] ?? null);

            $attributes['duplicate_key'] =
                ChildLaborer::makeDuplicateKey(
                    $attributes
                );

            $attributes['status'] =
                ChildLaborer::STATUS_DRAFT;

            $childLaborer = ChildLaborer::query()
                ->create($attributes);

            $childLaborer->forceFill([
                'profile_number' => sprintf(
                    'CL-%s-%06d',
                    $childLaborer->created_at->format('Y'),
                    $childLaborer->id
                ),
            ])->save();

            return $childLaborer->fresh([
                'creator',
                'assignedOfficer',
            ]);
        });
    }

    public function update(
        ChildLaborer $childLaborer,
        array $data,
        User $actor
    ): ChildLaborer {
        $this->ensureNoDuplicate(
            $data,
            $childLaborer
        );

        return DB::transaction(function () use (
            $childLaborer,
            $data,
            $actor
        ): ChildLaborer {
            $attributes = $this->profileAttributes(
                $data
            );

            if ($actor->isProfilingOfficer()) {
                unset($attributes['assigned_to']);
            }

            $attributes['duplicate_key'] =
                ChildLaborer::makeDuplicateKey(
                    $attributes
                );

            $childLaborer->update(
                $attributes
            );

            return $childLaborer->fresh([
                'creator',
                'assignedOfficer',
                'reviewer',
            ]);
        });
    }

    public function submit(
        ChildLaborer $childLaborer
    ): ChildLaborer {
        $childLaborer->update([
            'status' => ChildLaborer::STATUS_SUBMITTED,
            'submitted_at' => now(),
            'returned_at' => null,
            'return_reason' => null,
            'reviewed_by' => null,
            'approved_at' => null,
        ]);

        return $childLaborer->fresh();
    }

    public function approve(
        ChildLaborer $childLaborer,
        User $reviewer
    ): ChildLaborer {
        $childLaborer->update([
            'status' => ChildLaborer::STATUS_APPROVED,
            'reviewed_by' => $reviewer->id,
            'approved_at' => now(),
            'returned_at' => null,
            'return_reason' => null,
        ]);

        return $childLaborer->fresh();
    }

    public function returnForCorrection(
        ChildLaborer $childLaborer,
        User $reviewer,
        string $reason
    ): ChildLaborer {
        $childLaborer->update([
            'status' => ChildLaborer::STATUS_RETURNED,
            'reviewed_by' => $reviewer->id,
            'returned_at' => now(),
            'approved_at' => null,
            'return_reason' => $reason,
        ]);

        return $childLaborer->fresh();
    }

    public function archive(
        ChildLaborer $childLaborer,
        ?string $reason = null
    ): ChildLaborer {
        $childLaborer->update([
            'status_before_archive' => $childLaborer->status,
            'status' => ChildLaborer::STATUS_ARCHIVED,
            'archived_at' => now(),
            'archive_reason' => $reason,
        ]);

        return $childLaborer->fresh();
    }

    public function restore(
        ChildLaborer $childLaborer
    ): ChildLaborer {
        $restoredStatus =
            $childLaborer->status_before_archive;

        if (
            ! in_array(
                $restoredStatus,
                [
                    ChildLaborer::STATUS_DRAFT,
                    ChildLaborer::STATUS_SUBMITTED,
                    ChildLaborer::STATUS_RETURNED,
                    ChildLaborer::STATUS_APPROVED,
                ],
                true
            )
        ) {
            $restoredStatus =
                ChildLaborer::STATUS_DRAFT;
        }

        $childLaborer->update([
            'status' => $restoredStatus,
            'status_before_archive' => null,
            'archived_at' => null,
            'archive_reason' => null,
        ]);

        return $childLaborer->fresh();
    }

    public function ensureNoDuplicate(
        array $data,
        ?ChildLaborer $ignoredRecord = null
    ): void {
        $duplicateKey =
            ChildLaborer::makeDuplicateKey($data);

        $duplicate = ChildLaborer::query()
            ->withTrashed()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredRecord,
                fn ($query) => $query->whereKeyNot(
                    $ignoredRecord->id
                )
            )
            ->first();

        if (! $duplicate) {
            return;
        }

        $profileNumber = $duplicate->profile_number
            ?: 'an existing profile';

        throw ValidationException::withMessages([
            'first_name' => "A possible duplicate was found under {$profileNumber}. Check the existing child laborer record before continuing.",
        ]);
    }

    private function profileAttributes(
        array $data
    ): array {
        return Arr::only(
            $data,
            [
                'assigned_to',

                'first_name',
                'middle_name',
                'last_name',
                'suffix',
                'sex',
                'birth_date',
                'civil_status',
                'nationality',
                'religion',
                'contact_number',
                'photo_path',
            ]
        );
    }
}