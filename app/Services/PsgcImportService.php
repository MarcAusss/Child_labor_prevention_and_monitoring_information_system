<?php

namespace App\Services;

use App\Models\Barangay;
use App\Models\Locality;
use App\Models\Province;
use App\Models\PsgcImport;
use App\Models\Region;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use RuntimeException;
use Throwable;

class PsgcImportService
{
    private const BARANGAY_BATCH_SIZE = 1000;

    public function import(
        string $filePath,
        string $version,
        ?string $sourceUrl = null,
        bool $force = false,
        ?int $importedBy = null
    ): PsgcImport {
        if (! is_file($filePath)) {
            throw new RuntimeException(
                "The PSGC file does not exist: {$filePath}"
            );
        }

        if (! is_readable($filePath)) {
            throw new RuntimeException(
                "The PSGC file is not readable: {$filePath}"
            );
        }

        $fileHash = hash_file('sha256', $filePath);

        if ($fileHash === false) {
            throw new RuntimeException(
                'The PSGC file checksum could not be generated.'
            );
        }

        $previousImport = PsgcImport::query()
            ->where('file_sha256', $fileHash)
            ->where('status', PsgcImport::STATUS_COMPLETED)
            ->latest()
            ->first();

        if ($previousImport && ! $force) {
            throw new RuntimeException(
                "This PSGC file was already imported on "
                .$previousImport->completed_at?->format('F d, Y h:i A')
                .'. Use --force to import it again.'
            );
        }

        $import = PsgcImport::query()->create([
            'imported_by' => $importedBy,
            'version' => $version,
            'source_filename' => basename($filePath),
            'source_url' => $sourceUrl,
            'file_sha256' => $fileHash,
            'status' => PsgcImport::STATUS_RUNNING,
            'started_at' => now(),
        ]);

        try {
            $reader = IOFactory::createReaderForFile($filePath);

            $reader->setReadDataOnly(true);
            $reader->setLoadSheetsOnly([
                'PSGC',
            ]);

            $spreadsheet = $reader->load($filePath);

            $worksheet = $spreadsheet->getSheetByName('PSGC');

            if (! $worksheet) {
                throw new RuntimeException(
                    'The workbook does not contain a worksheet named PSGC.'
                );
            }

            $this->validateWorksheet($worksheet);

            $counts = DB::transaction(
                fn (): array => $this->importWorksheet($worksheet),
                attempts: 1
            );

            $spreadsheet->disconnectWorksheets();

            $import->update([
                'status' => PsgcImport::STATUS_COMPLETED,
                'record_counts' => $counts,
                'completed_at' => now(),
                'error_message' => null,
            ]);

            return $import->fresh();
        } catch (Throwable $exception) {
            $import->update([
                'status' => PsgcImport::STATUS_FAILED,
                'completed_at' => now(),
                'error_message' => $exception->getMessage(),
            ]);

            throw $exception;
        }
    }

    private function importWorksheet(
        Worksheet $worksheet
    ): array {
        $now = now();

        Region::query()->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);

        Province::query()->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);

        Locality::query()->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);

        Barangay::query()->update([
            'is_active' => false,
            'updated_at' => $now,
        ]);

        $counts = [
            'regions' => 0,
            'provinces' => 0,
            'cities' => 0,
            'municipalities' => 0,
            'sub_municipalities' => 0,
            'barangays' => 0,
            'ignored_grouping_rows' => 0,
        ];

        $currentRegionId = null;
        $currentProvinceId = null;
        $currentPrimaryLocalityId = null;
        $currentBarangayParentId = null;

        $barangayBatch = [];

        $highestRow = $worksheet->getHighestDataRow();

        for ($rowNumber = 2; $rowNumber <= $highestRow; $rowNumber++) {
            $row = $worksheet->rangeToArray(
                "A{$rowNumber}:K{$rowNumber}",
                null,
                true,
                false
            )[0];

            $psgcCode = $this->normalizeCode(
                $row[0] ?? null,
                10
            );

            $name = $this->nullableString(
                $row[1] ?? null
            );

            $correspondenceCode = $this->normalizeCode(
                $row[2] ?? null,
                9
            );

            $geographicLevel = $this->nullableString(
                $row[3] ?? null
            );

            $oldNames = $this->nullableString(
                $row[4] ?? null
            );

            $cityClass = $this->nullableString(
                $row[5] ?? null
            );

            $incomeClassification = $this->nullableString(
                $row[6] ?? null
            );

            $urbanRural = $this->nullableString(
                $row[7] ?? null
            );

            $population = $this->nullableInteger(
                $row[8] ?? null
            );

            $status = $this->nullableString(
                $row[10] ?? null
            );

            if (! $psgcCode || ! $name) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Grouping rows
            |--------------------------------------------------------------------------
            |
            | The PSGC file contains special grouping rows such as:
            | - City of Isabela (Not a Province)
            | - Special Geographic Area
            |
            | They have no geographic-level value. They are not saved as actual
            | selectable locations, but they reset the current province context.
            |
            */

            if (! $geographicLevel) {
                $currentProvinceId = null;
                $currentPrimaryLocalityId = null;
                $currentBarangayParentId = null;

                $counts['ignored_grouping_rows']++;

                continue;
            }

            switch ($geographicLevel) {
                case 'Reg':
                    $region = Region::query()->updateOrCreate(
                        [
                            'psgc_code' => $psgcCode,
                        ],
                        [
                            'correspondence_code' => $correspondenceCode,
                            'name' => $name,
                            'old_names' => $oldNames,
                            'population' => $population,
                            'is_active' => true,
                        ]
                    );

                    $currentRegionId = $region->id;
                    $currentProvinceId = null;
                    $currentPrimaryLocalityId = null;
                    $currentBarangayParentId = null;

                    $counts['regions']++;

                    break;

                case 'Prov':
                    $this->requireContext(
                        $currentRegionId,
                        "Province {$name} has no current region."
                    );

                    $province = Province::query()->updateOrCreate(
                        [
                            'psgc_code' => $psgcCode,
                        ],
                        [
                            'region_id' => $currentRegionId,
                            'correspondence_code' => $correspondenceCode,
                            'name' => $name,
                            'old_names' => $oldNames,
                            'population' => $population,
                            'is_active' => true,
                        ]
                    );

                    $currentProvinceId = $province->id;
                    $currentPrimaryLocalityId = null;
                    $currentBarangayParentId = null;

                    $counts['provinces']++;

                    break;

                case 'City':
                case 'Mun':
                    $this->requireContext(
                        $currentRegionId,
                        "Locality {$name} has no current region."
                    );

                    $level = $geographicLevel === 'City'
                        ? Locality::LEVEL_CITY
                        : Locality::LEVEL_MUNICIPALITY;

                    $locality = Locality::query()->updateOrCreate(
                        [
                            'psgc_code' => $psgcCode,
                        ],
                        [
                            'region_id' => $currentRegionId,
                            'province_id' => $currentProvinceId,
                            'parent_id' => null,
                            'correspondence_code' => $correspondenceCode,
                            'name' => $name,
                            'geographic_level' => $level,
                            'old_names' => $oldNames,
                            'city_class' => $cityClass,
                            'income_classification' => $incomeClassification,
                            'status' => $status,
                            'population' => $population,
                            'is_active' => true,
                        ]
                    );

                    $currentPrimaryLocalityId = $locality->id;
                    $currentBarangayParentId = $locality->id;

                    if ($geographicLevel === 'City') {
                        $counts['cities']++;
                    } else {
                        $counts['municipalities']++;
                    }

                    break;

                case 'SubMun':
                    $this->requireContext(
                        $currentRegionId,
                        "Sub-municipality {$name} has no current region."
                    );

                    $this->requireContext(
                        $currentPrimaryLocalityId,
                        "Sub-municipality {$name} has no parent locality."
                    );

                    $subMunicipality = Locality::query()->updateOrCreate(
                        [
                            'psgc_code' => $psgcCode,
                        ],
                        [
                            'region_id' => $currentRegionId,
                            'province_id' => $currentProvinceId,
                            'parent_id' => $currentPrimaryLocalityId,
                            'correspondence_code' => $correspondenceCode,
                            'name' => $name,
                            'geographic_level' => Locality::LEVEL_SUB_MUNICIPALITY,
                            'old_names' => $oldNames,
                            'city_class' => $cityClass,
                            'income_classification' => $incomeClassification,
                            'status' => $status,
                            'population' => $population,
                            'is_active' => true,
                        ]
                    );

                    $currentBarangayParentId = $subMunicipality->id;

                    $counts['sub_municipalities']++;

                    break;

                case 'Bgy':
                    $this->requireContext(
                        $currentRegionId,
                        "Barangay {$name} has no current region."
                    );

                    $this->requireContext(
                        $currentBarangayParentId,
                        "Barangay {$name} has no parent locality."
                    );

                    $barangayBatch[] = [
                        'region_id' => $currentRegionId,
                        'province_id' => $currentProvinceId,
                        'locality_id' => $currentBarangayParentId,
                        'psgc_code' => $psgcCode,
                        'correspondence_code' => $correspondenceCode,
                        'name' => $name,
                        'old_names' => $oldNames,
                        'urban_rural' => $urbanRural,
                        'status' => $status,
                        'population' => $population,
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $counts['barangays']++;

                    if (
                        count($barangayBatch)
                        >= self::BARANGAY_BATCH_SIZE
                    ) {
                        $this->saveBarangayBatch(
                            $barangayBatch
                        );

                        $barangayBatch = [];
                    }

                    break;

                default:
                    throw new RuntimeException(
                        "Unsupported geographic level '{$geographicLevel}' "
                        ."on spreadsheet row {$rowNumber}."
                    );
            }
        }

        if ($barangayBatch !== []) {
            $this->saveBarangayBatch(
                $barangayBatch
            );
        }

        if (
            $counts['regions'] === 0
            || $counts['barangays'] === 0
        ) {
            throw new RuntimeException(
                'No valid PSGC geographic records were imported.'
            );
        }

        return $counts;
    }

    private function saveBarangayBatch(
        array $barangays
    ): void {
        Barangay::query()->upsert(
            $barangays,
            [
                'psgc_code',
            ],
            [
                'region_id',
                'province_id',
                'locality_id',
                'correspondence_code',
                'name',
                'old_names',
                'urban_rural',
                'status',
                'population',
                'is_active',
                'updated_at',
            ]
        );
    }

    private function validateWorksheet(
        Worksheet $worksheet
    ): void {
        $expectedHeaders = [
            'A1' => '10-digit PSGC',
            'B1' => 'Name',
            'C1' => 'Correspondence Code',
            'D1' => 'Geographic Level',
        ];

        foreach ($expectedHeaders as $cell => $expected) {
            $actual = trim(
                (string) $worksheet
                    ->getCell($cell)
                    ->getValue()
            );

            if ($actual !== $expected) {
                throw new RuntimeException(
                    "Unexpected PSGC spreadsheet format. "
                    ."Expected '{$expected}' in {$cell}, "
                    ."but found '{$actual}'."
                );
            }
        }
    }

    private function normalizeCode(
        mixed $value,
        int $length
    ): ?string {
        if ($value === null || $value === '') {
            return null;
        }

        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $value = number_format(
                (float) $value,
                0,
                '',
                ''
            );
        }

        $digits = preg_replace(
            '/\D/',
            '',
            $value
        );

        if (! $digits) {
            return null;
        }

        return str_pad(
            $digits,
            $length,
            '0',
            STR_PAD_LEFT
        );
    }

    private function nullableString(
        mixed $value
    ): ?string {
        if ($value === null) {
            return null;
        }

        $value = trim(
            preg_replace(
                '/\s+/u',
                ' ',
                (string) $value
            ) ?? ''
        );

        return $value !== ''
            ? $value
            : null;
    }

    private function nullableInteger(
        mixed $value
    ): ?int {
        if (
            $value === null
            || $value === ''
            || ! is_numeric($value)
        ) {
            return null;
        }

        return (int) round(
            (float) $value
        );
    }

    private function requireContext(
        ?int $id,
        string $message
    ): void {
        if (! $id) {
            throw new RuntimeException(
                $message
            );
        }
    }
}