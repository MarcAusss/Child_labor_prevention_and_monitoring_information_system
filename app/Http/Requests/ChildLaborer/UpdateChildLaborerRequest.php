<?php

namespace App\Http\Requests\ChildLaborer;

use App\Models\ChildLaborer;
use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChildLaborerRequest extends FormRequest
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
        $profilingOfficerRoleId = Role::query()
            ->where(
                'slug',
                Role::PROFILING_OFFICER
            )
            ->value('id');

        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'suffix' => [
                'nullable',
                'string',
                'max:20',
            ],

            'sex' => [
                'required',
                Rule::in([
                    'Male',
                    'Female',
                ]),
            ],

            'birth_date' => [
                'required',
                'date',
                'before_or_equal:today',
            ],

            'civil_status' => [
                'nullable',
                'string',
                'max:50',
            ],

            'nationality' => [
                'required',
                'string',
                'max:100',
            ],

            'religion' => [
                'nullable',
                'string',
                'max:150',
            ],

            'contact_number' => [
                'nullable',
                'string',
                'max:30',
            ],

            'assigned_to' => [
                'nullable',
                'integer',

                Rule::exists('users', 'id')
                    ->where(function ($query) use (
                        $profilingOfficerRoleId
                    ): void {
                        $query
                            ->where('is_active', true)
                            ->where(
                                'role_id',
                                $profilingOfficerRoleId
                            );
                    }),
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'first_name' => $this->clean(
                $this->input('first_name')
            ),

            'middle_name' => $this->clean(
                $this->input('middle_name')
            ),

            'last_name' => $this->clean(
                $this->input('last_name')
            ),

            'suffix' => $this->clean(
                $this->input('suffix')
            ),

            'civil_status' => $this->clean(
                $this->input('civil_status')
            ),

            'nationality' => $this->clean(
                $this->input(
                    'nationality',
                    'Filipino'
                )
            ),

            'religion' => $this->clean(
                $this->input('religion')
            ),

            'contact_number' => $this->clean(
                $this->input('contact_number')
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
}