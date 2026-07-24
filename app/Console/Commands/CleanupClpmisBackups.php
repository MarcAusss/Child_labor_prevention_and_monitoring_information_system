<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;

class CleanupClpmisBackups extends Command
{
    protected $signature =
        'clpmis:backup:cleanup';

    protected $description =
        'Delete stored CLPMIS backups older than the configured retention period.';

    public function handle(
        BackupService $backupService
    ): int {
        $count = $backupService
            ->cleanup();

        $this->info(
            number_format($count)
            .' expired backup(s) were removed.'
        );

        return self::SUCCESS;
    }
}
