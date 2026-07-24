<?php

namespace App\Services\QualityAssurance;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class QualityAssuranceService
{
    /**
     * @return array<string, mixed>
     */
    public function run(
        bool $skipBuild = false,
        bool $skipSecurity = false
    ): array {
        $startedAt = now();

        $steps = collect();

        $steps->push(
            $this->runStep(
                key: 'configuration',
                label:
                    'Clear and rebuild application configuration',
                command: [
                    PHP_BINARY,
                    base_path('artisan'),
                    'optimize:clear',
                    '--no-interaction',
                ]
            )
        );

        $steps->push(
            $this->runStep(
                key: 'migrations',
                label:
                    'Check database migration status',
                command: [
                    PHP_BINARY,
                    base_path('artisan'),
                    'migrate:status',
                    '--no-interaction',
                ]
            )
        );

        $steps->push(
            $this->runStep(
                key: 'routes',
                label:
                    'Compile and inspect application routes',
                command: [
                    PHP_BINARY,
                    base_path('artisan'),
                    'route:list',
                    '--except-vendor',
                    '--no-ansi',
                ]
            )
        );

        $phpunitPath = base_path(
            'vendor/phpunit/phpunit/phpunit'
        );

        $steps->push(
            $this->runStep(
                key: 'automated_tests',
                label:
                    'Run dedicated CLPMIS PHPUnit suite',
                command: [
                    PHP_BINARY,
                    $phpunitPath,
                    '--configuration',
                    base_path(
                        'phpunit.clpmis.xml'
                    ),
                ],
                timeout: 1800
            )
        );

        if (! $skipSecurity) {
            $steps->push(
                $this->runStep(
                    key: 'security',
                    label:
                        'Run CLPMIS security audit',
                    command: [
                        PHP_BINARY,
                        base_path('artisan'),
                        'clpmis:security-check',
                        '--no-interaction',
                    ],
                    timeout: 600
                )
            );
        }

        if (! $skipBuild) {
            $steps->push(
                $this->runStep(
                    key: 'frontend_build',
                    label:
                        'Build production frontend assets',
                    command: [
                        PHP_OS_FAMILY
                            === 'Windows'
                            ? 'npm.cmd'
                            : 'npm',

                        'run',
                        'build',
                    ],
                    timeout: 1800
                )
            );
        }

        $finishedAt = now();

        $passed = $steps
            ->where('status', 'passed')
            ->count();

        $failed = $steps
            ->where('status', 'failed')
            ->count();

        $report = [
            'id' =>
                (string) Str::uuid(),

            'started_at' =>
                $startedAt->toAtomString(),

            'finished_at' =>
                $finishedAt->toAtomString(),

            'duration_seconds' =>
                $startedAt->diffInSeconds(
                    $finishedAt
                ),

            'status' =>
                $failed === 0
                    ? 'passed'
                    : 'failed',

            'summary' => [
                'total' =>
                    $steps->count(),

                'passed' =>
                    $passed,

                'failed' =>
                    $failed,
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

                'database_name' =>
                    config(
                        'database.connections.'
                        .config(
                            'database.default'
                        )
                        .'.database'
                    ),
            ],

            'steps' =>
                $steps->values()->all(),
        ];

        $this->storeReport(
            $report
        );

        return $report;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function recentReports(
        int $limit = 10
    ): array {
        $directory =
            $this->reportDirectory();

        if (! File::isDirectory(
            $directory
        )) {
            return [];
        }

        return collect(
            File::files($directory)
        )
            ->sortByDesc(
                fn ($file): int =>
                    $file->getMTime()
            )
            ->take($limit)
            ->map(
                function ($file): ?array {
                    $decoded = json_decode(
                        File::get(
                            $file->getPathname()
                        ),
                        true
                    );

                    return is_array($decoded)
                        ? $decoded
                        : null;
                }
            )
            ->filter()
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function runStep(
        string $key,
        string $label,
        array $command,
        int $timeout = 600
    ): array {
        $startedAt = microtime(true);

        $process = new Process(
            $command,
            base_path(),
            null,
            null,
            $timeout
        );

        $process->run();

        return [
            'key' =>
                $key,

            'label' =>
                $label,

            'status' =>
                $process->isSuccessful()
                    ? 'passed'
                    : 'failed',

            'exit_code' =>
                $process->getExitCode(),

            'duration_seconds' =>
                round(
                    microtime(true)
                    - $startedAt,
                    2
                ),

            'command' =>
                $process
                    ->getCommandLine(),

            'output' =>
                Str::limit(
                    trim(
                        $process
                            ->getOutput()
                    ),
                    25000
                ),

            'error_output' =>
                Str::limit(
                    trim(
                        $process
                            ->getErrorOutput()
                    ),
                    25000
                ),
        ];
    }

    /**
     * @param array<string, mixed> $report
     */
    private function storeReport(
        array $report
    ): void {
        $directory =
            $this->reportDirectory();

        File::ensureDirectoryExists(
            $directory
        );

        $filename =
            now()->format(
                'Ymd-His'
            )
            .'-'
            .$report['id']
            .'.json';

        File::put(
            $directory
            .DIRECTORY_SEPARATOR
            .$filename,
            json_encode(
                $report,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_SLASHES
            )
        );
    }

    private function reportDirectory(): string
    {
        return storage_path(
            'app/private/quality-assurance'
        );
    }
}
