<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use App\Models\EmploymentRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EmploymentRecordRequest extends FormRequest
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
            'employer_name' => [
                'nullable',
                'string',
                'max:255',
            ],

            'employer_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'work_type' => [
                'required',
                Rule::in(
                    EmploymentRecord::workTypes()
                ),
            ],

            'occupation' => [
                'required',
                'string',
                'max:200',
            ],

            'industry' => [
                'nullable',
                'string',
                'max:150',
            ],

            'employment_arrangement' => [
                'required',
                Rule::in(
                    EmploymentRecord::employmentArrangements()
                ),
            ],

            'start_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'end_date' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after_or_equal:start_date',
            ],

            'days_per_week' => [
                'required',
                'integer',
                'min:1',
                'max:7',
            ],

            'hours_per_day' => [
                'required',
                'numeric',
                'min:0.25',
                'max:24',
            ],

            'income_amount' => [
                'nullable',
                'numeric',
                'min:0',
                'max:999999999.99',
            ],

            'income_frequency' => [
                'required',
                Rule::in(
                    EmploymentRecord::incomeFrequencies()
                ),
            ],

            'is_current' => [
                'required',
                'boolean',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:3000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'days_per_week.max' =>
                'The number of workdays cannot exceed seven days per week.',

            'hours_per_day.max' =>
                'The working hours cannot exceed 24 hours per day.',

            'end_date.after_or_equal' =>
                'The employment end date cannot be earlier than the start date.',
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            $incomeFrequency = $this->input(
                'income_frequency'
            );

            $incomeAmount = $this->input(
                'income_amount'
            );

            if (
                $incomeFrequency !== 'Unpaid'
                && (
                    $incomeAmount === null
                    || $incomeAmount === ''
                )
            ) {
                $validator->errors()->add(
                    'income_amount',
                    'The income amount is required unless the work is unpaid.'
                );
            }

            if (
                $this->boolean('is_current')
                && $this->filled('end_date')
            ) {
                $validator->errors()->add(
                    'end_date',
                    'A current employment record must not have an end date.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $incomeFrequency = $this->clean(
            $this->input('income_frequency')
        );

        $incomeAmount = $this->normalizeMoney(
            $this->input('income_amount')
        );

        if ($incomeFrequency === 'Unpaid') {
            $incomeAmount = 0;
        }

        $this->merge([
            'employer_name' => $this->clean(
                $this->input('employer_name')
            ),

            'employer_address' => $this->clean(
                $this->input('employer_address')
            ),

            'occupation' => $this->clean(
                $this->input('occupation')
            ),

            'industry' => $this->clean(
                $this->input('industry')
            ),

            'employment_arrangement' => $this->clean(
                $this->input(
                    'employment_arrangement'
                )
            ),

            'income_frequency' => $incomeFrequency,

            'income_amount' => $incomeAmount,

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