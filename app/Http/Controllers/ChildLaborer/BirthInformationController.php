<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\UpsertBirthInformationRequest;
use App\Models\ChildLaborer;
use App\Services\LocationHierarchyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class BirthInformationController extends Controller
{
    use AuthorizesRequests;

    public function edit(
        ChildLaborer $childLaborer
    ): View {
        $this->authorize(
            'update',
            $childLaborer
        );

        $childLaborer->load([
            'birthInformation.locality',
        ]);

        return view(
            'child-laborers.birth-information.edit',
            [
                'childLaborer' => $childLaborer,
                'birthInformation' => $childLaborer->birthInformation,
            ]
        );
    }

    public function update(
        UpsertBirthInformationRequest $request,
        ChildLaborer $childLaborer,
        LocationHierarchyService $locationHierarchy
    ): RedirectResponse {
        $validated = $request->validated();

        $locationHierarchy->validate(
            $validated
        );

        $childLaborer->birthInformation()
            ->updateOrCreate(
                [],
                Arr::except(
                    $validated,
                    'top_locality_id'
                )
            );

        return redirect()
            ->route(
                'child-laborers.show',
                $childLaborer
            )
            ->with(
                'success',
                'The birth information was saved successfully.'
            );
    }
}