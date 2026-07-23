<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\WorkHazardRequest;
use App\Models\ChildLaborer;
use App\Models\EmploymentRecord;
use App\Models\WorkHazard;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WorkHazardController extends Controller
{
    use AuthorizesRequests;

    public function index(
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord
    ): View {
        $this->ensureEmploymentBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $this->authorize(
            'view',
            $childLaborer
        );

        $workHazards = $employmentRecord
            ->workHazards()
            ->latest()
            ->get();

        return view(
            'child-laborers.work-hazards.index',
            [
                'childLaborer' => $childLaborer,

                'employmentRecord' =>
                    $employmentRecord,

                'workHazards' => $workHazards,

                'hazardTypes' =>
                    WorkHazard::hazardTypes(),

                'exposureFrequencies' =>
                    WorkHazard::exposureFrequencies(),
            ]
        );
    }

    public function store(
        WorkHazardRequest $request,
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord
    ): RedirectResponse {
        $this->ensureEmploymentBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $validated = $request->validated();

        $duplicateKey =
            WorkHazard::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $employmentRecord,
            $duplicateKey
        );

        $employmentRecord
            ->workHazards()
            ->create([
                ...$validated,

                'duplicate_key' =>
                    $duplicateKey,
            ]);

        return back()->with(
            'success',
            'The work hazard was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord,
        WorkHazard $workHazard
    ): View {
        $this->ensureEmploymentBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $this->ensureHazardBelongsToEmployment(
            $employmentRecord,
            $workHazard
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        return view(
            'child-laborers.work-hazards.edit',
            [
                'childLaborer' => $childLaborer,

                'employmentRecord' =>
                    $employmentRecord,

                'workHazard' =>
                    $workHazard,

                'hazardTypes' =>
                    WorkHazard::hazardTypes(),

                'exposureFrequencies' =>
                    WorkHazard::exposureFrequencies(),
            ]
        );
    }

    public function update(
        WorkHazardRequest $request,
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord,
        WorkHazard $workHazard
    ): RedirectResponse {
        $this->ensureEmploymentBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $this->ensureHazardBelongsToEmployment(
            $employmentRecord,
            $workHazard
        );

        $validated = $request->validated();

        $duplicateKey =
            WorkHazard::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $employmentRecord,
            $duplicateKey,
            $workHazard
        );

        $workHazard->update([
            ...$validated,

            'duplicate_key' =>
                $duplicateKey,
        ]);

        return redirect()
            ->route(
                'child-laborers.work-hazards.index',
                [
                    $childLaborer,
                    $employmentRecord,
                ]
            )
            ->with(
                'success',
                'The work hazard was updated successfully.'
            );
    }

    public function destroy(
        ChildLaborer $childLaborer,
        EmploymentRecord $employmentRecord,
        WorkHazard $workHazard
    ): RedirectResponse {
        $this->ensureEmploymentBelongsToProfile(
            $childLaborer,
            $employmentRecord
        );

        $this->ensureHazardBelongsToEmployment(
            $employmentRecord,
            $workHazard
        );

        $this->authorize(
            'update',
            $childLaborer
        );

        $workHazard->delete();

        return back()->with(
            'success',
            'The work hazard was removed successfully.'
        );
    }

    private function ensureNoDuplicate(
        EmploymentRecord $employmentRecord,
        string $duplicateKey,
        ?WorkHazard $ignoredHazard = null
    ): void {
        $duplicate = $employmentRecord
            ->workHazards()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredHazard,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $ignoredHazard->id
                )
            )
            ->exists();

        if (! $duplicate) {
            return;
        }

        throw ValidationException::withMessages([
            'hazard_type' =>
                'This work hazard already exists for the selected employment record.',
        ]);
    }

    private function ensureEmploymentBelongsToProfile(
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

    private function ensureHazardBelongsToEmployment(
        EmploymentRecord $employmentRecord,
        WorkHazard $workHazard
    ): void {
        abort_unless(
            (int) $workHazard
                ->employment_record_id
                === (int) $employmentRecord->id,
            404
        );
    }
}