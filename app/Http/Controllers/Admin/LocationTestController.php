<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Barangay;
use App\Models\Locality;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LocationTestController extends Controller
{
    public function create(): View
    {
        return view('admin.location-test');
    }

    public function store(
        Request $request
    ): RedirectResponse {
        $validated = $request->validate([
            'region_id' => [
                'required',
                'integer',

                Rule::exists('regions', 'id')
                    ->where('is_active', true),
            ],

            'province_id' => [
                'nullable',
                'integer',

                Rule::exists('provinces', 'id')
                    ->where('is_active', true),
            ],

            'top_locality_id' => [
                'required',
                'integer',

                Rule::exists('localities', 'id')
                    ->where('is_active', true),
            ],

            'locality_id' => [
                'required',
                'integer',

                Rule::exists('localities', 'id')
                    ->where('is_active', true),
            ],

            'barangay_id' => [
                'required',
                'integer',

                Rule::exists('barangays', 'id')
                    ->where('is_active', true),
            ],
        ]);

        $topLocality = Locality::query()
            ->active()
            ->findOrFail(
                $validated['top_locality_id']
            );

        $locality = Locality::query()
            ->active()
            ->findOrFail(
                $validated['locality_id']
            );

        $barangay = Barangay::query()
            ->active()
            ->findOrFail(
                $validated['barangay_id']
            );

        $provinceId = isset(
            $validated['province_id']
        )
            ? (int) $validated['province_id']
            : null;

        $expectedTopLocalityId =
            $locality->parent_id
                ?: $locality->id;

        $isValidHierarchy = (
            (int) $topLocality->region_id
                === (int) $validated['region_id']

            && (int) $locality->region_id
                === (int) $validated['region_id']

            && (int) $barangay->region_id
                === (int) $validated['region_id']

            && $this->sameNullableId(
                $topLocality->province_id,
                $provinceId
            )

            && $this->sameNullableId(
                $locality->province_id,
                $provinceId
            )

            && $this->sameNullableId(
                $barangay->province_id,
                $provinceId
            )

            && (int) $expectedTopLocalityId
                === (int) $topLocality->id

            && (int) $barangay->locality_id
                === (int) $locality->id
        );

        if (! $isValidHierarchy) {
            throw ValidationException::withMessages([
                'barangay_id' => 'The selected location hierarchy is invalid. Please select the location again.',
            ]);
        }

        $displayParts = collect([
            $barangay->name,
            $locality->name,

            $locality->province?->name,

            $locality->region?->name,
        ])
            ->filter()
            ->implode(', ');

        return back()
            ->withInput()
            ->with(
                'success',
                "Valid PSGC location selected: {$displayParts}"
            );
    }

    private function sameNullableId(
        mixed $first,
        mixed $second
    ): bool {
        if ($first === null && $second === null) {
            return true;
        }

        return (int) $first === (int) $second;
    }
}