<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\InterventionRequest;
use App\Models\ChildLaborer;
use App\Models\Intervention;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class InterventionController extends Controller
{
    use AuthorizesRequests;

    public function index(
        Request $request,
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'viewInterventions',
            $childLaborer
        );

        $status = trim(
            (string) $request->query(
                'status',
                ''
            )
        );

        $type = trim(
            (string) $request->query(
                'type',
                ''
            )
        );

        $interventions = $childLaborer
            ->interventions()
            ->with([
                'recorder:id,name,email',
                'lastUpdater:id,name,email',
            ])
            ->when(
                in_array(
                    $status,
                    Intervention::statuses(),
                    true
                ),
                fn ($query) => $query->where(
                    'status',
                    $status
                )
            )
            ->when(
                in_array(
                    $type,
                    Intervention::interventionTypes(),
                    true
                ),
                fn ($query) => $query->where(
                    'intervention_type',
                    $type
                )
            )
            ->orderByRaw(
                "
                CASE status
                    WHEN 'Ongoing' THEN 1
                    WHEN 'Pending' THEN 2
                    WHEN 'Completed' THEN 3
                    WHEN 'Discontinued' THEN 4
                    WHEN 'Cancelled' THEN 5
                    ELSE 6
                END
                "
            )
            ->orderByDesc('date_provided')
            ->orderByDesc('id')
            ->get();

        $allInterventions = $childLaborer
            ->interventions()
            ->get();

        return view(
            'child-laborers.interventions.index',
            [
                'childLaborer' => $childLaborer,

                'interventions' => $interventions,

                'allInterventions' =>
                    $allInterventions,

                'statuses' =>
                    Intervention::statuses(),

                'interventionTypes' =>
                    Intervention::interventionTypes(),

                'selectedStatus' => $status,

                'selectedType' => $type,
            ]
        );
    }

    public function store(
        InterventionRequest $request,
        ChildLaborer $childLaborer
    ): RedirectResponse {
        $validated = $request->validated();

        $duplicateKey =
            Intervention::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey
        );

        $childLaborer
            ->interventions()
            ->create([
                ...$validated,

                'created_by' =>
                    $request->user()->id,

                'updated_by' =>
                    $request->user()->id,

                'duplicate_key' =>
                    $duplicateKey,
            ]);

        return back()->with(
            'success',
            'The intervention was added successfully.'
        );
    }

    public function edit(
        ChildLaborer $childLaborer,
        Intervention $intervention
    ): View {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $intervention
        );

        $this->authorize(
            'manageInterventions',
            $childLaborer
        );

        return view(
            'child-laborers.interventions.edit',
            [
                'childLaborer' => $childLaborer,

                'intervention' => $intervention,

                'statuses' =>
                    Intervention::statuses(),

                'interventionTypes' =>
                    Intervention::interventionTypes(),
            ]
        );
    }

    public function update(
        InterventionRequest $request,
        ChildLaborer $childLaborer,
        Intervention $intervention
    ): RedirectResponse {
        $this->ensureBelongsToProfile(
            $childLaborer,
            $intervention
        );

        $validated = $request->validated();

        $duplicateKey =
            Intervention::makeDuplicateKey(
                $validated
            );

        $this->ensureNoDuplicate(
            $childLaborer,
            $duplicateKey,
            $intervention
        );

        $intervention->update([
            ...$validated,

            'updated_by' =>
                $request->user()->id,

            'duplicate_key' =>
                $duplicateKey,
        ]);

        return redirect()
            ->route(
                'child-laborers.interventions.index',
                $childLaborer
            )
            ->with(
                'success',
                'The intervention was updated successfully.'
            );
    }

    private function ensureNoDuplicate(
        ChildLaborer $childLaborer,
        string $duplicateKey,
        ?Intervention $ignoredIntervention = null
    ): void {
        $duplicate = $childLaborer
            ->interventions()
            ->where(
                'duplicate_key',
                $duplicateKey
            )
            ->when(
                $ignoredIntervention,
                fn ($query) => $query->where(
                    'id',
                    '!=',
                    $ignoredIntervention->id
                )
            )
            ->exists();

        if (! $duplicate) {
            return;
        }

        throw ValidationException::withMessages([
            'intervention_type' =>
                'This intervention already exists in the child laborer profile.',
        ]);
    }

    private function ensureBelongsToProfile(
        ChildLaborer $childLaborer,
        Intervention $intervention
    ): void {
        abort_unless(
            (int) $intervention
                ->child_laborer_id
                === (int) $childLaborer->id,
            404
        );
    }
}