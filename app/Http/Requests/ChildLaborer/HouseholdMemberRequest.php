<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HouseholdMemberRequest extends FormRequest
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

            'sex' => [
                'required',

                Rule::in([
                    'Male',
                    'Female',
                ]),
            ],

            'birth_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'civil_status' => [
                'nullable',
                'string',
                'max:50',
            ],

            'educational_attainment' => [
                'nullable',
                'string',
                'max:150',
            ],

            'occupation' => [
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

            'civil_status' => $this->clean(
                $this->input('civil_status')
            ),

            'educational_attainment' => $this->clean(
                $this->input(
                    'educational_attainment'
                )
            ),

            'occupation' => $this->clean(
                $this->input('occupation')
            ),

            'monthly_income' => $this->normalizeMoney(
                $this->input('monthly_income')
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