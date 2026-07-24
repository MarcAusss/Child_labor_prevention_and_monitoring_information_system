<?php

namespace App\Providers;

use App\Models\AuditEvaluation;
use App\Models\AuditSchedule;
use App\Models\ChildLaborer;
use App\Models\ChildLaborerDocument;
use App\Models\Intervention;
use App\Observers\NotificationObserver;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        ChildLaborer::observe(
            NotificationObserver::class
        );

        AuditSchedule::observe(
            NotificationObserver::class
        );

        AuditEvaluation::observe(
            NotificationObserver::class
        );

        Intervention::observe(
            NotificationObserver::class
        );

        ChildLaborerDocument::observe(
            NotificationObserver::class
        );

        Route::middleware('web')
            ->group(
                base_path(
                    'routes/notifications.php'
                )
            );
    }
}
