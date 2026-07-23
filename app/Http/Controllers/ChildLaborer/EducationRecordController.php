<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\EducationRecordRequest;
use App\Models\ChildLaborer;
use App\Models\EducationRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EducationRecordController extends Controller
{
    use AuthorizesRequests;

    public function index(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'view',
            $childLaborer
        );

        $educationRecords = $childLaborer
            ->educationRecords()
            ->orderByDesc('is_current')
            ->orderByDesc('school_year')
            ->orderByDesc('id')
            ->get();

        return view(
            'child-laborers.education-records.index',
            [
                'childLaborer' => $childLaborer,
                'educationRecords' => $educationRecords,
                'enrollmentStatuses' =>
                    EducationRecord::enrollmentStatuses(),
                'gradeYearLevels' =>
                    EducationRecord::gradeYearLevels(),
            ]
        );
    }

    public function store(
        EducationRecordRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        $duplicateKey =
            EducationRecord::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey
        );

        DB::transaction(function () use (
            $validated,
            $duplicateKey,
            $childLaborer
        ): void {
            $hasExistingRecord = $childLaborer
                ->educationRecords()
                ->exists();

            $makeCurrent =
                ! $hasExistingRecord
                || (bool) $validated['is_current'];

            if ($makeCurrent) {
                $childLaborer
                    ->educationRecords()
                    ->update([
                        'is_current' => false,
                    ]);
            }

            $childLaborer
                ->educationRecords()
                ->create([
                    ...$validated,
                    'is_current' => $makeCurrent,
                    'duplicate_key' => $duplicateKey,
                ]);
        });

        return back()->with(
            'success',
            'The education record was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        EducationRecord $educationRecord
    ): View {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $educationRecord
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        return view(
            'child-laborers.education-records.edit',
            [
                'childLaborer' => $childLaborer,
                'educationRecord' => $educationRecord,
                'enrollmentStatuses' =>
                    EducationRecord::enrollmentStatuses(),
                'gradeYearLevels' =>
                    EducationRecord::gradeYearLevels(),
            ]
        );
    }

    public function update(
        EducationRecordRequest $request,
        ChildLaborer $childLaborer,
        EducationRecord $educationRecord
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $educationRecord
        );

        $validated = $request->validated();

        $duplicateKey =
            EducationRecord::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey,
            $educationRecord
        );

        DB::transaction(function () use (
            $validated,
            $duplicateKey,
            $childLaborer,
            $educationRecord
        ): void {
            $makeCurrent = (bool) $validated[
                'is_current'
            ];

            /*
             * Do not allow the only current education record
             * to be unchecked directly.
             *
             * To change the current record, edit another
             * record and mark it as current.
             */
            if (
                $educationRecord->is_current
                && ! $makeCurrent
            ) {
                $makeCurrent = true;
            }

            if ($makeCurrent) {
                $childLaborer
                    ->educationRecords()
                    ->where(
                        'id',
                        '!=',
                        $educationRecord->id
                    )
                    ->update([
                        'is_current' => false,
                    ]);
            }

            $educationRecord->update([
                ...$validated,
                'is_current' => $makeCurrent,
                'duplicate_key' => $duplicateKey,
            ]);
        });

        return redirect()
            ->route(
                'child-laborers.education-records.index',
                $childLaborer
            )
            ->with(
                'success',
                'The education record was updated successfully.'
            );
    }

    public function destroy(
        ChildLaborer $childLaborer,
        EducationRecord $educationRecord
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $educationRecord
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        DB::transaction(function () use (
            $childLaborer,
            $educationRecord
        ): void {
            $wasCurrent =
                $educationRecord->is_current;

            $educationRecord->delete();

            if ($wasCurrent) {
                $nextRecord = $childLaborer
                    ->educationRecords()
                    ->orderByDesc('school_year')
                    ->orderByDesc('id')
                    ->first();

                $nextRecord?->update([
                    'is_current' => true,
                ]);
            }
        });

        return back()->with(
            'success',
            'The education record was removed successfully.'
        );
    }

    private function ensureNoDuplicate(
        ChildLaborer $childLaborer,
        string $duplicateKey,
        ?EducationRecord $ignoredRecord = null
    ): void {
        $duplicate = $childLaborer
            ->educationRecords()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredRecord,
                function (
                    $query
                ) use ($ignoredRecord): void {
                    $query->where(
                        'id',
                        '!=',
                        $ignoredRecord->id
                    );
                }
            )
            ->exists();

        if (! $duplicate) {
            return;
        }

        throw ValidationException::withMessages([
            'school_name' =>
                'This education record already exists in the child laborer profile.',
        ]);
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        EducationRecord $educationRecord
    ): void {
        abort_unless(
            (int) $educationRecord->child_laborer_id
                === (int) $childLaborer->id,
            404
        );
    }
}
