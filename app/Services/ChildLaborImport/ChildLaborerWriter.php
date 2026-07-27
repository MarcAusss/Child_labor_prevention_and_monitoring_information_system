<?php

namespace App\Services\ChildLaborImport;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class ChildLaborerWriter
{
    public function write(array $data, int $userId): array
    {
        if (!Schema::hasTable('child_laborers')) throw new RuntimeException('The child_laborers table does not exist.');

        $columns = Schema::getColumnListing('child_laborers');
        $candidate = [
            'child_id_number' => $data['child_id_number'] ?? null,
            'profile_number' => $data['child_id_number'] ?? null,
            'first_name' => $data['first_name'] ?? null,
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'suffix' => $data['suffix'] ?? null,
            'sex' => $this->sex($data['sex'] ?? null),
            'birth_date' => $data['birth_date'] ?? null,
            'contact_number' => $data['contact_number'] ?? null,
            'religion' => $data['religion'] ?? null,
            'status' => 'Draft',
            'created_by' => $userId,
            'updated_by' => $userId,
            'imported_data' => json_encode($data, JSON_UNESCAPED_UNICODE),
            'updated_at' => now(),
        ];
        $payload = array_intersect_key($candidate, array_flip($columns));
        if (in_array('created_at', $columns, true)) $payload['created_at'] = now();

        $lookupColumn = null;
        foreach (['child_id_number', 'profile_number', 'reference_number'] as $column) {
            if (in_array($column, $columns, true)) { $lookupColumn = $column; break; }
        }
        if (!$lookupColumn) throw new RuntimeException('No supported child ID column exists in child_laborers.');

        $existing = DB::table('child_laborers')->where($lookupColumn, $data['child_id_number'])->first();
        if ($existing) {
            unset($payload['created_at'], $payload['created_by']);
            DB::table('child_laborers')->where('id', $existing->id)->update($payload);
            return ['action' => 'updated', 'id' => $existing->id];
        }

        $id = DB::table('child_laborers')->insertGetId($payload);
        return ['action' => 'created', 'id' => $id];
    }

    private function sex(?string $value): ?string
    {
        return match (strtolower(trim((string) $value))) {
            'm', 'male' => 'Male',
            'f', 'female' => 'Female',
            default => $value,
        };
    }
}
