<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use App\Models\Intervention;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class InterventionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $childLaborer = $this->route(
            'childLaborer'
        );

        return $childLaborer
                instanceof ChildLaborer
            && $this->user()?->can(
                'manageInterventions',
                $childLaborer
            ) === true;
    }

    public function rules(): array
    {
        return [
            'intervention_type' => [
                'required',

                Rule::in(
                    Intervention::interventionTypes()
                ),
            ],

            'provider' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],

            'date_provided' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'date_completed' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after_or_equal:date_provided',
            ],

            'amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'status' => [
                'required',

                Rule::in(
                    Intervention::statuses()
                ),
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'date_provided.before_or_equal' =>
                'The date provided cannot be in the future.',

            'date_completed.before_or_equal' =>
                'The completion date cannot be in the future.',

            'date_completed.after_or_equal' =>
                'The completion date cannot be earlier than the date provided.',
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            $status = $this->input('status');

            if (
                in_array(
                    $status,
                    [
                        Intervention::STATUS_ONGOING,
                        Intervention::STATUS_COMPLETED,
                        Intervention::STATUS_DISCONTINUED,
                    ],
                    true
                )
                && ! $this->filled('date_provided')
            ) {
                $validator->errors()->add(
                    'date_provided',
                    'The date provided is required for an ongoing, completed, or discontinued intervention.'
                );
            }

            if (
                $status
                    === Intervention::STATUS_COMPLETED
                && ! $this->filled('date_completed')
            ) {
                $validator->errors()->add(
                    'date_completed',
                    'The completion date is required for a completed intervention.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $status = $this->clean(
            $this->input('status')
        );

        $dateCompleted =
            $this->input('date_completed');

        if (
            $status
            !== Intervention::STATUS_COMPLETED
        ) {
            $dateCompleted = null;
        }

        $this->merge([
            'intervention_type' => $this->clean(
                $this->input(
                    'intervention_type'
                )
            ),

            'provider' => $this->clean(
                $this->input('provider')
            ),

            'description' => $this->clean(
                $this->input('description')
            ),

            'date_completed' => $dateCompleted,

            'amount' => $this->normalizeMoney(
                $this->input('amount')
            ),

            'status' => $status,

            'remarks' => $this->clean(
                $this->input('remarks')
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