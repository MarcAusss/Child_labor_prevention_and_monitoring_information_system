<?php

namespace Tests\Feature\CLPMIS;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CoreSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_core_tables_exist(): void
    {
        foreach (
            [
                'users',
                'roles',
                'child_laborers',
                'birth_information',
                'residential_addresses',
                'parent_guardians',
                'household_members',
                'education_records',
                'employment_records',
                'work_hazards',
                'health_information',
                'interventions',
                'child_laborer_documents',
                'audit_schedules',
                'audit_evaluations',
                'activity_logs',
                'notifications',
                'backup_runs',
            ]
            as $table
        ) {
            $this->assertTrue(
                Schema::hasTable($table),
                'Missing required table: '.$table
            );
        }
    }

    public function test_child_laborer_identity_and_workflow_columns_exist(): void
    {
        foreach (
            [
                'profile_number',
                'created_by',
                'assigned_to',
                'first_name',
                'last_name',
                'sex',
                'birth_date',
                'status',
            ]
            as $column
        ) {
            $this->assertTrue(
                Schema::hasColumn(
                    'child_laborers',
                    $column
                ),
                'Missing child_laborers column: '
                .$column
            );
        }
    }
}
