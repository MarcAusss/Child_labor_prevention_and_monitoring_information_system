<?php

namespace Tests\Feature\CLPMIS;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class ReportsSmokeTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_admin_can_open_report_pages(): void
    {
        $admin = $this->makeUser(
            'admin'
        );

        $this->actingAs($admin)
            ->get(
                route(
                    'reports.child-laborers.index'
                )
            )
            ->assertOk();

        $this->actingAs($admin)
            ->get(
                route(
                    'reports.statistics.index'
                )
            )
            ->assertOk();
    }

    public function test_viewer_can_open_permitted_report_pages(): void
    {
        $viewer = $this->makeUser(
            'viewer'
        );

        $this->actingAs($viewer)
            ->get(
                route(
                    'reports.child-laborers.index'
                )
            )
            ->assertOk();

        $this->actingAs($viewer)
            ->get(
                route(
                    'reports.statistics.index'
                )
            )
            ->assertOk();
    }
}
