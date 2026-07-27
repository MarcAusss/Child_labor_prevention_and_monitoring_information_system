<?php

namespace App\Services\ChildLaborImport;

use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ValueNormalizer
{
    public function clean(mixed $value): mixed
    {
        if ($value === null) return null;
        if (is_string($value)) {
            $value = preg_replace('/\R/u', "\n", trim($value));
            if ($this->isPlaceholder($value)) return null;
        }
        return $value;
    }

    public function text(mixed $value): ?string
    {
        $value = $this->clean($value);
        if ($value === null) return null;
        return trim((string) $value);
    }

    public function number(mixed $value): ?float
    {
        $value = $this->clean($value);
        if ($value === null) return null;
        if (is_numeric($value)) return (float) $value;
        $candidate = preg_replace('/[^0-9.\-]/', '', (string) $value);
        return is_numeric($candidate) ? (float) $candidate : null;
    }

    public function date(mixed $value): ?string
    {
        $value = $this->clean($value);
        if ($value === null) return null;
        try {
            if (is_numeric($value) && (float) $value > 1) {
                return ExcelDate::excelToDateTimeObject((float) $value)->format('Y-m-d');
            }
            $text = str_replace('.', '/', (string) $value);
            return Carbon::parse($text)->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    public function yesNo(mixed $value): ?bool
    {
        $text = strtolower((string) $this->text($value));
        if ($text === '') return null;
        if (in_array($text, ['yes', 'y', '1', 'true', 'oo'], true)) return true;
        if (in_array($text, ['no', 'n', '0', 'false', 'hindi'], true)) return false;
        return null;
    }

    public function nameParts(?string $name): array
    {
        $name = trim((string) $name);
        if ($name === '') return ['first_name' => null, 'middle_name' => null, 'last_name' => null, 'suffix' => null];
        if (!str_contains($name, ',')) {
            return ['first_name' => $name, 'middle_name' => null, 'last_name' => null, 'suffix' => null];
        }
        [$last, $given] = array_map('trim', explode(',', $name, 2));
        $parts = preg_split('/\s+/', $given) ?: [];
        $first = array_shift($parts);
        $suffix = null;
        if ($parts && preg_match('/^(Jr\.?|Sr\.?|II|III|IV)$/i', end($parts))) $suffix = array_pop($parts);
        return [
            'first_name' => $first ?: null,
            'middle_name' => $parts ? implode(' ', $parts) : null,
            'last_name' => $last ?: null,
            'suffix' => $suffix,
        ];
    }

    public function grouped(array $data, string $prefix, int $count = 12): array
    {
        $items = [];
        for ($i = 1; $i <= $count; $i++) {
            $value = $this->text($data["{$prefix}_{$i}"] ?? null);
            if ($value !== null) $items[] = ['sequence' => $i, 'raw_text' => $value];
        }
        return $items;
    }

    private function isPlaceholder(string $value): bool
    {
        return in_array(strtolower(trim($value)), config('child_labor_import.placeholder_values', []), true);
    }
}
