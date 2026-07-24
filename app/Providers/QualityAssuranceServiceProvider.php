<?php

namespace App\Providers;

use App\Console\Commands\RunClpmisQualityAssurance;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class QualityAssuranceServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(
                base_path(
                    'routes/quality-assurance.php'
                )
            );

        if (
            $this->app
                ->runningInConsole()
        ) {
            $this->commands([
                RunClpmisQualityAssurance::class,
            ]);
        }
    }
}
