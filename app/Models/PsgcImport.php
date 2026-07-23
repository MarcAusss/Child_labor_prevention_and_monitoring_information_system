<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PsgcImport extends Model
{
    public const STATUS_RUNNING = 'Running';

    public const STATUS_COMPLETED = 'Completed';

    public const STATUS_FAILED = 'Failed';

    protected $fillable = [
        'imported_by',
        'version',
        'source_filename',
        'source_url',
        'file_sha256',
        'status',
        'record_counts',
        'error_message',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'record_counts' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function importedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'imported_by'
        );
    }
}