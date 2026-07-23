<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class HealthInformationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route(
            'childLaborer'
        );

        return $childLaborer instanceof ChildLaborer
            && $this->user()?->can(
                'updateHealth',
                $childLaborer
            ) === true;
    }

    public function rules(): array
    {
        return [
            'assessment_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'health_condition' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'has_disability' => [
                'required',
                'boolean',
            ],

            'disability_details' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->boolean(
                            'has_disability'
                        )
                ),
                'nullable',
                'string',
                'max:3000',
            ],

            'injury_history' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'treatment_received' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'health_facility' => [
                'nullable',
                'string',
                'max:255',
            ],

            'current_complaints' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'mental_health_concerns' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],

            'is_current' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'assessment_date.before_or_equal' =>
                'The health assessment date cannot be in the future.',

            'disability_details.required' =>
                'Describe the reported disability.',
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            $hasInformation = collect([
                $this->input('health_condition'),
                $this->input('disability_details'),
                $this->input('injury_history'),
                $this->input('treatment_received'),
                $this->input('health_facility'),
                $this->input('current_complaints'),
                $this->input(
                    'mental_health_concerns'
                ),
                $this->input('remarks'),
            ])->contains(
                fn (mixed $value): bool =>
                    trim((string) $value) !== ''
            );

            if (! $hasInformation) {
                $validator->errors()->add(
                    'health_condition',
                    'Provide at least one health assessment detail.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $hasDisability = $this->boolean(
            'has_disability'
        );

        $this->merge([
            'health_condition' => $this->clean(
                $this->input('health_condition')
            ),

            'has_disability' => $hasDisability,

            'disability_details' => $hasDisability
                ? $this->clean(
                    $this->input(
                        'disability_details'
                    )
                )
                : null,

            'injury_history' => $this->clean(
                $this->input('injury_history')
            ),

            'treatment_received' => $this->clean(
                $this->input(
                    'treatment_received'
                )
            ),

            'health_facility' => $this->clean(
                $this->input('health_facility')
            ),

            'current_complaints' => $this->clean(
                $this->input(
                    'current_complaints'
                )
            ),

            'mental_health_concerns' =>
                $this->clean(
                    $this->input(
                        'mental_health_concerns'
                    )
                ),

            'remarks' => $this->clean(
                $this->input('remarks')
            ),

            'is_current' => $this->boolean(
                'is_current'
            ),
        ]);
    }

    private function clean(
        mixed $value
    ): ?string {
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