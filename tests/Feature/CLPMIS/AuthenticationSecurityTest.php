<?php

namespace Tests\Feature\CLPMIS;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class AuthenticationSecurityTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_guest_is_redirected_from_workspace(): void
    {
        $this->get(
            route('workspace.dashboard')
        )->assertRedirect(
            route('login')
        );
    }

    public function test_active_user_can_open_workspace(): void
    {
        $user = $this->makeUser(
            'viewer'
        );

        $this->actingAs($user)
            ->get(
                route(
                    'workspace.dashboard'
                )
            )
            ->assertOk();
    }

    public function test_inactive_user_is_signed_out(): void
    {
        if (! Schema::hasColumn(
            'users',
            'is_active'
        )) {
            $this->markTestSkipped(
                'The users table has no is_active column.'
            );
        }

        $user = $this->makeUser(
            'viewer',
            [
                'is_active' => false,
            ]
        );

        $this->actingAs($user)
            ->get(
                route(
                    'workspace.dashboard'
                )
            )
            ->assertRedirect(
                route('login')
            );

        $this->assertGuest();
    }
}
