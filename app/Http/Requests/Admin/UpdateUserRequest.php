<?php

namespace App\Http\Requests\Admin;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $actor = $this->user();
        $target = $this->route('user');

        if (! $actor || ! $target instanceof User) {
            return false;
        }

        if ($actor->isSuperAdmin()) {
            return true;
        }

        return $actor->isAdmin()
            && $target->hasAnyRole([
                Role::PROFILING_OFFICER,
                Role::VIEWER,
            ]);
    }

    public function rules(): array
    {
        /** @var User $target */
        $target = $this->route('user');

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
                Rule::unique('users', 'email')
                    ->ignore($target),
            ],

            'role_id' => [
                'required',
                'integer',
                Rule::in($this->allowedRoleIds()),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'can_import_child_laborers' => [
                'nullable',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'role_id.in' => 'You are not permitted to assign the selected role.',
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
