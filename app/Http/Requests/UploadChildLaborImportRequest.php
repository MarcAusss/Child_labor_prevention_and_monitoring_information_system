<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadChildLaborImportRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (!$user || !$user->is_active) {
            return false;
        }

        // Super Administrator and Administrator role IDs.
        if (in_array((int) $user->role_id, [1, 2], true)) {
            return true;
        }

        // Profiling Officers need explicit import permission.
        return (bool) $user->can_import_child_laborers;
    }

    public function rules(): array
    {
        return [
            'spreadsheet' => [
                'required',
                'file',
                'mimes:xlsx,xls',
                'max:' . config('child_labor_import.max_file_kb', 20480),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'spreadsheet.required' => 'Please select a spreadsheet.',
            'spreadsheet.uploaded' => 'PHP could not receive the spreadsheet. Check file_uploads, upload_tmp_dir, upload_max_filesize, and post_max_size in php.ini.',
            'spreadsheet.file' => 'The selected spreadsheet is not a valid uploaded file.',
            'spreadsheet.mimes' => 'Only XLSX and XLS spreadsheet files are accepted.',
            'spreadsheet.max' => 'The spreadsheet exceeds the allowed upload size.',
        ];
    }
}