<?php

namespace App\Providers;
use App\Listeners\AuthenticationActivitySubscriber;
use App\Models\ActivityLog;
use App\Models\BirthInformation;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\EducationRecord;
use App\Models\EmploymentRecord;
use App\Models\HealthInformation;
use App\Models\HouseholdMember;
use App\Models\Intervention;
use App\Models\ParentGuardian;
use App\Models\ResidentialAddress;
use App\Models\User;
use App\Models\WorkHazard;
use App\Observers\ActivityObserver;
use App\Policies\ActivityLogPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(
            ActivityLog::class,
            ActivityLogPolicy::class
        );

        $auditedModels = [
            User::class,
            ChildLaborer::class,
            BirthInformation::class,
            ResidentialAddress::class,
            ParentGuardian::class,
            HouseholdMember::class,
            EducationRecord::class,
            EmploymentRecord::class,
            WorkHazard::class,
            HealthInformation::class,
            Intervention::class,
            ChildLaborerDocument::class,
        ];

        foreach ($auditedModels as $model) {
            $model::observe(
                ActivityObserver::class
            );
        }

        Event::subscribe(
            AuthenticationActivitySubscriber::class
        );
    }
}
