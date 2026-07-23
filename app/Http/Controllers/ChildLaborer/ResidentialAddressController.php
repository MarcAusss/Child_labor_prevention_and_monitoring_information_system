<?php

namespace App\Http\Controllers\ChildLaborer;

use App\Http\Controllers\Controller;
use App\Http\Requests\ChildLaborer\UpsertResidentialAddressRequest;
use App\Models\ChildLaborer;
use App\Services\LocationHierarchyService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Arr;
use Illuminate\View\View;

class ResidentialAddressController extends Controller
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
            'residentialAddress.locality',
        ]);

        return view(
            'child-laborers.residential-address.edit',
            [
                'childLaborer' => $childLaborer,
                'residentialAddress' => $childLaborer->residentialAddress,
            ]
        );
    }

    public function update(
        UpsertResidentialAddressRequest $request,
        ChildLaborer $childLaborer,
        LocationHierarchyService $locationHierarchy
    ): RedirectResponse {
        $validated = $request->validated();

        $locationHierarchy->validate(
            $validated
        );

        $childLaborer->residentialAddress()
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
                'The residential address was saved successfully.'
            );
    }
}