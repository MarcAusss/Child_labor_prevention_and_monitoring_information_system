<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuditScheduleController;
use App\Http\Controllers\ChildLaborer\DocumentController;
use App\Http\Controllers\ChildLaborer\HealthInformationController;
use App\Http\Controllers\ChildLaborer\InterventionController;
use App\Http\Controllers\ChildLaborer\WorkHazardController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationLookupController;
use App\Http\Controllers\ChildLaborerController;
use App\Http\Controllers\ChildLaborer\BirthInformationController;
use App\Http\Controllers\ChildLaborer\ResidentialAddressController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilingOfficer\DashboardController as ProfilingOfficerDashboardController;
use App\Http\Controllers\Reports\ChildLaborerReportController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\Viewer\DashboardController as ViewerDashboardController;
use App\Http\Controllers\Admin\LocationTestController;
use App\Http\Controllers\ChildLaborer\HouseholdMemberController;
use App\Http\Controllers\ChildLaborer\EducationRecordController;
use App\Http\Controllers\ChildLaborer\EmploymentRecordController;
use App\Http\Controllers\ChildLaborer\ParentGuardianController;
use App\Models\Role;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('auth')->group(function (): void {
    Route::get('/dashboard', DashboardController::class)
        ->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::prefix('lookup/locations')
        ->name('lookup.locations.')
        ->group(function (): void {
            Route::get(
                '/regions',
                [LocationLookupController::class, 'regions']
            )->name('regions');

            Route::get(
                '/regions/{region}/provinces',
                [LocationLookupController::class, 'provinces']
            )->name('provinces');

            Route::get(
                '/regions/{region}/localities',
                [LocationLookupController::class, 'regionalLocalities']
            )->name('regional-localities');

            Route::get(
                '/provinces/{province}/localities',
                [LocationLookupController::class, 'provincialLocalities']
            )->name('provincial-localities');

            Route::get(
                '/localities/{locality}/children',
                [LocationLookupController::class, 'childLocalities']
            )->name('child-localities');

            Route::get(
                '/localities/{locality}/barangays',
                [LocationLookupController::class, 'barangays']
            )->name('barangays');
        });


    /*
|--------------------------------------------------------------------------
| Audit schedules
|--------------------------------------------------------------------------
*/

    Route::get(
        '/audit-schedules',
        [AuditScheduleController::class, 'index']
    )->name('audit-schedules.index');

    Route::get(
        '/audit-schedules/{auditSchedule}/edit',
        [AuditScheduleController::class, 'edit']
    )->name('audit-schedules.edit');

    Route::put(
        '/audit-schedules/{auditSchedule}',
        [AuditScheduleController::class, 'update']
    )->name('audit-schedules.update');

    Route::get(
        '/audit-schedules/{auditSchedule}',
        [AuditScheduleController::class, 'show']
    )->name('audit-schedules.show');

    /*
    |--------------------------------------------------------------------------
    | Audit evaluations
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/audit-schedules/{auditSchedule}/evaluations',
        [AuditEvaluationController::class, 'store']
    )->name('audit-schedules.evaluations.store');

    Route::get(
        '/audit-schedules/{auditSchedule}/evaluations/{auditEvaluation}/edit',
        [AuditEvaluationController::class, 'edit']
    )->name('audit-schedules.evaluations.edit');

    Route::put(
        '/audit-schedules/{auditSchedule}/evaluations/{auditEvaluation}',
        [AuditEvaluationController::class, 'update']
    )->name('audit-schedules.evaluations.update');
    /*
|--------------------------------------------------------------------------
| Global activity logs
|--------------------------------------------------------------------------
*/



    Route::get(
        '/activity-logs',
        [ActivityLogController::class, 'index']
    )->name('activity-logs.index');

    Route::get(
        '/activity-logs/{activityLog}',
        [ActivityLogController::class, 'show']
    )->name('activity-logs.show');
    Route::prefix('child-laborers')
        ->name('child-laborers.')
        ->group(function (): void {
            Route::get(
                '/',
                [ChildLaborerController::class, 'index']
            )->name('index');

            Route::get(
                '/create',
                [ChildLaborerController::class, 'create']
            )->name('create');

            Route::post(
                '/',
                [ChildLaborerController::class, 'store']
            )->name('store');

            Route::get(
                '/{childLaborer}/photo',
                [ChildLaborerController::class, 'photo']
            )->name('photo');

            Route::get(
                '/{childLaborer}/audit-schedules/create',
                [AuditScheduleController::class, 'create']
            )->name('audit-schedules.create');

            Route::post(
                '/{childLaborer}/audit-schedules',
                [AuditScheduleController::class, 'store']
            )->name('audit-schedules.store');

            /*
            |--------------------------------------------------------------------------
            | Reports
            |--------------------------------------------------------------------------
            */

            Route::prefix('reports')
                ->name('reports.')
                ->group(function (): void {
                    Route::get(
                        '/child-laborers',
                        [
                            ChildLaborerReportController::class,
                            'index',
                        ]
                    )->name(
                            'child-laborers.index'
                        );

                    /*
                     * Static routes must be placed before the dynamic
                     * {childLaborer} route.
                     */
                    Route::get(
                        '/child-laborers/export/csv',
                        [
                            ChildLaborerReportController::class,
                            'exportCsv',
                        ]
                    )->name(
                            'child-laborers.export.csv'
                        );

                    Route::get(
                        '/child-laborers/print',
                        [
                            ChildLaborerReportController::class,
                            'printMasterList',
                        ]
                    )->name(
                            'child-laborers.print'
                        );

                    Route::get(
                        '/child-laborers/{childLaborer}/print',
                        [
                            ChildLaborerReportController::class,
                            'printProfile',
                        ]
                    )->name(
                            'child-laborers.profile.print'
                        );

                    Route::get(
                        '/child-laborers/{childLaborer}',
                        [
                            ChildLaborerReportController::class,
                            'profile',
                        ]
                    )->name(
                            'child-laborers.profile'
                        );
                });
            /*
            |--------------------------------------------------------------------------
            | Education records
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/education-records',
                [EducationRecordController::class, 'index']
            )->name('education-records.index');

            Route::post(
                '/{childLaborer}/education-records',
                [EducationRecordController::class, 'store']
            )->name('education-records.store');

            Route::get(
                '/{childLaborer}/education-records/{educationRecord}/edit',
                [EducationRecordController::class, 'edit']
            )->name('education-records.edit');

            Route::put(
                '/{childLaborer}/education-records/{educationRecord}',
                [EducationRecordController::class, 'update']
            )->name('education-records.update');

            Route::delete(
                '/{childLaborer}/education-records/{educationRecord}',
                [EducationRecordController::class, 'destroy']
            )->name('education-records.destroy');

            /*
            |--------------------------------------------------------------------------
            | Birth information
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/birth-information',
                [BirthInformationController::class, 'edit']
            )->name('birth-information.edit');

            Route::put(
                '/{childLaborer}/birth-information',
                [BirthInformationController::class, 'update']
            )->name('birth-information.update');

            /*
            |--------------------------------------------------------------------------
            | Residential address
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/residential-address',
                [ResidentialAddressController::class, 'edit']
            )->name('residential-address.edit');

            Route::put(
                '/{childLaborer}/residential-address',
                [ResidentialAddressController::class, 'update']
            )->name('residential-address.update');

            /*
            |--------------------------------------------------------------------------
            | Health information
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/health-information',
                [HealthInformationController::class, 'index']
            )->name('health-information.index');

            Route::post(
                '/{childLaborer}/health-information',
                [HealthInformationController::class, 'store']
            )->name('health-information.store');

            Route::get(
                '/{childLaborer}/health-information/{healthInformation}/edit',
                [HealthInformationController::class, 'edit']
            )->name('health-information.edit');

            Route::put(
                '/{childLaborer}/health-information/{healthInformation}',
                [HealthInformationController::class, 'update']
            )->name('health-information.update');

            Route::delete(
                '/{childLaborer}/health-information/{healthInformation}',
                [HealthInformationController::class, 'destroy']
            )->name('health-information.destroy');


            /*
            |--------------------------------------------------------------------------
            | Interventions and assistance
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/interventions',
                [InterventionController::class, 'index']
            )->name('interventions.index');

            Route::post(
                '/{childLaborer}/interventions',
                [InterventionController::class, 'store']
            )->name('interventions.store');

            Route::get(
                '/{childLaborer}/interventions/{intervention}/edit',
                [InterventionController::class, 'edit']
            )->name('interventions.edit');

            Route::put(
                '/{childLaborer}/interventions/{intervention}',
                [InterventionController::class, 'update']
            )->name('interventions.update');

            /*
            |--------------------------------------------------------------------------
            | Work hazards
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/employment-records/{employmentRecord}/work-hazards',
                [WorkHazardController::class, 'index']
            )->name('work-hazards.index');

            Route::post(
                '/{childLaborer}/employment-records/{employmentRecord}/work-hazards',
                [WorkHazardController::class, 'store']
            )->name('work-hazards.store');

            Route::get(
                '/{childLaborer}/employment-records/{employmentRecord}/work-hazards/{workHazard}/edit',
                [WorkHazardController::class, 'edit']
            )->name('work-hazards.edit');

            Route::put(
                '/{childLaborer}/employment-records/{employmentRecord}/work-hazards/{workHazard}',
                [WorkHazardController::class, 'update']
            )->name('work-hazards.update');

            Route::delete(
                '/{childLaborer}/employment-records/{employmentRecord}/work-hazards/{workHazard}',
                [WorkHazardController::class, 'destroy']
            )->name('work-hazards.destroy');

            /*
            |--------------------------------------------------------------------------
            | Employment records
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/employment-records',
                [EmploymentRecordController::class, 'index']
            )->name('employment-records.index');

            Route::post(
                '/{childLaborer}/employment-records',
                [EmploymentRecordController::class, 'store']
            )->name('employment-records.store');

            Route::get(
                '/{childLaborer}/employment-records/{employmentRecord}/edit',
                [EmploymentRecordController::class, 'edit']
            )->name('employment-records.edit');

            Route::put(
                '/{childLaborer}/employment-records/{employmentRecord}',
                [EmploymentRecordController::class, 'update']
            )->name('employment-records.update');

            Route::delete(
                '/{childLaborer}/employment-records/{employmentRecord}',
                [EmploymentRecordController::class, 'destroy']
            )->name('employment-records.destroy');
            /*
            |--------------------------------------------------------------------------
            | Workflow
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/edit',
                [ChildLaborerController::class, 'edit']
            )->name('edit');

            Route::patch(
                '/{childLaborer}/submit',
                [ChildLaborerController::class, 'submit']
            )->name('submit');

            Route::patch(
                '/{childLaborer}/approve',
                [ChildLaborerController::class, 'approve']
            )->name('approve');

            Route::patch(
                '/{childLaborer}/return',
                [
                    ChildLaborerController::class,
                    'returnForCorrection',
                ]
            )->name('return');

            Route::patch(
                '/{childLaborer}/archive',
                [ChildLaborerController::class, 'archive']
            )->name('archive');

            Route::patch(
                '/{childLaborer}/restore',
                [ChildLaborerController::class, 'restore']
            )->name('restore');

            Route::get(
                '/{childLaborer}',
                [ChildLaborerController::class, 'show']
            )->name('show');

            Route::patch(
                '/{childLaborer}',
                [ChildLaborerController::class, 'update']
            )->name('update');

            Route::get(
                '/{childLaborer}/activity-logs',
                [ActivityLogController::class, 'profile']
            )->name('activity-logs.index');
            /*
            |--------------------------------------------------------------------------
            | Parents and guardians
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/parent-guardians',
                [ParentGuardianController::class, 'index']
            )->name('parent-guardians.index');

            Route::post(
                '/{childLaborer}/parent-guardians',
                [ParentGuardianController::class, 'store']
            )->name('parent-guardians.store');

            Route::get(
                '/{childLaborer}/parent-guardians/{parentGuardian}/edit',
                [ParentGuardianController::class, 'edit']
            )->name('parent-guardians.edit');

            Route::put(
                '/{childLaborer}/parent-guardians/{parentGuardian}',
                [ParentGuardianController::class, 'update']
            )->name('parent-guardians.update');

            Route::delete(
                '/{childLaborer}/parent-guardians/{parentGuardian}',
                [ParentGuardianController::class, 'destroy']
            )->name('parent-guardians.destroy');

            /*
            |--------------------------------------------------------------------------
            | Child laborer documents
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/documents',
                [DocumentController::class, 'index']
            )->name('documents.index');

            Route::post(
                '/{childLaborer}/documents',
                [DocumentController::class, 'store']
            )->name('documents.store');

            Route::get(
                '/{childLaborer}/documents/{document}/download',
                [DocumentController::class, 'download']
            )->name('documents.download');

            Route::delete(
                '/{childLaborer}/documents/{document}',
                [DocumentController::class, 'destroy']
            )->name('documents.destroy');


            /*
            |--------------------------------------------------------------------------
            | Household members
            |--------------------------------------------------------------------------
            */

            Route::get(
                '/{childLaborer}/household-members',
                [HouseholdMemberController::class, 'index']
            )->name('household-members.index');

            Route::post(
                '/{childLaborer}/household-members',
                [HouseholdMemberController::class, 'store']
            )->name('household-members.store');

            Route::get(
                '/{childLaborer}/household-members/{householdMember}/edit',
                [HouseholdMemberController::class, 'edit']
            )->name('household-members.edit');

            Route::put(
                '/{childLaborer}/household-members/{householdMember}',
                [HouseholdMemberController::class, 'update']
            )->name('household-members.update');

            Route::delete(
                '/{childLaborer}/household-members/{householdMember}',
                [HouseholdMemberController::class, 'destroy']
            )->name('household-members.destroy');
        });
    /*
    |--------------------------------------------------------------------------
    | Super Admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('super-admin')
        ->name('super-admin.')
        ->middleware('role:' . Role::SUPER_ADMIN)
        ->group(function (): void {
            Route::get(
                '/dashboard',
                SuperAdminDashboardController::class
            )->name('dashboard');
        });

    /*
    |--------------------------------------------------------------------------
    | Admin and Super Admin
    |--------------------------------------------------------------------------
    */

    Route::prefix('admin')
        ->name('admin.')
        ->middleware(
            'role:' . Role::SUPER_ADMIN . ',' . Role::ADMIN
        )
        ->group(function (): void {
            Route::get(
                '/dashboard',
                AdminDashboardController::class
            )->name('dashboard');

            Route::get(
                '/users',
                [UserController::class, 'index']
            )->name('users.index');

            Route::get(
                '/users/create',
                [UserController::class, 'create']
            )->name('users.create');

            Route::post(
                '/users',
                [UserController::class, 'store']
            )->name('users.store');

            Route::get(
                '/users/{user}/edit',
                [UserController::class, 'edit']
            )->name('users.edit');

            Route::patch(
                '/users/{user}',
                [UserController::class, 'update']
            )->name('users.update');

            Route::patch(
                '/users/{user}/toggle-status',
                [UserController::class, 'toggleStatus']
            )->name('users.toggle-status');

            Route::patch(
                '/users/{user}/reset-password',
                [UserController::class, 'resetPassword']
            )->name('users.reset-password');
            Route::get(
                '/location-test',
                [LocationTestController::class, 'create']
            )->name('location-test.create');

            Route::post(
                '/location-test',
                [LocationTestController::class, 'store']
            )->name('location-test.store');
        });

    /*
    |--------------------------------------------------------------------------
    | Profiling Officer
    |--------------------------------------------------------------------------
    */

    Route::prefix('profiling-officer')
        ->name('profiling-officer.')
        ->middleware('role:' . Role::PROFILING_OFFICER)
        ->group(function (): void {
            Route::get(
                '/dashboard',
                ProfilingOfficerDashboardController::class
            )->name('dashboard');
        });

    /*
    |--------------------------------------------------------------------------
    | Viewer
    |--------------------------------------------------------------------------
    */

    Route::prefix('viewer')
        ->name('viewer.')
        ->middleware('role:' . Role::VIEWER)
        ->group(function (): void {
            Route::get(
                '/dashboard',
                ViewerDashboardController::class
            )->name('dashboard');
        });
});

require __DIR__ . '/auth.php';