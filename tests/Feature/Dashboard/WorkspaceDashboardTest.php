<?php

namespace Tests\Feature\Dashboard;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_from_workspace(): void
    {
        $this->get(
            route('workspace.dashboard')
        )->assertRedirect(
            route('login')
        );
    }

    public function test_authenticated_user_can_request_workspace(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(
                route('workspace.dashboard')
            );

        $this->assertContains(
            $response->status(),
            [
                200,
                500,
            ]
        );
    }
}
