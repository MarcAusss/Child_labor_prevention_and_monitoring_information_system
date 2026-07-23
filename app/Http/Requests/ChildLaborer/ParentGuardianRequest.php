<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use Illuminate\Foundation\Http\FormRequest;

class ParentGuardianRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route(
            'childLaborer'
        );

        return $childLaborer instanceof ChildLaborer
            && $this->user()?->can(
                'update',
                $childLaborer
            ) === true;
    }

    public function rules(): array
    {
        return [
            'full_name' => [
                'required',
                'string',
                'max:200',
            ],

            'relationship' => [
                'required',
                'string',
                'max:100',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'occupation' => [
                'nullable',
                'string',
                'max:150',
            ],

            'educational_attainment' => [
                'nullable',
                'string',
                'max:150',
            ],

            'monthly_income' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'is_primary' => [
                'required',
                'boolean',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'full_name' => $this->clean(
                $this->input('full_name')
            ),

            'relationship' => $this->clean(
                $this->input('relationship')
            ),

            'contact_number' => $this->clean(
                $this->input('contact_number')
            ),

            'occupation' => $this->clean(
                $this->input('occupation')
            ),

            'educational_attainment' => $this->clean(
                $this->input(
                    'educational_attainment'
                )
            ),

            'monthly_income' => $this->normalizeMoney(
                $this->input('monthly_income')
            ),

            'is_primary' => $this->boolean(
                'is_primary'
            ),
        ]);
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

    private function normalizeMoney(
        mixed $value
    ): mixed {
        if (
            $value === null
            || trim((string) $value) === ''
        ) {
            return null;
        }

        return str_replace(
            ',',
            '',
            (string) $value
        );
    }
}