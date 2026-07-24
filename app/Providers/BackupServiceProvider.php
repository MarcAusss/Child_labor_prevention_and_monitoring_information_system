<?php

namespace App\Providers;

use App\Console\Commands\CleanupClpmisBackups;
use App\Console\Commands\CreateClpmisBackup;
use App\Console\Commands\RestoreClpmisBackup;
use App\Console\Commands\VerifyClpmisBackup;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class BackupServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Route::middleware('web')
            ->group(
                base_path(
                    'routes/backups.php'
                )
            );

        if (
            $this->app
                ->runningInConsole()
        ) {
            $this->commands([
                CreateClpmisBackup::class,
                VerifyClpmisBackup::class,
                CleanupClpmisBackups::class,
                RestoreClpmisBackup::class,
            ]);

            $this->app->booted(
                function (): void {
                    $schedule = app(
                        Schedule::class
                    );

                    if (
                        config(
                            'clpmis-backup.scheduled_backup_enabled',
                            false
                        )
                    ) {
                        $schedule
                            ->command(
                                'clpmis:backup:create'
                            )
                            ->dailyAt(
                                (string) config(
                                    'clpmis-backup.scheduled_backup_time',
                                    '01:30'
                                )
                            )
                            ->withoutOverlapping()
                            ->runInBackground();
                    }

                    if (
                        config(
                            'clpmis-backup.scheduled_cleanup_enabled',
                            false
                        )
                    ) {
                        $schedule
                            ->command(
                                'clpmis:backup:cleanup'
                            )
                            ->dailyAt('02:30')
                            ->withoutOverlapping();
                    }
                }
            );
        }
    }
}
