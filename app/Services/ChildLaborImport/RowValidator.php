<?php

namespace App\Services\ChildLaborImport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RowValidator
{
    public function validate(array $data): array
    {
        $errors = [];
        $warnings = [];
        foreach (config('child_labor_import.required_fields', []) as $field) {
            if (($data[$field] ?? null) === null || ($data[$field] ?? '') === '') $errors[] = "Missing required field: {$field}.";
        }
        if (($data['birth_date'] ?? null) === null) $errors[] = 'Date of birth is invalid or unreadable.';
        if (($data['sex'] ?? null) && !in_array(strtolower($data['sex']), ['male', 'female', 'm', 'f'], true)) $warnings[] = 'Sex value is not a recognized Male/Female value.';
        foreach (['height_cm', 'weight_kg', 'working_hours_per_day', 'working_days_per_week'] as $field) {
            if (($data[$field] ?? null) !== null && !is_numeric($data[$field])) $warnings[] = "{$field} is not numeric.";
        }
        if (($data['is_4ps_household'] ?? null) === true && empty($data['household_id_number'])) $warnings[] = '4Ps is marked Yes but Household ID Number is blank.';
        if (($data['already_referred'] ?? null) === true && empty($data['referral_agencies'])) $warnings[] = 'Referral is marked Yes but no agency is listed.';
        if (($data['withdrawn_from_child_labor'] ?? null) === true && empty($data['withdrawal_indicator'])) $warnings[] = 'Withdrawn is marked Yes but the withdrawal indicator is blank.';

        if (Schema::hasTable('child_laborers')) {
            $idColumn = $this->firstExistingColumn('child_laborers', ['child_id_number', 'profile_number', 'reference_number']);
            if ($idColumn && !empty($data['child_id_number']) && DB::table('child_laborers')->where($idColumn, $data['child_id_number'])->exists()) {
                $warnings[] = 'A child laborer with the same ID already exists and will be treated as an update candidate.';
            }
        }

        return ['errors' => $errors, 'warnings' => $warnings, 'status' => $errors ? 'error' : ($warnings ? 'warning' : 'valid')];
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) if (Schema::hasColumn($table, $column)) return $column;
        return null;
    }
}
