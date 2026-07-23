<?php

namespace App\Models;

use App\Policies\ChildLaborerPolicy;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

#[UsePolicy(ChildLaborerPolicy::class)]
class ChildLaborer extends Model
{
    use SoftDeletes;

    public const STATUS_DRAFT = 'Draft';

    public const STATUS_SUBMITTED = 'Submitted';

    public const STATUS_RETURNED = 'Returned';

    public const STATUS_APPROVED = 'Approved';

    public const STATUS_ARCHIVED = 'Archived';

    protected $fillable = [
        'profile_number',
        'created_by',
        'assigned_to',
        'reviewed_by',

        'first_name',
        'middle_name',
        'last_name',
        'suffix',
        'sex',
        'birth_date',
        'civil_status',
        'nationality',
        'religion',
        'contact_number',
        'photo_path',

        'duplicate_key',
        'status',
        'status_before_archive',
        'return_reason',
        'archive_reason',

        'submitted_at',
        'returned_at',
        'approved_at',
        'archived_at',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'submitted_at' => 'datetime',
            'returned_at' => 'datetime',
            'approved_at' => 'datetime',
            'archived_at' => 'datetime',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_DRAFT,
            self::STATUS_SUBMITTED,
            self::STATUS_RETURNED,
            self::STATUS_APPROVED,
            self::STATUS_ARCHIVED,
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }

    public function assignedOfficer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'reviewed_by'
        );
    }

    public function scopeActiveProfiles(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            '!=',
            self::STATUS_ARCHIVED
        );
    }

    public function scopeArchived(
        Builder $query
    ): Builder {
        return $query->where(
            'status',
            self::STATUS_ARCHIVED
        );
    }

    public function getFullNameAttribute(): string
    {
        return collect([
            $this->first_name,
            $this->middle_name,
            $this->last_name,
            $this->suffix,
        ])
            ->filter()
            ->implode(' ');
    }

    public function getAgeAttribute(): int
    {
        return $this->birth_date
            ? $this->birth_date->age
            : 0;
    }

    public function isEditableByProfilingOfficer(): bool
    {
        return in_array(
            $this->status,
            [
                self::STATUS_DRAFT,
                self::STATUS_RETURNED,
            ],
            true
        );
    }

    public static function makeDuplicateKey(
        array $attributes
    ): string {
        $birthDate = $attributes['birth_date'] ?? null;

        if ($birthDate instanceof CarbonInterface) {
            $birthDate = $birthDate->format('Y-m-d');
        } elseif ($birthDate) {
            $birthDate = Carbon::parse(
                $birthDate
            )->format('Y-m-d');
        }

        $values = [
            self::normalizeDuplicateValue(
                $attributes['first_name'] ?? null
            ),

            self::normalizeDuplicateValue(
                $attributes['middle_name'] ?? null
            ),

            self::normalizeDuplicateValue(
                $attributes['last_name'] ?? null
            ),

            self::normalizeDuplicateValue(
                $attributes['suffix'] ?? null
            ),

            $birthDate,
        ];

        return hash(
            'sha256',
            implode('|', $values)
        );
    }

    private static function normalizeDuplicateValue(
        mixed $value
    ): string {
        $value = Str::ascii(
            trim((string) $value)
        );

        $value = preg_replace(
            '/\s+/',
            ' ',
            $value
        ) ?? '';

        return Str::lower($value);
    }
    public function birthInformation(): HasOne
    {
        return $this->hasOne(BirthInformation::class);
    }

    public function residentialAddress(): HasOne
    {
        return $this->hasOne(ResidentialAddress::class);
    }
    public function parentGuardians(): HasMany
    {
        return $this->hasMany(
            ParentGuardian::class,
            'child_laborer_id',
            'id'
        );
    }

    public function primaryGuardian(): HasOne
    {
        return $this->hasOne(
            ParentGuardian::class,
            'child_laborer_id',
            'id'
        )->where('is_primary', true);
    }

    public function householdMembers(): HasMany
    {
        return $this->hasMany(
            HouseholdMember::class,
            'child_laborer_id',
            'id'
        );
    }

    public function educationRecords(): HasMany
    {
        return $this->hasMany(
            EducationRecord::class,
            'child_laborer_id',
            'id'
        );
    }

    public function currentEducation(): HasOne
    {
        return $this->hasOne(
            EducationRecord::class,
            'child_laborer_id',
            'id'
        )->where('is_current', true);
    }

    public function employmentRecords(): HasMany
    {
        return $this->hasMany(
            EmploymentRecord::class,
            'child_laborer_id',
            'id'
        );
    }

    public function currentEmployment(): HasOne
    {
        return $this->hasOne(
            EmploymentRecord::class,
            'child_laborer_id',
            'id'
        )->where('is_current', true);
    }

    public function workHazards(): HasManyThrough
    {
        return $this->hasManyThrough(
            WorkHazard::class,
            EmploymentRecord::class,
            'child_laborer_id',
            'employment_record_id',
            'id',
            'id'
        );
    }

    public function healthInformationRecords(): HasMany
    {
        return $this->hasMany(
            HealthInformation::class,
            'child_laborer_id',
            'id'
        );
    }

    public function currentHealthInformation(): HasOne
    {
        return $this->hasOne(
            HealthInformation::class,
            'child_laborer_id',
            'id'
        )->where('is_current', true);
    }

    public function interventions(): HasMany
    {
        return $this->hasMany(
            Intervention::class,
            'child_laborer_id',
            'id'
        );
    }

    public function documents(): HasMany
    {
        return $this->hasMany(
            ChildLaborerDocument::class,
            'child_laborer_id',
            'id'
        );
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(
            ActivityLog::class,
            'child_laborer_id',
            'id'
        );
    }
}