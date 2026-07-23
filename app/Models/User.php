<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
            'password' => 'hashed',
        ];
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
}