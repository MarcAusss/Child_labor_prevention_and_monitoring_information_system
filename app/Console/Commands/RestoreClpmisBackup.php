<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Services\Backup\RestoreService;
use Illuminate\Console\Command;
use Throwable;

class RestoreClpmisBackup extends Command
{
    protected $signature =
        'clpmis:backup:restore
        {backup : Backup run ID}
        {--force : Skip the interactive confirmation}';

    protected $description =
        'Restore the MySQL database from a verified CLPMIS backup.';

    public function handle(
        RestoreService $restoreService
    ): int {
        $backup = BackupRun::query()
            ->findOrFail(
                (int) $this->argument(
                    'backup'
                )
            );

        $this->warn(
            'This operation replaces database records using the selected SQL dump.'
        );

        $this->warn(
            'Uploaded files are not restored automatically.'
        );

        if (
            ! $this->option('force')
            && ! $this->confirm(
                'Continue with database restoration?',
                false
            )
        ) {
            $this->line(
                'Restoration cancelled.'
            );

            return self::SUCCESS;
        }

        try {
            $restoreService
                ->restoreDatabase(
                    $backup
                );

            $this->info(
                'Database restoration completed.'
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }
}
