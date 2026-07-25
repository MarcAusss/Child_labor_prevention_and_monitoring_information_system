<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class DefaultUserSeeder extends Seeder
{
    public const DEFAULT_PASSWORD =
        'Password123!';

    public function run(): void
    {
        $accounts = [
            [
                'role' => Role::SUPER_ADMIN,
                'name' => 'CLPMIS Super Administrator',
                'email' => 'superadmin@clpmis.test',
            ],
            [
                'role' => Role::ADMIN,
                'name' => 'CLPMIS Administrator',
                'email' => 'admin@clpmis.test',
            ],
            [
                'role' => Role::ADMIN,
                'name' => 'Audit and Review Administrator',
                'email' => 'admin.audit@clpmis.test',
            ],
            [
                'role' => Role::PROFILING_OFFICER,
                'name' => 'Maria Profiling Officer',
                'email' => 'profiling@clpmis.test',
            ],
            [
                'role' => Role::PROFILING_OFFICER,
                'name' => 'Jose Profiling Officer',
                'email' => 'profiling2@clpmis.test',
            ],
            [
                'role' => Role::VIEWER,
                'name' => 'CLPMIS Monitoring Viewer',
                'email' => 'viewer@clpmis.test',
            ],
        ];

        foreach ($accounts as $account) {
            $role = Role::query()
                ->where(
                    'slug',
                    $account['role']
                )
                ->firstOrFail();

            $values = [
                'role_id' => $role->id,
                'name' => $account['name'],
                'email_verified_at' => now(),
                'password' => Hash::make(
                    self::DEFAULT_PASSWORD
                ),
                'is_active' => true,
            ];

            if (Schema::hasColumn(
                'users',
                'password_changed_at'
            )) {
                $values['password_changed_at'] =
                    now();
            }

            User::query()->updateOrCreate(
                [
                    'email' => $account['email'],
                ],
                $values
            );
        }
    }
}
