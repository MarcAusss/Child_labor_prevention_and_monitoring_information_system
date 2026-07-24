<?php

namespace App\Console\Commands;

use App\Services\Backup\BackupService;
use Illuminate\Console\Command;
use Throwable;

class CreateClpmisBackup extends Command
{
    protected $signature =
        'clpmis:backup:create';

    protected $description =
        'Create and verify a complete CLPMIS database and private-file backup.';

    public function handle(
        BackupService $backupService
    ): int {
        $this->info(
            'Creating a complete CLPMIS backup...'
        );

        try {
            $backup = $backupService
                ->create();

            $this->newLine();

            $this->info(
                'Backup completed successfully.'
            );

            $this->table(
                [
                    'ID',
                    'File',
                    'Size',
                    'Checksum',
                    'Verified',
                ],
                [[
                    $backup->id,
                    $backup->file_name,
                    $backup->formatted_size,
                    $backup
                        ->checksum_sha256,
                    $backup
                        ->verified_at
                        ? 'Yes'
                        : 'No',
                ]]
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
