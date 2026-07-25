<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChildLaborerImport extends Model
{
    protected $fillable = [
        'uploaded_by', 'assigned_to', 'original_filename', 'stored_path',
        'status', 'total_rows', 'valid_rows', 'imported_rows',
        'duplicate_rows', 'failed_rows', 'errors', 'completed_at',
    ];

    protected function casts(): array
    {
        return ['errors' => 'array', 'completed_at' => 'datetime'];
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
