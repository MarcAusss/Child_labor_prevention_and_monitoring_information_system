<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            DefaultUserSeeder::class,
        ]);

        /*
         * Demo records are intentionally restricted to local and testing
         * environments. Production seeding creates only roles and accounts.
         */
        if (app()->environment([
            'local',
            'testing',
        ])) {
            $this->call(
                CLPMISDemoSeeder::class
            );
        }
    }
}
