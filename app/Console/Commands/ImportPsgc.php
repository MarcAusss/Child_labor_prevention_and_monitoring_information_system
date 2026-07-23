<?php

namespace App\Console\Commands;

use App\Services\PsgcImportService;
use Illuminate\Console\Command;
use RuntimeException;
use Throwable;

class ImportPsgc extends Command
{
    protected $signature = 'psgc:import
        {file : Absolute path or path relative to the project root}
        {--dataset-version= : PSGC publication version, such as 2Q 2026}
        {--source-url= : Official source URL}
        {--force : Import the file even when its checksum was already imported}';

    protected $description = 'Import official PSGC location data from an Excel publication file.';

    public function handle(
        PsgcImportService $importService
    ): int {
        try {
            $filePath = $this->resolveFilePath(
                (string) $this->argument('file')
            );

            $version = trim(
                (string) $this->option('dataset-version')
            );

            if ($version === '') {
                $version = $this->deriveVersion(
                    basename($filePath)
                );
            }

            $this->components->info(
                'Importing PSGC location data...'
            );

            $import = $importService->import(
                filePath: $filePath,
                version: $version,
                sourceUrl: $this->nullableOption('source-url'),
                force: (bool) $this->option('force'),
            );

            $counts = $import->record_counts ?? [];

            $this->newLine();

            $this->table(
                [
                    'Record Type',
                    'Imported',
                ],
                [
                    [
                        'Regions',
                        number_format($counts['regions'] ?? 0),
                    ],
                    [
                        'Provinces',
                        number_format($counts['provinces'] ?? 0),
                    ],
                    [
                        'Cities',
                        number_format($counts['cities'] ?? 0),
                    ],
                    [
                        'Municipalities',
                        number_format($counts['municipalities'] ?? 0),
                    ],
                    [
                        'Sub-Municipalities',
                        number_format(
                            $counts['sub_municipalities'] ?? 0
                        ),
                    ],
                    [
                        'Barangays',
                        number_format($counts['barangays'] ?? 0),
                    ],
                    [
                        'Grouping Rows Ignored',
                        number_format(
                            $counts['ignored_grouping_rows'] ?? 0
                        ),
                    ],
                ]
            );

            $this->newLine();

            $this->components->info(
                "PSGC {$import->version} was imported successfully."
            );

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->components->error(
                $exception->getMessage()
            );

            return self::FAILURE;
        }
    }

    private function resolveFilePath(
        string $input
    ): string {
        $possiblePaths = [
            $input,
            base_path($input),
            storage_path(
                'app/private/psgc/'.$input
            ),
        ];

        foreach ($possiblePaths as $path) {
            if (is_file($path)) {
                $realPath = realpath($path);

                if ($realPath !== false) {
                    return $realPath;
                }
            }
        }

        throw new RuntimeException(
            "Unable to locate the PSGC file: {$input}"
        );
    }

    private function deriveVersion(
        string $filename
    ): string {
        if (
            preg_match(
                '/(\d)Q[-_\s]*(\d{4})/i',
                $filename,
                $matches
            )
        ) {
            return "{$matches[1]}Q {$matches[2]}";
        }

        return pathinfo(
            $filename,
            PATHINFO_FILENAME
        );
    }

    private function nullableOption(
        string $name
    ): ?string {
        $value = trim(
            (string) $this->option($name)
        );

        return $value !== ''
            ? $value
            : null;
    }
}