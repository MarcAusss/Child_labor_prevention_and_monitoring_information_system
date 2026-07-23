<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ChildLaborerDocument extends Model
{
    use SoftDeletes;

    public const TYPE_PROFILE_IDENTIFICATION =
        'Profile and Identification';

    public const TYPE_BIRTH_REGISTRATION =
        'Birth and Civil Registration';

    public const TYPE_EDUCATION =
        'Education Document';

    public const TYPE_EMPLOYMENT =
        'Employment Document';

    public const TYPE_HEALTH_MEDICAL =
        'Health and Medical Document';

    public const TYPE_INTERVENTION =
        'Intervention and Assistance Document';

    public const TYPE_REFERRAL =
        'Referral and Case Management';

    public const TYPE_ASSESSMENT =
        'Assessment and Monitoring';

    public const TYPE_LEGAL =
        'Legal and Compliance Document';

    public const TYPE_PHOTO =
        'Photographic Evidence';

    public const TYPE_CORRESPONDENCE =
        'Correspondence';

    public const TYPE_OTHER =
        'Other Document';

    protected $fillable = [
        'child_laborer_id',
        'uploaded_by',
        'deleted_by',
        'document_type',
        'original_name',
        'stored_name',
        'file_path',
        'mime_type',
        'extension',
        'file_size',
        'checksum_sha256',
        'description',
        'is_confidential',
        'download_count',
        'last_downloaded_at',
        'uploaded_at',
    ];

    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
            'is_confidential' => 'boolean',
            'download_count' => 'integer',
            'last_downloaded_at' => 'datetime',
            'uploaded_at' => 'datetime',
        ];
    }

    public static function documentTypes(): array
    {
        return [
            self::TYPE_PROFILE_IDENTIFICATION,
            self::TYPE_BIRTH_REGISTRATION,
            self::TYPE_EDUCATION,
            self::TYPE_EMPLOYMENT,
            self::TYPE_HEALTH_MEDICAL,
            self::TYPE_INTERVENTION,
            self::TYPE_REFERRAL,
            self::TYPE_ASSESSMENT,
            self::TYPE_LEGAL,
            self::TYPE_PHOTO,
            self::TYPE_CORRESPONDENCE,
            self::TYPE_OTHER,
        ];
    }

    public function childLaborer(): BelongsTo
    {
        return $this->belongsTo(
            ChildLaborer::class,
            'child_laborer_id',
            'id'
        );
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'uploaded_by',
            'id'
        );
    }

    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'deleted_by',
            'id'
        );
    }

    public function scopeVisibleTo(
        Builder $query,
        User $user
    ): Builder {
        if ($user->isViewer()) {
            return $query->where(
                'is_confidential',
                false
            );
        }

        return $query;
    }

    public function getFormattedFileSizeAttribute(): string
    {
        $bytes = max(
            0,
            (int) $this->file_size
        );

        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return number_format(
                $bytes / 1024,
                2
            ).' KB';
        }

        if ($bytes < 1024 * 1024 * 1024) {
            return number_format(
                $bytes / (1024 * 1024),
                2
            ).' MB';
        }

        return number_format(
            $bytes / (1024 * 1024 * 1024),
            2
        ).' GB';
    }

    public function getDisplayExtensionAttribute(): string
    {
        return strtoupper(
            $this->extension ?: 'FILE'
        );
    }
}