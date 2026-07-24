<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $fullName = fake()->name();

        $data = [
            'email' =>
                fake()->unique()->safeEmail(),

            'email_verified_at' =>
                now(),

            'password' =>
                static::$password
                    ??= Hash::make('Password123!'),

            'remember_token' =>
                Str::random(10),
        ];

        if ($this->hasUserColumn('name')) {
            $data['name'] = $fullName;
        }

        if ($this->hasUserColumn('first_name')) {
            $data['first_name'] =
                fake()->firstName();
        }

        if ($this->hasUserColumn('last_name')) {
            $data['last_name'] =
                fake()->lastName();
        }

        if ($this->hasUserColumn('username')) {
            $data['username'] =
                fake()->unique()->userName();
        }

        if ($this->hasUserColumn('role_id')) {
            $data['role_id'] =
                $this->roleId('viewer');
        }

        if ($this->hasUserColumn('is_active')) {
            $data['is_active'] = true;
        }

        if (
            $this->hasUserColumn(
                'password_changed_at'
            )
        ) {
            $data['password_changed_at'] =
                now();
        }

        return $data;
    }

    public function unverified(): static
    {
        return $this->state(
            fn (): array => [
                'email_verified_at' => null,
            ]
        );
    }

    public function inactive(): static
    {
        return $this->state(
            function (): array {
                return $this->hasUserColumn(
                    'is_active'
                )
                    ? ['is_active' => false]
                    : [];
            }
        );
    }

    public function superAdmin(): static
    {
        return $this->forRole(
            'super-admin'
        );
    }

    public function admin(): static
    {
        return $this->forRole(
            'admin'
        );
    }

    public function profilingOfficer(): static
    {
        return $this->forRole(
            'profiling-officer'
        );
    }

    public function viewer(): static
    {
        return $this->forRole(
            'viewer'
        );
    }

    public function forRole(
        string $roleName
    ): static {
        return $this->state(
            function () use (
                $roleName
            ): array {
                if (! $this->hasUserColumn(
                    'role_id'
                )) {
                    return [];
                }

                return [
                    'role_id' =>
                        $this->roleId(
                            $roleName
                        ),
                ];
            }
        );
    }

    private function roleId(
        string $roleName
    ): int {
        if (! Schema::hasTable('roles')) {
            throw new \RuntimeException(
                'The roles table must exist before creating users.'
            );
        }

        $nameColumn =
            $this->firstExistingColumn(
                'roles',
                [
                    'name',
                    'slug',
                    'role_name',
                ]
            );

        if (! $nameColumn) {
            throw new \RuntimeException(
                'No supported role-name column was found.'
            );
        }

        $existing = DB::table('roles')
            ->where(
                $nameColumn,
                $roleName
            )
            ->value('id');

        if ($existing) {
            return (int) $existing;
        }

        $payload = [
            $nameColumn => $roleName,
        ];

        if (
            Schema::hasColumn(
                'roles',
                'name'
            )
            && $nameColumn !== 'name'
        ) {
            $payload['name'] =
                $roleName;
        }

        if (
            Schema::hasColumn(
                'roles',
                'slug'
            )
            && $nameColumn !== 'slug'
        ) {
            $payload['slug'] =
                $roleName;
        }

        if (
            Schema::hasColumn(
                'roles',
                'description'
            )
        ) {
            $payload['description'] =
                Str::headline($roleName);
        }

        if (
            Schema::hasColumn(
                'roles',
                'is_active'
            )
        ) {
            $payload['is_active'] =
                true;
        }

        if (
            Schema::hasColumn(
                'roles',
                'created_at'
            )
        ) {
            $payload['created_at'] =
                now();
        }

        if (
            Schema::hasColumn(
                'roles',
                'updated_at'
            )
        ) {
            $payload['updated_at'] =
                now();
        }

        return (int) DB::table('roles')
            ->insertGetId($payload);
    }

    private function hasUserColumn(
        string $column
    ): bool {
        return Schema::hasTable('users')
            && Schema::hasColumn(
                'users',
                $column
            );
    }

    private function firstExistingColumn(
        string $table,
        array $columns
    ): ?string {
        foreach ($columns as $column) {
            if (
                Schema::hasColumn(
                    $table,
                    $column
                )
            ) {
                return $column;
            }
        }

        return null;
    }
}
