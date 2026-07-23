<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Locality;
use App\Models\Province;
use App\Models\Region;
use Illuminate\Validation\ValidationException;

class LocationHierarchyService
{
    public function validate(array $data): void
    {
        $region = Region::query()
            ->active()
            ->find($data['region_id']);

        if (! $region) {
            throw ValidationException::withMessages([
                'region_id' => 'The selected region is invalid or inactive.',
            ]);
        }

        $province = null;

        if (! empty($data['province_id'])) {
            $province = Province::query()
                ->active()
                ->find($data['province_id']);

            if (! $province) {
                throw ValidationException::withMessages([
                    'province_id' => 'The selected province is invalid or inactive.',
                ]);
            }
        }

        $topLocality = Locality::query()
            ->active()
            ->find($data['top_locality_id']);

        if (! $topLocality) {
            throw ValidationException::withMessages([
                'top_locality_id' => 'The selected city or municipality is invalid.',
            ]);
        }

        $locality = Locality::query()
            ->active()
            ->find($data['locality_id']);

        if (! $locality) {
            throw ValidationException::withMessages([
                'locality_id' => 'The selected locality is invalid.',
            ]);
        }

        $barangay = Barangay::query()
            ->active()
            ->find($data['barangay_id']);

        if (! $barangay) {
            throw ValidationException::withMessages([
                'barangay_id' => 'The selected barangay is invalid or inactive.',
            ]);
        }

        $provinceId = $province?->id;

        $expectedTopLocalityId = $locality->parent_id
            ?: $locality->id;

        $isValid = (
            (int) $topLocality->region_id === (int) $region->id
            && (int) $locality->region_id === (int) $region->id
            && (int) $barangay->region_id === (int) $region->id

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

        if (! $isValid) {
            throw ValidationException::withMessages([
                'barangay_id' => 'The selected Region, Province, City or Municipality, and Barangay do not belong to the same PSGC hierarchy.',
            ]);
        }
    }

    private function sameNullableId(
        mixed $first,
        mixed $second
    ): bool {
        if ($first === null && $second === null) {
            return true;
        }

        if ($first === null || $second === null) {
            return false;
        }

        return (int) $first === (int) $second;
    }
}