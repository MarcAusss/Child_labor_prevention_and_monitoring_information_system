<?php

namespace App\Http\Requests\Audit;

use App\Models\AuditSchedule;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AuditScheduleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $auditSchedule = $this->route(
            'auditSchedule'
        );

        if ($auditSchedule instanceof AuditSchedule) {
            return $this->user()?->can(
                'update',
                $auditSchedule
            ) === true;
        }

        return $this->user()?->can(
            'create',
            AuditSchedule::class
        ) === true;
    }

    public function rules(): array
    {
        return [
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id'),
            ],

            'scheduled_at' => [
                'required',
                'date',
            ],

            'location' => [
                'nullable',
                'string',
                'max:500',
            ],

            'status' => [
                'required',
                Rule::in(
                    AuditSchedule::editableStatuses()
                ),
            ],

            'remarks' => [
                Rule::requiredIf(
                    fn (): bool =>
                        $this->input('status')
                        === AuditSchedule::STATUS_CANCELLED
                ),
                'nullable',
                'string',
                'max:5000',
            ],
        ];
    }

    public function withValidator(
        Validator $validator
    ): void {
        $validator->after(function (
            Validator $validator
        ): void {
            $assignedUser = User::query()
                ->with('role')
                ->find(
                    $this->integer(
                        'assigned_to'
                    )
                );

            if (
                ! $assignedUser
                || ! $assignedUser->is_active
                || (
                    ! $assignedUser->isAdmin()
                    && ! $assignedUser->isSuperAdmin()
                )
            ) {
                $validator->errors()->add(
                    'assigned_to',
                    'The assigned evaluator must be an active Admin or Super Admin.'
                );
            }

            if (
                $this->input('status')
                !== AuditSchedule::STATUS_SCHEDULED
            ) {
                return;
            }

            if (! $this->filled('scheduled_at')) {
                return;
            }

            $auditSchedule = $this->route(
                'auditSchedule'
            );

            $scheduledAt = Carbon::parse(
                $this->input('scheduled_at')
            );

            $dateChanged =
                ! $auditSchedule instanceof AuditSchedule
                || ! $auditSchedule->scheduled_at
                    ->equalTo($scheduledAt);

            if (
                $dateChanged
                && $scheduledAt->isBefore(
                    today()
                )
            ) {
                $validator->errors()->add(
                    'scheduled_at',
                    'A scheduled audit cannot use a past date.'
                );
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'location' => $this->clean(
                $this->input('location')
            ),

            'status' => $this->clean(
                $this->input(
                    'status',
                    AuditSchedule::STATUS_SCHEDULED
                )
            ),

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
}