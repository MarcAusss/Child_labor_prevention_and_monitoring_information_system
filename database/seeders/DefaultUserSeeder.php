<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DefaultUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            [
                'role' => Role::SUPER_ADMIN,
                'name' => 'CLPMIS Super Administrator',
                'email' => 'superadmin@clpmis.test',
                'password' => 'Password123!',
            ],
            [
                'role' => Role::ADMIN,
                'name' => 'CLPMIS Administrator',
                'email' => 'admin@clpmis.test',
                'password' => 'Password123!',
            ],
            [
                'role' => Role::PROFILING_OFFICER,
                'name' => 'Profiling Officer',
                'email' => 'profiling@clpmis.test',
                'password' => 'Password123!',
            ],
            [
                'role' => Role::VIEWER,
                'name' => 'CLPMIS Viewer',
                'email' => 'viewer@clpmis.test',
                'password' => 'Password123!',
            ],
        ];

        foreach ($accounts as $account) {
            $role = Role::query()
                ->where('slug', $account['role'])
                ->firstOrFail();

            User::query()->updateOrCreate(
                [
                    'email' => $account['email'],
                ],
                [
                    'role_id' => $role->id,
                    'name' => $account['name'],
                    'email_verified_at' => now(),
                    'password' => Hash::make(
                        $account['password']
                    ),
                    'is_active' => true,
                ]
            );
        }
    }
}