<?php

namespace Tests\Support;

use App\Models\ChildLaborer;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

trait CreatesClpmisRecords
{
    protected function makeUser(
        string $role,
        array $attributes = []
    ): User {
        $factory = User::factory()
            ->forRole($role);

        return $factory->create(
            $attributes
        );
    }

    protected function makeChildLaborer(
        User $creator,
        array $attributes = []
    ): ChildLaborer {
        $defaults = [
            'profile_number' =>
                'CL-TEST-'
                .strtoupper(
                    Str::random(10)
                ),

            'created_by' =>
                $creator->id,

            'assigned_to' =>
                $creator->id,

            'first_name' =>
                fake()->firstName(),

            'middle_name' =>
                fake()->optional()->firstName(),

            'last_name' =>
                fake()->lastName(),

            'suffix' =>
                null,

            'sex' =>
                fake()->randomElement([
                    'Male',
                    'Female',
                ]),

            'birth_date' =>
                now()
                    ->subYears(
                        fake()->numberBetween(
                            8,
                            17
                        )
                    )
                    ->subDays(
                        fake()->numberBetween(
                            0,
                            300
                        )
                    )
                    ->format('Y-m-d'),

            'civil_status' =>
                'Single',

            'nationality' =>
                'Filipino',

            'status' =>
                ChildLaborer::STATUS_DRAFT,
        ];

        if (
            Schema::hasColumn(
                'child_laborers',
                'duplicate_key'
            )
        ) {
            $defaults['duplicate_key'] =
                hash(
                    'sha256',
                    Str::uuid()->toString()
                );
        }

        return ChildLaborer::unguarded(
            fn (): ChildLaborer =>
                ChildLaborer::query()
                    ->create([
                        ...$defaults,
                        ...$attributes,
                    ])
        );
    }
}
