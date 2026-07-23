<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\HealthInformationRequest;
use App\Models\ChildLaborer;
use App\Models\HealthInformation;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class HealthInformationController extends Controller
{
    use AuthorizesRequests;

    public function index(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'viewHealth',
            $childLaborer
        );

        $healthInformationRecords = $childLaborer
            ->healthInformationRecords()
            ->orderByDesc('is_current')
            ->orderByDesc('assessment_date')
            ->orderByDesc('id')
            ->get();

        return view(
            'child-laborers.health-information.index',
            compact(
                'childLaborer',
                'healthInformationRecords'
            )
        );
    }

    public function store(
        HealthInformationRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        $duplicateKey =
            HealthInformation::makeDuplicateKey(
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
                ->healthInformationRecords()
                ->exists();

            $makeCurrent =
                ! $hasExistingRecord
                || (bool) $validated['is_current'];

            if ($makeCurrent) {
                $childLaborer
                    ->healthInformationRecords()
                    ->update([
                        'is_current' => false,
                    ]);
            }

            $childLaborer
                ->healthInformationRecords()
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
            'The health assessment was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        HealthInformation $healthInformation
    ): View {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $healthInformation
        );

        $this->authorize(
            'updateHealth',
            $childLaborer
        );

        return view(
            'child-laborers.health-information.edit',
            compact(
                'childLaborer',
                'healthInformation'
            )
        );
    }

    public function update(
        HealthInformationRequest $request,
        ChildLaborer $childLaborer,
        HealthInformation $healthInformation
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $healthInformation
        );

        $validated = $request->validated();

        $makeCurrent = (bool) $validated[
            'is_current'
        ];

        if (
            $healthInformation->is_current
            && ! $makeCurrent
        ) {
            $makeCurrent = true;
        }

        $duplicateKey =
            HealthInformation::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey,
            $healthInformation
        );

        DB::transaction(function () use (
            $validated,
            $duplicateKey,
            $makeCurrent,
            $childLaborer,
            $healthInformation
        ): void {
            if ($makeCurrent) {
                $childLaborer
                    ->healthInformationRecords()
                    ->where(
                        'id',
                        '!=',
                        $healthInformation->id
                    )
                    ->update([
                        'is_current' => false,
                    ]);
            }

            $healthInformation->update([
                ...$validated,

                'is_current' =>
                    $makeCurrent,

                'duplicate_key' =>
                    $duplicateKey,
            ]);
        });

        return redirect()
            ->route(
                'child-laborers.health-information.index',
                $childLaborer
            )
            ->with(
                'success',
                'The health assessment was updated successfully.'
            );
    }

    public function destroy(
        ChildLaborer $childLaborer,
        HealthInformation $healthInformation
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $healthInformation
        );

        $this->authorize(
            'updateHealth',
            $childLaborer
        );

        DB::transaction(function () use (
            $childLaborer,
            $healthInformation
        ): void {
            $wasCurrent =
                $healthInformation->is_current;

            $healthInformation->delete();

            if ($wasCurrent) {
                $nextRecord = $childLaborer
                    ->healthInformationRecords()
                    ->orderByDesc('assessment_date')
                    ->orderByDesc('id')
                    ->first();

                $nextRecord?->update([
                    'is_current' => true,
                ]);
            }
        });

        return back()->with(
            'success',
            'The health assessment was removed successfully.'
        );
    }

    private function ensureNoDuplicate(
        ChildLaborer $childLaborer,
        string $duplicateKey,
        ?HealthInformation $ignoredRecord = null
    ): void {
        $duplicate = $childLaborer
            ->healthInformationRecords()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredRecord,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $ignoredRecord->id
                )
            )
            ->exists();

        if (! $duplicate) {
            return;
        }

        throw ValidationException::withMessages([
            'health_condition' =>
                'This health assessment already exists in the child laborer profile.',
        ]);
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        HealthInformation $healthInformation
    ): void {
        abort_unless(
            (int) $healthInformation
                ->child_laborer_id
                === (int) $childLaborer->id,
            404
        );
    }
}