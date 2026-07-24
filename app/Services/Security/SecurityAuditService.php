<?php

namespace App\Services\Security;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Throwable;

class SecurityAuditService
{
    /**
     * @return array<string, mixed>
     */
    public function audit(): array
    {
        $checks = collect([
            $this->check(
                key: 'debug_mode',
                label: 'Debug mode disabled',
                category: 'Application',
                passed: ! (bool) config(
                    'app.debug'
                ),
                critical: true,
                details: config('app.debug')
                    ? 'APP_DEBUG is enabled.'
                    : 'Debug mode is disabled.',
                recommendation:
                    'Set APP_DEBUG=false outside local development.'
            ),

            $this->check(
                key: 'app_key',
                label: 'Application encryption key',
                category: 'Application',
                passed: filled(
                    config('app.key')
                ),
                critical: true,
                details: filled(config('app.key'))
                    ? 'An application key is configured.'
                    : 'APP_KEY is missing.',
                recommendation:
                    'Run php artisan key:generate before using the system.'
            ),

            $this->check(
                key: 'https',
                label: 'HTTPS application URL',
                category: 'Transport',
                passed: str_starts_with(
                    strtolower(
                        (string) config(
                            'app.url'
                        )
                    ),
                    'https://'
                ),
                critical: app()->isProduction(),
                details:
                    'Configured URL: '
                    .(
                        config('app.url')
                        ?: 'Not configured'
                    ),
                recommendation:
                    'Use an HTTPS APP_URL and a valid TLS certificate in production.'
            ),

            $this->check(
                key: 'secure_cookie',
                label: 'Secure session cookie',
                category: 'Session',
                passed: (bool) config(
                    'session.secure'
                ),
                critical: app()->isProduction(),
                details: config(
                    'session.secure'
                )
                    ? 'Secure cookies are enabled.'
                    : 'Secure cookies are disabled.',
                recommendation:
                    'Set SESSION_SECURE_COOKIE=true when HTTPS is enabled.'
            ),

            $this->check(
                key: 'http_only_cookie',
                label: 'HTTP-only session cookie',
                category: 'Session',
                passed: (bool) config(
                    'session.http_only',
                    true
                ),
                critical: true,
                details: config(
                    'session.http_only',
                    true
                )
                    ? 'HTTP-only cookies are enabled.'
                    : 'HTTP-only cookies are disabled.',
                recommendation:
                    'Set SESSION_HTTP_ONLY=true.'
            ),

            $this->check(
                key: 'same_site_cookie',
                label: 'SameSite session protection',
                category: 'Session',
                passed: in_array(
                    strtolower(
                        (string) config(
                            'session.same_site'
                        )
                    ),
                    [
                        'lax',
                        'strict',
                    ],
                    true
                ),
                critical: false,
                details:
                    'Current SameSite value: '
                    .(
                        config(
                            'session.same_site'
                        )
                        ?: 'Not configured'
                    ),
                recommendation:
                    'Use SESSION_SAME_SITE=lax or strict.'
            ),

            $this->check(
                key: 'session_lifetime',
                label: 'Limited session lifetime',
                category: 'Session',
                passed: (
                    (int) config(
                        'session.lifetime',
                        120
                    )
                ) <= 120,
                critical: false,
                details:
                    'Configured session lifetime: '
                    .(int) config(
                        'session.lifetime',
                        120
                    )
                    .' minutes.',
                recommendation:
                    'Use a session lifetime of 120 minutes or less.'
            ),

            $this->check(
                key: 'idle_timeout',
                label: 'Idle-session timeout',
                category: 'Session',
                passed: (
                    (int) config(
                        'clpmis-security.idle_timeout_minutes',
                        30
                    )
                ) > 0,
                critical: false,
                details:
                    'Idle timeout: '
                    .(int) config(
                        'clpmis-security.idle_timeout_minutes',
                        30
                    )
                    .' minutes.',
                recommendation:
                    'Keep CLPMIS_IDLE_TIMEOUT_MINUTES between 15 and 60.'
            ),

            $this->databaseCheck(),

            $this->tableCheck(
                'activity_logs',
                'Activity logging table',
                'Accountability'
            ),

            $this->tableCheck(
                'notifications',
                'Notifications table',
                'Application'
            ),

            $this->privateDocumentStorageCheck(),

            $this->storageWritableCheck(),

            $this->migrationCheck(),

            $this->extensionCheck(
                'openssl',
                'OpenSSL extension'
            ),

            $this->extensionCheck(
                'fileinfo',
                'Fileinfo extension'
            ),

            $this->extensionCheck(
                'mbstring',
                'Mbstring extension'
            ),

            $this->extensionCheck(
                'pdo_mysql',
                'PDO MySQL extension'
            ),
        ]);

        return [
            'checks' => $checks,

            'summary' => [
                'total' =>
                    $checks->count(),

                'passed' =>
                    $checks
                        ->where(
                            'status',
                            'pass'
                        )
                        ->count(),

                'warnings' =>
                    $checks
                        ->where(
                            'status',
                            'warning'
                        )
                        ->count(),

                'critical' =>
                    $checks
                        ->where(
                            'status',
                            'critical'
                        )
                        ->count(),

                'score' =>
                    $checks->isEmpty()
                        ? 0
                        : (int) round(
                            (
                                $checks
                                    ->where(
                                        'status',
                                        'pass'
                                    )
                                    ->count()
                                / $checks->count()
                            ) * 100
                        ),
            ],

            'environment' => [
                'application_environment' =>
                    app()->environment(),

                'php_version' =>
                    PHP_VERSION,

                'laravel_version' =>
                    app()->version(),

                'database_connection' =>
                    config(
                        'database.default'
                    ),

                'session_driver' =>
                    config(
                        'session.driver'
                    ),

                'cache_store' =>
                    config(
                        'cache.default'
                    ),

                'queue_connection' =>
                    config(
                        'queue.default'
                    ),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->check(
                key: 'database',
                label: 'Database connection',
                category: 'Data',
                passed: true,
                critical: true,
                details:
                    'The configured database is reachable.',
                recommendation:
                    'Continue protecting database credentials and backups.'
            );
        } catch (Throwable $exception) {
            return $this->check(
                key: 'database',
                label: 'Database connection',
                category: 'Data',
                passed: false,
                critical: true,
                details:
                    'Database connection failed.',
                recommendation:
                    'Verify DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, and DB_PASSWORD.'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function tableCheck(
        string $table,
        string $label,
        string $category
    ): array {
        $exists = false;

        try {
            $exists = Schema::hasTable(
                $table
            );
        } catch (Throwable) {
            $exists = false;
        }

        return $this->check(
            key: 'table_'.$table,
            label: $label,
            category: $category,
            passed: $exists,
            critical: $table
                === 'activity_logs',
            details: $exists
                ? 'The '.$table.' table exists.'
                : 'The '.$table.' table is missing.',
            recommendation:
                'Run php artisan migrate and verify migration status.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function privateDocumentStorageCheck(): array
    {
        $root = config(
            'filesystems.disks.clpmis_documents.root'
        );

        $configured = is_string($root)
            && $root !== '';

        $insidePublic = $configured
            && str_starts_with(
                $this->normalizePath($root),
                $this->normalizePath(
                    public_path()
                )
            );

        return $this->check(
            key: 'private_documents',
            label: 'Private document storage',
            category: 'Data Protection',
            passed:
                $configured
                && ! $insidePublic,
            critical: true,
            details: ! $configured
                ? 'The clpmis_documents disk is not configured.'
                : (
                    $insidePublic
                    ? 'The private document root is inside the public directory.'
                    : 'The private document root is outside the public directory.'
                ),
            recommendation:
                'Keep sensitive profile files under storage/app/private and serve them only through authorized controllers.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function storageWritableCheck(): array
    {
        $paths = [
            storage_path(),
            storage_path(
                'framework'
            ),
            storage_path(
                'logs'
            ),
        ];

        $passed = collect($paths)
            ->every(
                fn (string $path): bool =>
                    File::isDirectory($path)
                    && is_writable($path)
            );

        return $this->check(
            key: 'storage_writable',
            label: 'Storage directories writable',
            category: 'Application',
            passed: $passed,
            critical: true,
            details: $passed
                ? 'Required storage directories are writable.'
                : 'One or more storage directories are not writable.',
            recommendation:
                'Grant the web server write access only to the required storage and cache directories.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function migrationCheck(): array
    {
        try {
            if (! Schema::hasTable(
                'migrations'
            )) {
                return $this->check(
                    key: 'migrations',
                    label: 'Database migrations',
                    category: 'Data',
                    passed: false,
                    critical: true,
                    details:
                        'The migrations table is missing.',
                    recommendation:
                        'Run php artisan migrate.'
                );
            }

            $diskMigrations = collect(
                File::files(
                    database_path(
                        'migrations'
                    )
                )
            )
                ->map(
                    fn ($file): string =>
                        pathinfo(
                            $file->getFilename(),
                            PATHINFO_FILENAME
                        )
                )
                ->values();

            $databaseMigrations = DB::table(
                'migrations'
            )
                ->pluck('migration');

            $pending = $diskMigrations
                ->diff(
                    $databaseMigrations
                )
                ->values();

            return $this->check(
                key: 'migrations',
                label: 'Database migrations current',
                category: 'Data',
                passed: $pending->isEmpty(),
                critical: true,
                details: $pending->isEmpty()
                    ? 'No pending migration was detected.'
                    : $pending->count()
                        .' pending migration(s) detected.',
                recommendation:
                    'Back up the database and run php artisan migrate.'
            );
        } catch (Throwable) {
            return $this->check(
                key: 'migrations',
                label: 'Database migrations current',
                category: 'Data',
                passed: false,
                critical: true,
                details:
                    'Migration status could not be checked.',
                recommendation:
                    'Run php artisan migrate:status manually.'
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function extensionCheck(
        string $extension,
        string $label
    ): array {
        $loaded = extension_loaded(
            $extension
        );

        return $this->check(
            key: 'extension_'.$extension,
            label: $label,
            category: 'PHP',
            passed: $loaded,
            critical: in_array(
                $extension,
                [
                    'openssl',
                    'fileinfo',
                    'pdo_mysql',
                ],
                true
            ),
            details: $loaded
                ? 'The extension is loaded.'
                : 'The extension is not loaded.',
            recommendation:
                'Enable the '.$extension.' PHP extension and restart PHP.'
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function check(
        string $key,
        string $label,
        string $category,
        bool $passed,
        bool $critical,
        string $details,
        string $recommendation
    ): array {
        return [
            'key' => $key,

            'label' => $label,

            'category' => $category,

            'status' => $passed
                ? 'pass'
                : (
                    $critical
                    ? 'critical'
                    : 'warning'
                ),

            'details' => $details,

            'recommendation' =>
                $recommendation,
        ];
    }

    private function normalizePath(
        string $path
    ): string {
        return str_replace(
            '\\',
            '/',
            strtolower(
                rtrim(
                    $path,
                    '\\/'
                )
            )
        );
    }
}
