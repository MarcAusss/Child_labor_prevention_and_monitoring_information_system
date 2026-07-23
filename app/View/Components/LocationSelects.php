<?php

namespace App\View\Components;

use App\Models\Region;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\Component;

class LocationSelects extends Component
{
    public Collection $regions;

    public string $idPrefix;

    public ?int $selectedRegionId;

    public ?int $selectedProvinceId;

    public ?int $selectedTopLocalityId;

    public ?int $selectedLocalityId;

    public ?int $selectedBarangayId;

    public bool $required;

    public function __construct(
        mixed $selectedRegionId = null,
        mixed $selectedProvinceId = null,
        mixed $selectedTopLocalityId = null,
        mixed $selectedLocalityId = null,
        mixed $selectedBarangayId = null,
        bool $required = true,
        ?string $idPrefix = null
    ) {
        $this->selectedRegionId = $this->nullableInteger(
            $selectedRegionId
        );

        $this->selectedProvinceId = $this->nullableInteger(
            $selectedProvinceId
        );

        $this->selectedTopLocalityId = $this->nullableInteger(
            $selectedTopLocalityId
        );

        $this->selectedLocalityId = $this->nullableInteger(
            $selectedLocalityId
        );

        $this->selectedBarangayId = $this->nullableInteger(
            $selectedBarangayId
        );

        $this->required = $required;

        $this->idPrefix = $idPrefix
            ?: 'location-'.Str::lower(Str::random(8));

        $this->regions = Region::query()
            ->active()
            ->orderBy('name')
            ->get([
                'id',
                'psgc_code',
                'name',
            ]);
    }

    public function render(): View
    {
        return view('components.location-selects');
    }

    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
            || ! is_numeric($value)
        ) {
            return null;
        }

        return (int) $value;
    }
}