<?php

namespace Tests\Feature\CLPMIS;

use App\Models\ChildLaborer;
use App\Services\Dashboard\WorkspaceDashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\CreatesClpmisRecords;
use Tests\TestCase;

class ProfileScopeTest extends TestCase
{
    use RefreshDatabase;
    use CreatesClpmisRecords;

    public function test_profiling_officer_only_receives_created_or_assigned_profiles(): void
    {
        $officer = $this->makeUser(
            'profiling-officer'
        );

        $otherOfficer = $this->makeUser(
            'profiling-officer'
        );

        $created = $this->makeChildLaborer(
            $officer
        );

        $assigned = $this->makeChildLaborer(
            $otherOfficer,
            [
                'assigned_to' =>
                    $officer->id,
            ]
        );

        $hidden = $this->makeChildLaborer(
            $otherOfficer
        );

        $ids = app(
            WorkspaceDashboardService::class
        )
            ->profileQuery($officer)
            ->pluck('id');

        $this->assertTrue(
            $ids->contains($created->id)
        );

        $this->assertTrue(
            $ids->contains($assigned->id)
        );

        $this->assertFalse(
            $ids->contains($hidden->id)
        );
    }

    public function test_viewer_only_receives_submitted_and_approved_profiles(): void
    {
        $admin = $this->makeUser(
            'admin'
        );

        $viewer = $this->makeUser(
            'viewer'
        );

        $draft = $this->makeChildLaborer(
            $admin,
            [
                'status' =>
                    ChildLaborer::STATUS_DRAFT,
            ]
        );

        $submitted = $this->makeChildLaborer(
            $admin,
            [
                'status' =>
                    ChildLaborer::STATUS_SUBMITTED,
            ]
        );

        $approved = $this->makeChildLaborer(
            $admin,
            [
                'status' =>
                    ChildLaborer::STATUS_APPROVED,
            ]
        );

        $ids = app(
            WorkspaceDashboardService::class
        )
            ->profileQuery($viewer)
            ->pluck('id');

        $this->assertFalse(
            $ids->contains($draft->id)
        );

        $this->assertTrue(
            $ids->contains($submitted->id)
        );

        $this->assertTrue(
            $ids->contains($approved->id)
        );
    }
}
