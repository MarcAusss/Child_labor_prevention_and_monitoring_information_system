<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $actor = $request->user();
        $roles = $this->allowedRoles($actor);

        $search = trim((string) $request->query('search', ''));
        $roleId = $request->integer('role_id');
        $status = (string) $request->query('status', '');

        $allowedRoleIds = $roles
            ->pluck('id')
            ->all();

        $users = User::query()
            ->with('role')
            ->whereIn('role_id', $allowedRoleIds)
            ->when(
                $search !== '',
                function (Builder $query) use ($search): void {
                    $query->where(function (Builder $subQuery) use ($search): void {
                        $subQuery
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhereHas(
                                'role',
                                function (Builder $roleQuery) use ($search): void {
                                    $roleQuery->where(
                                        'name',
                                        'like',
                                        "%{$search}%"
                                    );
                                }
                            );
                    });
                }
            )
            ->when(
                $roleId > 0 && in_array($roleId, $allowedRoleIds, true),
                fn (Builder $query): Builder => $query->where(
                    'role_id',
                    $roleId
                )
            )
            ->when(
                $status === 'active',
                fn (Builder $query): Builder => $query->where(
                    'is_active',
                    true
                )
            )
            ->when(
                $status === 'inactive',
                fn (Builder $query): Builder => $query->where(
                    'is_active',
                    false
                )
            )
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles,
            'search' => $search,
            'selectedRoleId' => $roleId,
            'selectedStatus' => $status,
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.users.create', [
            'roles' => $this->allowedRoles($request->user()),
        ]);
    }

    public function store(
        StoreUserRequest $request
    ): RedirectResponse {
        $validated = $request->validated();

        $user = User::query()->create([
            'role_id' => $validated['role_id'],
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'password' => Hash::make($validated['password']),
            'is_active' => $validated['is_active'],
        ]);

        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();

        return redirect()
            ->route('admin.users.index')
            ->with(
                'success',
                "The account for {$user->name} was created successfully."
            );
    }

    public function edit(
        Request $request,
        User $user
    ): View {
        $this->ensureCanManageTarget(
            $request->user(),
            $user
        );

        return view('admin.users.edit', [
            'managedUser' => $user->load('role'),
            'roles' => $this->allowedRoles($request->user()),
        ]);
    }

    public function update(
        UpdateUserRequest $request,
        User $user
    ): RedirectResponse {
        $actor = $request->user();
        $validated = $request->validated();

        $this->ensureCanManageTarget($actor, $user);

        $newRole = Role::query()
            ->whereKey($validated['role_id'])
            ->where('is_active', true)
            ->firstOrFail();

        if ($actor->is($user)) {
            if ((int) $validated['role_id'] !== (int) $user->role_id) {
                throw ValidationException::withMessages([
                    'role_id' => 'You cannot change your own role.',
                ]);
            }

            if (! $validated['is_active']) {
                throw ValidationException::withMessages([
                    'is_active' => 'You cannot deactivate your own account.',
                ]);
            }
        }

        $this->preventLastSuperAdminRemoval(
            $user,
            $newRole,
            (bool) $validated['is_active']
        );

        $emailChanged = strtolower($validated['email']) !== strtolower(
            $user->email
        );

        $user->fill([
            'role_id' => $newRole->id,
            'name' => $validated['name'],
            'email' => strtolower($validated['email']),
            'is_active' => $validated['is_active'],
        ]);

        if ($emailChanged) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with(
                'success',
                "The account for {$user->name} was updated successfully."
            );
    }

    public function toggleStatus(
        Request $request,
        User $user
    ): RedirectResponse {
        $actor = $request->user();

        $this->ensureCanManageTarget($actor, $user);

        if ($actor->is($user)) {
            return back()->withErrors([
                'status' => 'You cannot deactivate your own account.',
            ]);
        }

        $newStatus = ! $user->is_active;

        if (
            $user->isSuperAdmin()
            && $user->is_active
            && ! $newStatus
            && $this->activeSuperAdminCount() <= 1
        ) {
            return back()->withErrors([
                'status' => 'The final active Super Admin cannot be deactivated.',
            ]);
        }

        $user->update([
            'is_active' => $newStatus,
        ]);

        $message = $newStatus
            ? "{$user->name}'s account has been activated."
            : "{$user->name}'s account has been deactivated.";

        return back()->with('success', $message);
    }

    public function resetPassword(
        Request $request,
        User $user
    ): RedirectResponse {
        $this->ensureCanManageTarget(
            $request->user(),
            $user
        );

        $validated = $request->validate([
            'password' => [
                'required',
                'confirmed',
                Password::min(12)
                    ->letters()
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],
        ]);

        $user->forceFill([
            'password' => Hash::make($validated['password']),
        ])->save();

        return redirect()
            ->route('admin.users.edit', $user)
            ->with(
                'success',
                "The password for {$user->name} was reset successfully."
            );
    }

    private function allowedRoles(User $actor): Collection
    {
        $query = Role::query()
            ->where('is_active', true)
            ->orderBy('name');

        if ($actor->isAdmin()) {
            $query->whereIn('slug', [
                Role::PROFILING_OFFICER,
                Role::VIEWER,
            ]);
        }

        return $query->get();
    }

    private function ensureCanManageTarget(
        User $actor,
        User $target
    ): void {
        if ($actor->isSuperAdmin()) {
            return;
        }

        $allowed = $actor->isAdmin()
            && $target->hasAnyRole([
                Role::PROFILING_OFFICER,
                Role::VIEWER,
            ]);

        abort_unless(
            $allowed,
            403,
            'You are not authorized to manage this account.'
        );
    }

    private function preventLastSuperAdminRemoval(
        User $user,
        Role $newRole,
        bool $willRemainActive
    ): void {
        if (! $user->isSuperAdmin() || ! $user->is_active) {
            return;
        }

        $removesSuperAdminAccess = (
            $newRole->slug !== Role::SUPER_ADMIN
            || ! $willRemainActive
        );

        if (
            $removesSuperAdminAccess
            && $this->activeSuperAdminCount() <= 1
        ) {
            throw ValidationException::withMessages([
                'role_id' => 'The final active Super Admin cannot be deactivated or assigned another role.',
            ]);
        }
    }

    private function activeSuperAdminCount(): int
    {
        return User::query()
            ->where('is_active', true)
            ->whereHas(
                'role',
                fn (Builder $query): Builder => $query->where(
                    'slug',
                    Role::SUPER_ADMIN
                )
            )
            ->count();
    }
}