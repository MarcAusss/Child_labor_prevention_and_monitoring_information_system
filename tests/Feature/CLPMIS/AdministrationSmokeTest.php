<?php

namespace Tests\Feature\CLPMIS;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class AdministrationSmokeTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_admin_module_routes_render_without_server_errors(): void
    {
        $admin = $this->makeUser(
            'admin'
        );

        $this->actingAs($admin);

        foreach (
            [
                'workspace.dashboard',
                'notifications.index',
                'audit-schedules.index',
                'activity-logs.index',
                'users.index',
                'reports.child-laborers.index',
                'reports.statistics.index',
                'security.status',
                'backups.index',
                'quality-assurance.index',
            ]
            as $routeName
        ) {
            $this->assertTrue(
                Route::has($routeName),
                'Required route is missing: '
                .$routeName
            );

            $response = $this->get(
                route($routeName)
            );

            $this->assertLessThan(
                500,
                $response->status(),
                'Route returned a server error: '
                .$routeName
            );
        }
    }
}
