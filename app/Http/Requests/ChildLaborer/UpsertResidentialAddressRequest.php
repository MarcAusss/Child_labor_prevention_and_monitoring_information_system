<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpsertResidentialAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route('childLaborer');

        return $childLaborer instanceof ChildLaborer
            && $this->user()?->can(
                'update',
                $childLaborer
            ) === true;
    }

    public function rules(): array
    {
        return [
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

            'house_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'street' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sitio_purok' => [
                'nullable',
                'string',
                'max:150',
            ],

            'postal_code' => [
                'nullable',
                'string',
                'max:20',
            ],

            'landmark' => [
                'nullable',
                'string',
                'max:255',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'province_id' => $this->normalizeNullableInteger(
                $this->input('province_id')
            ),

            'house_number' => $this->clean(
                $this->input('house_number')
            ),

            'street' => $this->clean(
                $this->input('street')
            ),

            'sitio_purok' => $this->clean(
                $this->input('sitio_purok')
            ),

            'postal_code' => $this->clean(
                $this->input('postal_code')
            ),

            'landmark' => $this->clean(
                $this->input('landmark')
            ),
        ]);
    }

    private function normalizeNullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
            || $value === '__REGIONAL__'
        ) {
            return null;
        }

        return is_numeric($value)
            ? (int) $value
            : null;
    }

    private function clean(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(
            preg_replace(
                '/\s+/',
                ' ',
                (string) $value
            ) ?? ''
        );

        return $value !== ''
            ? $value
            : null;
    }
}