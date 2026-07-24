<?php

namespace App\Services\Backup;

use App\Models\BackupRun;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\NotificationService;
use FilesystemIterator;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
use Symfony\Component\Process\Process;
use Throwable;
use ZipArchive;

class BackupService
{
    public function __construct(
        private readonly ActivityLogger
            $activityLogger,

        private readonly NotificationService
            $notificationService
    ) {
    }

    public function create(
        ?User $actor = null,
        string $type = 'full'
    ): BackupRun {
        $this->ensureRequirements();

        $backup = BackupRun::query()
            ->create([
                'created_by' =>
                    $actor?->id,

                'backup_type' =>
                    $type,

                'status' =>
                    BackupRun::STATUS_RUNNING,

                'disk' =>
                    (string) config(
                        'clpmis-backup.disk',
                        'local'
                    ),

                'started_at' =>
                    now(),
            ]);

        $workingDirectory =
            $this->workingDirectory(
                $backup
            );

        try {
            File::ensureDirectoryExists(
                $workingDirectory
            );

            $databasePath =
                $workingDirectory
                .DIRECTORY_SEPARATOR
                .'database.sql';

            $this->dumpDatabase(
                $databasePath
            );

            $manifest = $this->manifest(
                $backup,
                $databasePath
            );

            $manifestPath =
                $workingDirectory
                .DIRECTORY_SEPARATOR
                .'manifest.json';

            File::put(
                $manifestPath,
                json_encode(
                    $manifest,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                )
            );

            $fileName =
                'clpmis-full-'
                .now()->format(
                    'Ymd-His'
                )
                .'-'
                .Str::lower(
                    Str::random(6)
                )
                .'.zip';

            $relativePath =
                trim(
                    (string) config(
                        'clpmis-backup.directory',
                        'backups'
                    ),
                    '/\\'
                )
                .'/'
                .$fileName;

            $temporaryArchive =
                $workingDirectory
                .DIRECTORY_SEPARATOR
                .$fileName;

            $this->buildArchive(
                archivePath:
                    $temporaryArchive,
                databasePath:
                    $databasePath,
                manifestPath:
                    $manifestPath
            );

            $disk = Storage::disk(
                $backup->disk
            );

            $stream = fopen(
                $temporaryArchive,
                'rb'
            );

            if ($stream === false) {
                throw new RuntimeException(
                    'The completed backup archive could not be opened.'
                );
            }

            try {
                $stored = $disk->put(
                    $relativePath,
                    $stream
                );
            } finally {
                fclose($stream);
            }

            if (! $stored) {
                throw new RuntimeException(
                    'The backup archive could not be stored.'
                );
            }

            $checksum = hash_file(
                'sha256',
                $temporaryArchive
            );

            if (! is_string($checksum)) {
                throw new RuntimeException(
                    'The backup checksum could not be generated.'
                );
            }

            $fileSize = filesize(
                $temporaryArchive
            );

            $backup->update([
                'status' =>
                    BackupRun::STATUS_COMPLETED,

                'file_path' =>
                    $relativePath,

                'file_name' =>
                    $fileName,

                'file_size' =>
                    is_int($fileSize)
                        ? $fileSize
                        : null,

                'checksum_sha256' =>
                    $checksum,

                'manifest' =>
                    $manifest,

                'completed_at' =>
                    now(),

                'error_message' =>
                    null,
            ]);

            $this->verify($backup);

            $this->activityLogger->log(
                action: 'backup_created',
                description:
                    'Created a complete CLPMIS backup.',
                metadata: [
                    'backup_run_id' =>
                        $backup->id,

                    'file_name' =>
                        $backup->file_name,

                    'file_size' =>
                        $backup->file_size,

                    'checksum_sha256' =>
                        $backup
                            ->checksum_sha256,
                ],
                actor: $actor
            );

            $this->notifySuccess(
                $backup,
                $actor
            );

            return $backup->fresh([
                'creator',
            ]);
        } catch (Throwable $exception) {
            $backup->update([
                'status' =>
                    BackupRun::STATUS_FAILED,

                'error_message' =>
                    Str::limit(
                        $exception->getMessage(),
                        5000
                    ),

                'completed_at' =>
                    now(),
            ]);

            $this->notifyFailure(
                $backup,
                $exception,
                $actor
            );

            throw $exception;
        } finally {
            File::deleteDirectory(
                $workingDirectory
            );
        }
    }

    public function verify(
        BackupRun $backup
    ): bool {
        if (! $backup->isDownloadable()) {
            throw new RuntimeException(
                'Only completed backups can be verified.'
            );
        }

        $localPath = $this->localCopy(
            $backup
        );

        $deleteLocalCopy =
            $localPath['temporary'];

        try {
            $actualChecksum =
                hash_file(
                    'sha256',
                    $localPath['path']
                );

            if (
                ! is_string(
                    $actualChecksum
                )
                || ! hash_equals(
                    (string) $backup
                        ->checksum_sha256,
                    $actualChecksum
                )
            ) {
                throw new RuntimeException(
                    'The backup checksum does not match.'
                );
            }

            $zip = new ZipArchive();

            $opened = $zip->open(
                $localPath['path']
            );

            if ($opened !== true) {
                throw new RuntimeException(
                    'The backup ZIP archive is invalid.'
                );
            }

            try {
                foreach (
                    [
                        'database.sql',
                        'manifest.json',
                    ]
                    as $requiredFile
                ) {
                    if (
                        $zip->locateName(
                            $requiredFile
                        ) === false
                    ) {
                        throw new RuntimeException(
                            'The backup is missing '
                            .$requiredFile
                            .'.'
                        );
                    }
                }
            } finally {
                $zip->close();
            }

            $backup->forceFill([
                'verified_at' => now(),
            ])->save();

            return true;
        } finally {
            if ($deleteLocalCopy) {
                File::delete(
                    $localPath['path']
                );
            }
        }
    }

    public function delete(
        BackupRun $backup,
        ?User $actor = null
    ): void {
        if (
            filled($backup->file_path)
            && Storage::disk(
                $backup->disk
            )->exists(
                $backup->file_path
            )
        ) {
            Storage::disk(
                $backup->disk
            )->delete(
                $backup->file_path
            );
        }

        $backup->update([
            'status' =>
                BackupRun::STATUS_PRUNED,

            'pruned_at' =>
                now(),
        ]);

        $this->activityLogger->log(
            action: 'backup_deleted',
            description:
                'Deleted a stored CLPMIS backup archive.',
            metadata: [
                'backup_run_id' =>
                    $backup->id,

                'file_name' =>
                    $backup->file_name,
            ],
            actor: $actor
        );
    }

    public function cleanup(
        ?User $actor = null
    ): int {
        $days = max(
            1,
            (int) config(
                'clpmis-backup.retention_days',
                30
            )
        );

        $backups = BackupRun::query()
            ->where(
                'status',
                BackupRun::STATUS_COMPLETED
            )
            ->where(
                'created_at',
                '<',
                now()->subDays($days)
            )
            ->get();

        foreach ($backups as $backup) {
            $this->delete(
                $backup,
                $actor
            );
        }

        return $backups->count();
    }

    /**
     * @return array{
     *     path:string,
     *     temporary:bool
     * }
     */
    public function localCopy(
        BackupRun $backup
    ): array {
        if (
            ! filled($backup->file_path)
        ) {
            throw new RuntimeException(
                'The backup has no stored file.'
            );
        }

        $disk = Storage::disk(
            $backup->disk
        );

        if (! $disk->exists(
            $backup->file_path
        )) {
            throw new RuntimeException(
                'The backup file no longer exists.'
            );
        }

        $configuredPath = method_exists(
            $disk,
            'path'
        )
            ? $disk->path(
                $backup->file_path
            )
            : null;

        if (
            is_string($configuredPath)
            && File::exists(
                $configuredPath
            )
        ) {
            return [
                'path' =>
                    $configuredPath,

                'temporary' =>
                    false,
            ];
        }

        $temporaryPath =
            storage_path(
                'app/private/backups/.downloads/'
                .Str::uuid()
                .'.zip'
            );

        File::ensureDirectoryExists(
            dirname(
                $temporaryPath
            )
        );

        $readStream = $disk->readStream(
            $backup->file_path
        );

        if ($readStream === false) {
            throw new RuntimeException(
                'The backup could not be read.'
            );
        }

        $writeStream = fopen(
            $temporaryPath,
            'wb'
        );

        if ($writeStream === false) {
            fclose($readStream);

            throw new RuntimeException(
                'A temporary backup file could not be created.'
            );
        }

        try {
            stream_copy_to_stream(
                $readStream,
                $writeStream
            );
        } finally {
            fclose($readStream);
            fclose($writeStream);
        }

        return [
            'path' =>
                $temporaryPath,

            'temporary' =>
                true,
        ];
    }

    private function dumpDatabase(
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
                'Phase 7B currently supports MySQL database backups.'
            );
        }

        $database = (string) (
            $connection['database']
            ?? ''
        );

        if ($database === '') {
            throw new RuntimeException(
                'The MySQL database name is not configured.'
            );
        }

        $arguments = [
            (string) config(
                'clpmis-backup.mysqldump_path',
                'mysqldump'
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

            '--single-transaction',
            '--quick',
            '--routines',
            '--triggers',
            '--events',
            '--hex-blob',
            '--default-character-set=utf8mb4',
            '--skip-comments',
            $database,
        ];

        $process = new Process(
            $arguments,
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

        $handle = fopen(
            $databasePath,
            'wb'
        );

        if ($handle === false) {
            throw new RuntimeException(
                'The SQL dump file could not be created.'
            );
        }

        $errorOutput = '';

        try {
            $process->run(
                function (
                    string $type,
                    string $buffer
                ) use (
                    $handle,
                    &$errorOutput
                ): void {
                    if (
                        $type
                        === Process::OUT
                    ) {
                        fwrite(
                            $handle,
                            $buffer
                        );

                        return;
                    }

                    $errorOutput .= $buffer;
                }
            );
        } finally {
            fclose($handle);
        }

        if (! $process->isSuccessful()) {
            File::delete(
                $databasePath
            );

            throw new RuntimeException(
                'mysqldump failed: '
                .trim(
                    $errorOutput
                    ?: $process
                        ->getErrorOutput()
                )
            );
        }

        if (
            ! File::exists(
                $databasePath
            )
            || File::size(
                $databasePath
            ) <= 0
        ) {
            throw new RuntimeException(
                'mysqldump created an empty database file.'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function manifest(
        BackupRun $backup,
        string $databasePath
    ): array {
        return [
            'application' =>
                config(
                    'app.name',
                    'CLPMIS'
                ),

            'backup_run_id' =>
                $backup->id,

            'backup_type' =>
                $backup->backup_type,

            'created_at' =>
                now()->toAtomString(),

            'environment' =>
                app()->environment(),

            'laravel_version' =>
                app()->version(),

            'php_version' =>
                PHP_VERSION,

            'database_connection' =>
                config(
                    'database.default'
                ),

            'database_dump_size' =>
                File::size(
                    $databasePath
                ),

            'included_paths' =>
                collect(
                    config(
                        'clpmis-backup.include_paths',
                        []
                    )
                )
                    ->map(
                        fn (mixed $path): string =>
                            (string) $path
                    )
                    ->values()
                    ->all(),

            'excluded_paths' =>
                collect(
                    config(
                        'clpmis-backup.exclude_paths',
                        []
                    )
                )
                    ->map(
                        fn (mixed $path): string =>
                            (string) $path
                    )
                    ->values()
                    ->all(),
        ];
    }

    private function buildArchive(
        string $archivePath,
        string $databasePath,
        string $manifestPath
    ): void {
        $zip = new ZipArchive();

        $opened = $zip->open(
            $archivePath,
            ZipArchive::CREATE
            | ZipArchive::OVERWRITE
        );

        if ($opened !== true) {
            throw new RuntimeException(
                'The backup ZIP archive could not be created.'
            );
        }

        try {
            $zip->addFile(
                $databasePath,
                'database.sql'
            );

            $zip->addFile(
                $manifestPath,
                'manifest.json'
            );

            foreach (
                (array) config(
                    'clpmis-backup.include_paths',
                    []
                )
                as $includedPath
            ) {
                $includedPath =
                    (string) $includedPath;

                if (! File::exists(
                    $includedPath
                )) {
                    continue;
                }

                if (File::isFile(
                    $includedPath
                )) {
                    $zip->addFile(
                        $includedPath,
                        'files/'
                        .basename(
                            $includedPath
                        )
                    );

                    continue;
                }

                $this->addDirectory(
                    $zip,
                    $includedPath
                );
            }
        } finally {
            $zip->close();
        }

        if (
            ! File::exists(
                $archivePath
            )
            || File::size(
                $archivePath
            ) <= 0
        ) {
            throw new RuntimeException(
                'The backup ZIP archive is empty.'
            );
        }
    }

    private function addDirectory(
        ZipArchive $zip,
        string $root
    ): void {
        $root = rtrim(
            realpath($root)
                ?: $root,
            DIRECTORY_SEPARATOR
        );

        $iterator =
            new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator(
                    $root,
                    FilesystemIterator::SKIP_DOTS
                ),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $realPath =
                $file->getRealPath();

            if (
                ! is_string(
                    $realPath
                )
                || $this->isExcluded(
                    $realPath
                )
            ) {
                continue;
            }

            $relative =
                ltrim(
                    str_replace(
                        '\\',
                        '/',
                        substr(
                            $realPath,
                            strlen($root)
                        )
                    ),
                    '/'
                );

            $zip->addFile(
                $realPath,
                'files/private/'
                .$relative
            );
        }
    }

    private function isExcluded(
        string $path
    ): bool {
        $normalizedPath =
            $this->normalizePath(
                $path
            );

        return collect(
            config(
                'clpmis-backup.exclude_paths',
                []
            )
        )->contains(
            function (
                mixed $excluded
            ) use (
                $normalizedPath
            ): bool {
                $normalizedExcluded =
                    $this->normalizePath(
                        (string) $excluded
                    );

                return $normalizedExcluded
                    !== ''
                    && (
                        $normalizedPath
                            === $normalizedExcluded
                        || str_starts_with(
                            $normalizedPath,
                            $normalizedExcluded
                            .'/'
                        )
                    );
            }
        );
    }

    private function ensureRequirements(): void
    {
        if (! class_exists(
            ZipArchive::class
        )) {
            throw new RuntimeException(
                'The PHP zip extension is required.'
            );
        }

        if (! File::isWritable(
            storage_path('app')
        )) {
            throw new RuntimeException(
                'Laravel storage/app is not writable.'
            );
        }
    }

    private function workingDirectory(
        BackupRun $backup
    ): string {
        return storage_path(
            'app/private/backups/.working/'
            .$backup->id
            .'-'
            .Str::uuid()
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function localNotificationRoute(
        BackupRun $backup
    ): array {
        return [
            'routeName' =>
                'backups.index',

            'routeParameters' =>
                [],
        ];
    }

    private function notifySuccess(
        BackupRun $backup,
        ?User $actor
    ): void {
        $route = $this
            ->localNotificationRoute(
                $backup
            );

        $this->notificationService->send(
            recipients:
                $this
                    ->notificationService
                    ->administrators(),

            title:
                'System backup completed',

            message:
                ($backup->file_name
                    ?: 'A CLPMIS backup')
                .' was created and verified successfully.',

            type:
                NotificationService::TYPE_SYSTEM,

            severity:
                NotificationService::SEVERITY_SUCCESS,

            routeName:
                $route['routeName'],

            routeParameters:
                $route['routeParameters'],

            actor:
                $actor,

            metadata: [
                'backup_run_id' =>
                    $backup->id,

                'file_size' =>
                    $backup->file_size,
            ]
        );
    }

    private function notifyFailure(
        BackupRun $backup,
        Throwable $exception,
        ?User $actor
    ): void {
        $this->notificationService->send(
            recipients:
                $this
                    ->notificationService
                    ->administrators(),

            title:
                'System backup failed',

            message:
                'CLPMIS backup run #'
                .$backup->id
                .' failed. Review the backup management page.',

            type:
                NotificationService::TYPE_SYSTEM,

            severity:
                NotificationService::SEVERITY_DANGER,

            routeName:
                'backups.index',

            actor:
                $actor,

            metadata: [
                'backup_run_id' =>
                    $backup->id,

                'error' =>
                    Str::limit(
                        $exception->getMessage(),
                        500
                    ),
            ]
        );
    }

    private function normalizePath(
        string $path
    ): string {
        return strtolower(
            str_replace(
                '\\',
                '/',
                rtrim(
                    $path,
                    '\\/'
                )
            )
        );
    }
}
