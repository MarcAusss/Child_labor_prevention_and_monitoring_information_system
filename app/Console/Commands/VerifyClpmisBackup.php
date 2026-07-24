<?php

namespace App\Console\Commands;

use App\Models\BackupRun;
use App\Services\Backup\BackupService;
use Illuminate\Console\Command;
use Throwable;

class VerifyClpmisBackup extends Command
{
    protected $signature =
        'clpmis:backup:verify
        {backup : Backup run ID}';

    protected $description =
        'Verify the checksum and required contents of a CLPMIS backup.';

    public function handle(
        BackupService $backupService
    ): int {
        $backup = BackupRun::query()
            ->findOrFail(
                (int) $this->argument(
                    'backup'
                )
            );

        try {
            $backupService->verify(
                $backup
            );

            $this->info(
                'Backup verification passed.'
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
