<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->canManageUsers() === true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email'),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::in($this->allowedRoleIds()),
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.in' => 'You are not permitted to assign the selected role.',
            'password.confirmed' => 'The password confirmation does not match.',
        ];
    }

    private function allowedRoleIds(): array
    {
        $query = Role::query()
            ->where('is_active', true);

        if ($this->user()?->isAdmin()) {
            $query->whereIn('slug', [
                Role::PROFILING_OFFICER,
                Role::VIEWER,
            ]);
        }

        return $query
            ->pluck('id')
            ->all();
    }
}