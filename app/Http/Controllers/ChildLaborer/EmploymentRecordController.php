<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\EmploymentRecordRequest;
use App\Models\ChildLaborer;
use App\Models\EmploymentRecord;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EmploymentRecordController extends Controller
{
    use AuthorizesRequests;

    public function index(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'view',
            $childLaborer
        );

        $employmentRecords = $childLaborer
            ->employmentRecords()
            ->withCount('workHazards')
            ->orderByDesc('is_current')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->get();

        return view(
            'child-laborers.employment-records.index',
            [
                'childLaborer' => $childLaborer,

                'employmentRecords' =>
                    $employmentRecords,

                'workTypes' =>
                    EmploymentRecord::workTypes(),

                'employmentArrangements' =>
                    EmploymentRecord::employmentArrangements(),

                'incomeFrequencies' =>
                    EmploymentRecord::incomeFrequencies(),
            ]
        );
    }

    public function store(
        EmploymentRecordRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        if ($validated['is_current']) {
            $validated['end_date'] = null;
        }

        $duplicateKey =
            EmploymentRecord::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey
        );

        DB::transaction(function () use ($childLaborer, $validated, $duplicateKey): void {
            $hasExistingRecord = $childLaborer
                ->employmentRecords()
                ->exists();

            $makeCurrent =
                !$hasExistingRecord
                || (bool) $validated['is_current'];

            if ($makeCurrent) {
                $childLaborer
                    ->employmentRecords()
                    ->update([
                        'is_current' => false,
                    ]);

                $validated['end_date'] = null;
            }

            $childLaborer
                ->employmentRecords()
                ->create([
                    ...$validated,

                    'is_current' =>
                        $makeCurrent,

                    'duplicate_key' =>
                        $duplicateKey,
                ]);
        });

        return back()->with(
            'success',
            'The employment record was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord
    ): View {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        return view(
            'child-laborers.employment-records.edit',
            [
                'childLaborer' => $childLaborer,

                'employmentRecord' =>
                    $employmentRecord,

                'workTypes' =>
                    EmploymentRecord::workTypes(),

                'employmentArrangements' =>
                    EmploymentRecord::employmentArrangements(),

                'incomeFrequencies' =>
                    EmploymentRecord::incomeFrequencies(),
            ]
        );
    }

    public function update(
        EmploymentRecordRequest $request,
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $validated = $request->validated();

        $makeCurrent = (bool) $validated[
            'is_current'
        ];

        if (
            $employmentRecord->is_current
            && !$makeCurrent
        ) {
            $makeCurrent = true;
        }

        if ($makeCurrent) {
            $validated['end_date'] = null;
        }

        $duplicateKey =
            EmploymentRecord::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey,
            $employmentRecord
        );

        DB::transaction(function () use ($childLaborer, $employmentRecord, $validated, $duplicateKey, $makeCurrent): void {
            if ($makeCurrent) {
                $childLaborer
                    ->employmentRecords()
                    ->where(
                        'id',
                        '!=',
                        $employmentRecord->id
                    )
                    ->update([
                        'is_current' => false,
                    ]);
            }

            $employmentRecord->update([
                ...$validated,

                'is_current' =>
                    $makeCurrent,

                'duplicate_key' =>
                    $duplicateKey,
            ]);
        });

        return redirect()
            ->route(
                'child-laborers.employment-records.index',
                $childLaborer
            )
            ->with(
                'success',
                'The employment record was updated successfully.'
            );
    }

    public function destroy(
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        DB::transaction(function () use ($childLaborer, $employmentRecord): void {
            $wasCurrent =
                $employmentRecord->is_current;

            $employmentRecord->delete();

            if ($wasCurrent) {
                $nextRecord = $childLaborer
                    ->employmentRecords()
                    ->orderByDesc('start_date')
                    ->orderByDesc('id')
                    ->first();

                if ($nextRecord) {
                    $nextRecord->update([
                        'is_current' => true,
                        'end_date' => null,
                    ]);
                }
            }
        });

        return back()->with(
            'success',
            'The employment record was removed successfully.'
        );
    }

    private function ensureNoDuplicate(
        ChildLaborer $childLaborer,
        string $duplicateKey,
        ?EmploymentRecord $ignoredRecord = null
    ): void {
        $duplicate = $childLaborer
            ->employmentRecords()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredRecord,
                fn($query) => $query->where(
                    'id',
                    '!=',
                    $ignoredRecord->id
                )
            )
            ->exists();

        if (!$duplicate) {
            return;
        }

        throw ValidationException::withMessages([
            'occupation' =>
                'This employment record already exists in the child laborer profile.',
        ]);
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord
    ): void {
        abort_unless(
            (int) $employmentRecord
                ->child_laborer_id
            === (int) $childLaborer->id,
            404
        );
    }
}