<?php

namespace App\Services\ChildLaborImport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

class WorkbookReader
{
    public function __construct(private ValueNormalizer $normalizer)
    {
    }

    public function read(string $absolutePath): array
    {
        $reader = IOFactory::createReaderForFile($absolutePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($absolutePath);
        $sheet = $spreadsheet->getSheet(0);

        $expected = (int) config('child_labor_import.expected_column_count', 129);
        if ($sheet->getHighestColumn() && \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($sheet->getHighestColumn()) < $expected) {
            throw new RuntimeException("The worksheet has fewer than {$expected} columns.");
        }

        $rows = [];
        $mapping = config('child_labor_import.columns', []);
        $firstDataRow = (int) config('child_labor_import.first_data_row', 2);

        for ($rowNumber = $firstDataRow; $rowNumber <= $sheet->getHighestDataRow(); $rowNumber++) {
            $data = [];
            foreach ($mapping as $column => $key) {
                $coordinate = Coordinate::stringFromColumnIndex($column) . $rowNumber;

                $data[$key] = $this->normalizer->clean(
                    $sheet->getCell($coordinate)->getValue()
                );
            }
            if ($this->isEmpty($data))
                continue;
            $rows[] = ['sheet_row' => $rowNumber, 'data' => $this->normalize($data)];
        }

        $spreadsheet->disconnectWorksheets();
        unset($spreadsheet);
        return $rows;
    }

    private function normalize(array $data): array
    {
        $name = $this->normalizer->text($data['full_name'] ?? null);
        $data = array_map(fn($value) => $this->normalizer->text($value), $data);
        $data = array_merge($data, $this->normalizer->nameParts($name));

        foreach (['birth_date', 'interview_date', 'referral_date', 'service_date_initial', 'service_date_2022', 'service_date_2023', 'service_date_2024', 'monitoring_date'] as $key) {
            $data[$key] = $this->normalizer->date($data[$key] ?? null);
        }
        foreach (['height_cm', 'weight_kg', 'working_hours_per_day', 'working_days_per_week', 'age_started_working', 'average_monthly_income', 'recorded_age'] as $key) {
            $data[$key] = $this->normalizer->number($data[$key] ?? null);
        }
        foreach (['is_indigenous_person', 'ever_attended_school', 'currently_attending_school', 'has_disability', 'needs_disability_assessment', 'needs_medical_assessment', 'adult_supervision', 'is_4ps_household', 'needs_assessed', 'already_referred', 'already_provided_services', 'withdrawn_from_child_labor'] as $key) {
            $data[$key] = $this->normalizer->yesNo($data[$key] ?? null);
        }

        $data['family_members'] = $this->normalizer->grouped($data, 'family');
        $data['availed_services'] = $this->normalizer->grouped($data, 'availed_service');
        $data['requested_services'] = $this->normalizer->grouped($data, 'requested_service');
        return $data;
    }

    private function isEmpty(array $data): bool
    {
        foreach ($data as $value)
            if ($this->normalizer->text($value) !== null)
                return false;
        return true;
    }
}
