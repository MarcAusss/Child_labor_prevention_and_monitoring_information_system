<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BackupRun extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_RUNNING = 'running';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    public const STATUS_PRUNED = 'pruned';

    protected $fillable = [
        'created_by',
        'backup_type',
        'status',
        'disk',
        'file_path',
        'file_name',
        'file_size',
        'checksum_sha256',
        'manifest',
        'error_message',
        'started_at',
        'completed_at',
        'verified_at',
        'pruned_at',
    ];

    protected function casts(): array
    {
        return [
            'manifest' => 'array',
            'file_size' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'verified_at' => 'datetime',
            'pruned_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function isDownloadable(): bool
    {
        return $this->status
            === self::STATUS_COMPLETED
            && filled($this->file_path);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = (int) (
            $this->file_size ?? 0
        );

        if ($bytes <= 0) {
            return '—';
        }

        $units = [
            'B',
            'KB',
            'MB',
            'GB',
            'TB',
        ];

        $power = min(
            (int) floor(
                log(
                    max($bytes, 1),
                    1024
                )
            ),
            count($units) - 1
        );

        return number_format(
            $bytes / (1024 ** $power),
            $power === 0 ? 0 : 2
        ).' '.$units[$power];
    }
}
