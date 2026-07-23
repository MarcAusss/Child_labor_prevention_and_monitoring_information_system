<?php

namespace App\Http\Controllers;

use App\Models\Locality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Http\JsonResponse;

class LocationLookupController extends Controller
{
    public function regions(): JsonResponse
    {
        $regions = Region::query()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'psgc_code',
                'name',
            ]);

        return response()->json([
            'data' => $regions,
        ]);
    }

    public function provinces(
        Region $region
    ): JsonResponse {
        abort_unless(
            $region->is_active,
            404
        );

        $provinces = $region->provinces()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'region_id',
                'psgc_code',
                'name',
            ]);

        return response()->json([
            'data' => $provinces,
        ]);
    }

    public function regionalLocalities(
        Region $region
    ): JsonResponse {
        abort_unless(
            $region->is_active,
            404
        );

        $localities = $region->localities()
            ->active()
            ->topLevel()
            ->whereNull('province_id')
            ->withCount([
                'children as active_children_count' => function ($query): void {
                    $query->active();
                },
            ])
            ->orderBy('name')
            ->get([
                'id',
                'region_id',
                'province_id',
                'parent_id',
                'psgc_code',
                'name',
                'geographic_level',
                'city_class',
            ]);

        return response()->json([
            'data' => $localities,
        ]);
    }

    public function provincialLocalities(
        Province $province
    ): JsonResponse {
        abort_unless(
            $province->is_active,
            404
        );

        $localities = $province->localities()
            ->active()
            ->topLevel()
            ->withCount([
                'children as active_children_count' => function ($query): void {
                    $query->active();
                },
            ])
            ->orderBy('name')
            ->get([
                'id',
                'region_id',
                'province_id',
                'parent_id',
                'psgc_code',
                'name',
                'geographic_level',
                'city_class',
            ]);

        return response()->json([
            'data' => $localities,
        ]);
    }

    public function childLocalities(
        Locality $locality
    ): JsonResponse {
        abort_unless(
            $locality->is_active,
            404
        );

        $children = $locality->children()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'region_id',
                'province_id',
                'parent_id',
                'psgc_code',
                'name',
                'geographic_level',
            ]);

        return response()->json([
            'data' => $children,
        ]);
    }

    public function barangays(
        Locality $locality
    ): JsonResponse {
        abort_unless(
            $locality->is_active,
            404
        );

        $barangays = $locality->barangays()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'region_id',
                'province_id',
                'locality_id',
                'psgc_code',
                'name',
                'urban_rural',
                'status',
            ]);

        return response()->json([
            'data' => $barangays,
        ]);
    }
}