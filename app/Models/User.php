<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Arr;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'is_active',
        'can_import_child_laborers',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'is_active' => 'boolean',
            'can_import_child_laborers' => 'boolean',
            'password' => 'hashed',
        ];
    }

    public function createdChildLaborers(): HasMany
    {
        return $this->hasMany(
            ChildLaborer::class,
            'created_by'
        );
    }

    public function assignedChildLaborers(): HasMany
    {
        return $this->hasMany(
            ChildLaborer::class,
            'assigned_to'
        );
    }

    public function reviewedChildLaborers(): HasMany
    {
        return $this->hasMany(
            ChildLaborer::class,
            'reviewed_by'
        );
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function hasRole(string $role): bool
    {
        return $this->role?->slug === $role;
    }

    public function hasAnyRole(string|array $roles): bool
    {
        return in_array(
            $this->role?->slug,
            Arr::wrap($roles),
            true
        );
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::ADMIN);
    }

    public function isProfilingOfficer(): bool
    {
        return $this->hasRole(Role::PROFILING_OFFICER);
    }

    public function isViewer(): bool
    {
        return $this->hasRole(Role::VIEWER);
    }

    public function canManageUsers(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::ADMIN,
        ]);
    }

    public function canManageAudits(): bool
    {
        return $this->hasAnyRole([
            Role::SUPER_ADMIN,
            Role::ADMIN,
        ]);
    }

    public function canImportChildLaborers(): bool
    {
        return $this->isSuperAdmin()
            || $this->isAdmin()
            || ($this->isProfilingOfficer() && $this->can_import_child_laborers);
    }

    public function childLaborerImports(): HasMany
    {
        return $this->hasMany(ChildLaborerImport::class, 'uploaded_by');
    }
    public function activityLogs(): HasMany
    {
        return $this->hasMany(
            ActivityLog::class,
            'user_id',
            'id'
        );
    }

    public function createdAuditSchedules(): HasMany
    {
        return $this->hasMany(
            AuditSchedule::class,
            'created_by',
            'id'
        );
    }

    public function assignedAuditSchedules(): HasMany
    {
        return $this->hasMany(
            AuditSchedule::class,
            'assigned_to',
            'id'
        );
    }

    public function auditEvaluations(): HasMany
    {
        return $this->hasMany(
            AuditEvaluation::class,
            'evaluated_by',
            'id'
        );
    }

    public function updatedAuditEvaluations(): HasMany
    {
        return $this->hasMany(
            AuditEvaluation::class,
            'updated_by',
            'id'
        );
    }
}