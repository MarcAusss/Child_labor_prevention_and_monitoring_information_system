@php
    $isEditing = isset($managedUser) && $managedUser;
    $isSelf = $isEditing && auth()->id() === $managedUser->id;
@endphp

<div class="space-y-6">
    <div>
        <label
            for="name"
            class="block text-sm font-semibold text-slate-700"
        >
            Full Name
        </label>

        <input
            id="name"
            name="name"
            type="text"
            value="{{ old('name', $managedUser->name ?? '') }}"
            required
            autofocus
            class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
        >

        @error('name')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="email"
            class="block text-sm font-semibold text-slate-700"
        >
            Email Address
        </label>

        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $managedUser->email ?? '') }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
        >

        @error('email')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="role_id"
            class="block text-sm font-semibold text-slate-700"
        >
            User Role
        </label>

        @if ($isSelf)
            <input
                type="hidden"
                name="role_id"
                value="{{ $managedUser->role_id }}"
            >

            <select
                id="role_id"
                disabled
                class="mt-2 block w-full rounded-xl border-slate-300 bg-slate-100 text-slate-500 shadow-sm"
            >
                <option>
                    {{ $managedUser->role?->name }}
                </option>
            </select>

            <p class="mt-2 text-xs text-slate-500">
                You cannot change your own role.
            </p>
        @else
            <select
                id="role_id"
                name="role_id"
                required
                class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
            >
                <option value="">
                    Select a role
                </option>

                @foreach ($roles as $role)
                    <option
                        value="{{ $role->id }}"
                        @selected(
                            (int) old(
                                'role_id',
                                $managedUser->role_id ?? 0
                            ) === $role->id
                        )
                    >
                        {{ $role->name }}
                    </option>
                @endforeach
            </select>
        @endif

        @error('role_id')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    @unless ($isEditing)
        <div>
            <label
                for="password"
                class="block text-sm font-semibold text-slate-700"
            >
                Temporary Password
            </label>

            <input
                id="password"
                name="password"
                type="password"
                required
                autocomplete="new-password"
                class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
            >

            <p class="mt-2 text-xs leading-5 text-slate-500">
                Use at least 12 characters with uppercase and lowercase
                letters, a number, and a symbol.
            </p>

            @error('password')
                <p class="mt-2 text-sm text-red-600">
                    {{ $message }}
                </p>
            @enderror
        </div>

        <div>
            <label
                for="password_confirmation"
                class="block text-sm font-semibold text-slate-700"
            >
                Confirm Password
            </label>

            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                required
                autocomplete="new-password"
                class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
            >
        </div>
    @endunless

    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5">
        <div class="flex items-start gap-3">
            <input type="hidden" name="can_import_child_laborers" value="0">
            <input
                id="can_import_child_laborers"
                name="can_import_child_laborers"
                type="checkbox"
                value="1"
                @checked((bool) old('can_import_child_laborers', $managedUser->can_import_child_laborers ?? false))
                class="mt-1 rounded border-slate-300 text-sky-600 focus:ring-sky-500"
            >
            <div>
                <label for="can_import_child_laborers" class="text-sm font-bold text-slate-800">
                    Allow spreadsheet import
                </label>
                <p class="mt-1 text-xs leading-5 text-slate-600">
                    This permission is used only for Profiling Officers. Admin and Super Admin already have import access.
                </p>
            </div>
        </div>
    </div>

    <div>
        <label
            for="is_active"
            class="block text-sm font-semibold text-slate-700"
        >
            Account Status
        </label>

        @if ($isSelf)
            <input
                type="hidden"
                name="is_active"
                value="1"
            >

            <select
                disabled
                class="mt-2 block w-full rounded-xl border-slate-300 bg-slate-100 text-slate-500 shadow-sm"
            >
                <option>Active</option>
            </select>

            <p class="mt-2 text-xs text-slate-500">
                You cannot deactivate your own account.
            </p>
        @else
            <select
                id="is_active"
                name="is_active"
                required
                class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
            >
                <option
                    value="1"
                    @selected(
                        (string) old(
                            'is_active',
                            isset($managedUser)
                                ? (int) $managedUser->is_active
                                : 1
                        ) === '1'
                    )
                >
                    Active
                </option>

                <option
                    value="0"
                    @selected(
                        (string) old(
                            'is_active',
                            isset($managedUser)
                                ? (int) $managedUser->is_active
                                : 1
                        ) === '0'
                    )
                >
                    Inactive
                </option>
            </select>
        @endif

        @error('is_active')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>