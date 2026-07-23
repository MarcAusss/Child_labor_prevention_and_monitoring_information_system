<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use App\Models\EducationRecord;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class EducationRecordRequest extends FormRequest
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
            'enrollment_status' => [
                'required',
                Rule::in(
                    EducationRecord::enrollmentStatuses()
                ),
            ],

            'school_name' => [
                Rule::requiredIf(
                    fn (): bool => in_array(
                        $this->input('enrollment_status'),
                        [
                            EducationRecord::STATUS_ENROLLED,
                            EducationRecord::STATUS_COMPLETED,
                            EducationRecord::STATUS_GRADUATED,
                        ],
                        true
                    )
                ),
                'nullable',
                'string',
                'max:255',
            ],

            'grade_year_level' => [
                'nullable',
                'string',
                'max:100',
            ],

            'school_year' => [
                'nullable',
                'string',
                'max:20',
                'regex:/^\d{4}-\d{4}$/',
            ],

            'school_address' => [
                'nullable',
                'string',
                'max:500',
            ],

            'reason_not_attending' => [
                Rule::requiredIf(
                    fn (): bool => in_array(
                        $this->input('enrollment_status'),
                        [
                            EducationRecord::STATUS_NOT_ENROLLED,
                            EducationRecord::STATUS_DROPPED_OUT,
                        ],
                        true
                    )
                ),
                'nullable',
                'string',
                'max:2000',
            ],

            'last_grade_completed' => [
                'nullable',
                'string',
                'max:150',
            ],

            'date_enrolled' => [
                'nullable',
                'date',
                'before_or_equal:today',
            ],

            'date_ended' => [
                'nullable',
                'date',
                'before_or_equal:today',
                'after_or_equal:date_enrolled',
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
            'school_year.regex' =>
                'The school year must use the format YYYY-YYYY, such as 2025-2026.',

            'school_name.required' =>
                'The school name is required for the selected enrollment status.',

            'reason_not_attending.required' =>
                'Please provide the reason why the child is not attending school.',
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            $schoolYear = trim(
                (string) $this->input(
                    'school_year',
                    ''
                )
            );

            if (
                $schoolYear === ''
                || ! preg_match(
                    '/^(\d{4})-(\d{4})$/',
                    $schoolYear,
                    $matches
                )
            ) {
                return;
            }

            $startYear = (int) $matches[1];
            $endYear = (int) $matches[2];

            if ($endYear !== $startYear + 1) {
                $validator->errors()->add(
                    'school_year',
                    'The ending year must be one year after the starting year.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'school_name' => $this->clean(
                $this->input('school_name')
            ),

            'grade_year_level' => $this->clean(
                $this->input('grade_year_level')
            ),

            'school_year' => $this->clean(
                $this->input('school_year')
            ),

            'school_address' => $this->clean(
                $this->input('school_address')
            ),

            'reason_not_attending' => $this->clean(
                $this->input('reason_not_attending')
            ),

            'last_grade_completed' => $this->clean(
                $this->input('last_grade_completed')
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