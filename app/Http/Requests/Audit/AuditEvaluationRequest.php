<?php

namespace App\Http\Requests\Audit;

use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AuditEvaluationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $auditSchedule = $this->route(
            'auditSchedule'
        );

        $auditEvaluation = $this->route(
            'auditEvaluation'
        );

        if (
            ! $auditSchedule
            instanceof AuditSchedule
        ) {
            return false;
        }

        if (
            $auditEvaluation
            instanceof AuditEvaluation
        ) {
            return $this->user()?->can(
                'update',
                $auditEvaluation
            ) === true;
        }

        return $this->user()?->can(
            'create',
            [
                AuditEvaluation::class,
                $auditSchedule,
            ]
        ) === true;
    }

    public function rules(): array
    {
        $requiresCompleteDetails =
            in_array(
                $this->input('status'),
                [
                    AuditEvaluation::STATUS_SUBMITTED,
                    AuditEvaluation::STATUS_FINALIZED,
                ],
                true
            );

        return [
            'evaluation_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'findings' => [
                Rule::requiredIf(
                    $requiresCompleteDetails
                ),
                'nullable',
                'string',
                'max:20000',
            ],

            'recommendations' => [
                Rule::requiredIf(
                    $requiresCompleteDetails
                ),
                'nullable',
                'string',
                'max:20000',
            ],

            'status' => [
                'required',
                Rule::in(
                    AuditEvaluation::statuses()
                ),
            ],
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            $hasFindings =
                trim(
                    (string) $this->input(
                        'findings'
                    )
                ) !== '';

            $hasRecommendations =
                trim(
                    (string) $this->input(
                        'recommendations'
                    )
                ) !== '';

            if (
                $this->input('status')
                    === AuditEvaluation::STATUS_DRAFT
                && ! $hasFindings
                && ! $hasRecommendations
            ) {
                $validator->errors()->add(
                    'findings',
                    'A draft evaluation must contain findings or recommendations.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'findings' => $this->clean(
                $this->input('findings')
            ),

            'recommendations' => $this->clean(
                $this->input(
                    'recommendations'
                )
            ),

            'status' => $this->clean(
                $this->input(
                    'status',
                    AuditEvaluation::STATUS_DRAFT
                )
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
                '/[ \t]+/',
                ' ',
                (string) $value
            ) ?? ''
        );

        return $value !== ''
            ? $value
            : null;
    }
}