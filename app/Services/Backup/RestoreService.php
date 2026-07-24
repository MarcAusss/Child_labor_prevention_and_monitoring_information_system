<?php

namespace App\Services\Backup;

use App\Models\BackupRun;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class RestoreService
{
    public function __construct(
        private readonly BackupService
            $backupService
    ) {
    }

    /**
     * Restores only the database dump from a verified backup.
     *
     * File restoration is intentionally manual so existing uploaded
     * documents are not overwritten without an administrator review.
     */
    public function restoreDatabase(
        BackupRun $backup
    ): void {
        $this->backupService->verify(
            $backup
        );

        $copy = $this
            ->backupService
            ->localCopy(
                $backup
            );

        $workingDirectory =
            storage_path(
                'app/private/backups/.restore/'
                .Str::uuid()
            );

        try {
            File::ensureDirectoryExists(
                $workingDirectory
            );

            $zip = new ZipArchive();

            if (
                $zip->open(
                    $copy['path']
                ) !== true
            ) {
                throw new RuntimeException(
                    'The backup ZIP archive could not be opened.'
                );
            }

            try {
                if (
                    ! $zip->extractTo(
                        $workingDirectory,
                        [
                            'database.sql',
                            'manifest.json',
                        ]
                    )
                ) {
                    throw new RuntimeException(
                        'The database dump could not be extracted.'
                    );
                }
            } finally {
                $zip->close();
            }

            $databasePath =
                $workingDirectory
                .DIRECTORY_SEPARATOR
                .'database.sql';

            if (! File::exists(
                $databasePath
            )) {
                throw new RuntimeException(
                    'The database.sql file is missing.'
                );
            }

            $this->importDatabase(
                $databasePath
            );
        } finally {
            File::deleteDirectory(
                $workingDirectory
            );

            if ($copy['temporary']) {
                File::delete(
                    $copy['path']
                );
            }
        }
    }

    private function importDatabase(
        string $databasePath
    ): void {
        $connectionName =
            (string) config(
                'database.default'
            );

        $connection = config(
            'database.connections.'
            .$connectionName
        );

        if (
            ! is_array($connection)
            || (
                $connection['driver']
                ?? null
            ) !== 'mysql'
        ) {
            throw new RuntimeException(
                'Phase 7B currently supports MySQL database restoration.'
            );
        }

        $database = (string) (
            $connection['database']
            ?? ''
        );

        if ($database === '') {
            throw new RuntimeException(
                'The database name is not configured.'
            );
        }

        $input = fopen(
            $databasePath,
            'rb'
        );

        if ($input === false) {
            throw new RuntimeException(
                'The SQL dump could not be opened.'
            );
        }

        $process = new Process(
            [
                (string) config(
                    'clpmis-backup.mysql_path',
                    'mysql'
                ),

                '--host='
                    .(string) (
                        $connection['host']
                        ?? '127.0.0.1'
                    ),

                '--port='
                    .(string) (
                        $connection['port']
                        ?? '3306'
                    ),

                '--user='
                    .(string) (
                        $connection['username']
                        ?? ''
                    ),

                '--default-character-set=utf8mb4',
                $database,
            ],
            base_path(),
            [
                'MYSQL_PWD' =>
                    (string) (
                        $connection['password']
                        ?? ''
                    ),
            ],
            null,
            3600
        );

        try {
            $process->setInput(
                $input
            );

            $process->run();
        } finally {
            fclose($input);
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException(
                'Database restoration failed: '
                .trim(
                    $process
                        ->getErrorOutput()
                )
            );
        }
    }
}
