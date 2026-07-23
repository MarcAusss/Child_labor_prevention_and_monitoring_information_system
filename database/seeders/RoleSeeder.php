<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => Role::SUPER_ADMIN,
                'description' => 'Has complete access to all system modules, settings, users, and records.',
                'is_active' => true,
            ],
            [
                'name' => 'Admin',
                'slug' => Role::ADMIN,
                'description' => 'Manages users, profiles, interventions, audits, documents, and reports.',
                'is_active' => true,
            ],
            [
                'name' => 'Profiling Officer',
                'slug' => Role::PROFILING_OFFICER,
                'description' => 'Creates and maintains child laborer profiles and intervention records.',
                'is_active' => true,
            ],
            [
                'name' => 'Viewer',
                'slug' => Role::VIEWER,
                'description' => 'Has read-only access to authorized records and reports.',
                'is_active' => true,
            ],
        ];

        foreach ($roles as $role) {
            Role::query()->updateOrCreate(
                ['slug' => $role['slug']],
                $role
            );
        }
    }
}