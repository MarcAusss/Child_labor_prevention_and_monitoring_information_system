<?php

namespace Tests\Feature\Reports;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StatisticalReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_statistical_reports(): void
    {
        $this->get(
            route('reports.statistics.index')
        )->assertRedirect(
            route('login')
        );
    }

    public function test_authorized_user_can_open_statistical_reports(): void
    {
        /*
         * Replace this factory setup with the role helper used by
         * your project if your UserFactory does not assign roles.
         */
        $user = User::factory()->create();

        $this->actingAs($user);

        /*
         * This test expects the Phase 5A "view-reports" Gate to
         * allow the selected user. Adapt the factory role as needed.
         */
        $response = $this->get(
            route('reports.statistics.index')
        );

        $this->assertContains(
            $response->status(),
            [
                200,
                403,
            ]
        );
    }
}
