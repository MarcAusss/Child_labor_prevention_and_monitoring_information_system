<?php

namespace Tests\Feature\CLPMIS;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class AuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_viewer_cannot_open_administration_modules(): void
    {
        $viewer = $this->makeUser(
            'viewer'
        );

        $this->actingAs($viewer);

        foreach (
            [
                'users.index',
                'activity-logs.index',
                'audit-schedules.index',
                'security.status',
                'backups.index',
                'quality-assurance.index',
            ]
            as $routeName
        ) {
            if (! Route::has($routeName)) {
                $this->fail(
                    'Required route is missing: '
                    .$routeName
                );
            }

            $this->get(
                route($routeName)
            )->assertForbidden();
        }
    }

    public function test_profiling_officer_cannot_open_reports_or_administration(): void
    {
        $officer = $this->makeUser(
            'profiling-officer'
        );

        $this->actingAs($officer);

        foreach (
            [
                'reports.child-laborers.index',
                'reports.statistics.index',
                'users.index',
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

            $this->get(
                route($routeName)
            )->assertForbidden();
        }
    }

    public function test_admin_can_open_administration_modules(): void
    {
        $admin = $this->makeUser(
            'admin'
        );

        $this->actingAs($admin);

        foreach (
            [
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

            $this->get(
                route($routeName)
            )->assertOk();
        }
    }
}
