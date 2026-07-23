<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use App\Models\EmploymentRecord;
use App\Models\WorkHazard;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class WorkHazardRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route(
            'childLaborer'
        );

        $employmentRecord = $this->route(
            'employmentRecord'
        );

        if (
            ! $childLaborer instanceof ChildLaborer
            || ! $employmentRecord instanceof EmploymentRecord
        ) {
            return false;
        }

        if (
            (int) $employmentRecord->child_laborer_id
            !== (int) $childLaborer->id
        ) {
            return false;
        }

        return $this->user()?->can(
            'update',
            $childLaborer
        ) === true;
    }

    public function rules(): array
    {
        return [
            'hazard_type' => [
                'required',

                Rule::in(
                    WorkHazard::hazardTypes()
                ),
            ],

            'hazard_description' => [
                'required',
                'string',
                'max:3000',
            ],

            'exposure_frequency' => [
                'required',

                Rule::in(
                    WorkHazard::exposureFrequencies()
                ),
            ],

            'equipment_machinery' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'chemicals_substances' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'heavy_work' => [
                'required',
                'boolean',
            ],

            'long_hours' => [
                'required',
                'boolean',
            ],

            'night_work' => [
                'required',
                'boolean',
            ],

            'unsafe_conditions' => [
                'required',
                'boolean',
            ],

            'ppe_provided' => [
                'required',
                'boolean',
            ],

            'ppe_description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'injuries_incidents' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            if (
                $this->boolean('ppe_provided')
                && ! $this->filled('ppe_description')
            ) {
                $validator->errors()->add(
                    'ppe_description',
                    'Describe the personal protective equipment provided to the child.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $ppeProvided = $this->boolean(
            'ppe_provided'
        );

        $this->merge([
            'hazard_description' => $this->clean(
                $this->input('hazard_description')
            ),

            'equipment_machinery' => $this->clean(
                $this->input('equipment_machinery')
            ),

            'chemicals_substances' => $this->clean(
                $this->input('chemicals_substances')
            ),

            'heavy_work' => $this->boolean(
                'heavy_work'
            ),

            'long_hours' => $this->boolean(
                'long_hours'
            ),

            'night_work' => $this->boolean(
                'night_work'
            ),

            'unsafe_conditions' => $this->boolean(
                'unsafe_conditions'
            ),

            'ppe_provided' => $ppeProvided,

            'ppe_description' => $ppeProvided
                ? $this->clean(
                    $this->input('ppe_description')
                )
                : null,

            'injuries_incidents' => $this->clean(
                $this->input('injuries_incidents')
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