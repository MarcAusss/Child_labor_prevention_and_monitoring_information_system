<x-dashboard-shell
    title="User Management"
    subtitle="Create, edit, activate, and deactivate CLPMIS accounts."
    badge="{{ auth()->user()->role?->name }}"
>
    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200 bg-red-50 px-5 py-4"
        >
            <ul class="space-y-1 text-sm font-semibold text-red-700">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-6">
            <div
                class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between"
            >
                <form
                    method="GET"
                    action="{{ route('admin.users.index') }}"
                    class="grid flex-1 gap-3 md:grid-cols-4"
                >
                    <input
                        type="search"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search name or email"
                        class="rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500 md:col-span-2"
                    >

                    <select
                        name="role_id"
                        class="rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    >
                        <option value="">
                            All Roles
                        </option>

                        @foreach ($roles as $role)
                            <option
                                value="{{ $role->id }}"
                                @selected($selectedRoleId === $role->id)
                            >
                                {{ $role->name }}
                            </option>
                        @endforeach
                    </select>

                    <select
                        name="status"
                        class="rounded-xl border-slate-300 focus:border-sky-500 focus:ring-sky-500"
                    >
                        <option value="">
                            All Statuses
                        </option>

                        <option
                            value="active"
                            @selected($selectedStatus === 'active')
                        >
                            Active
                        </option>

                        <option
                            value="inactive"
                            @selected($selectedStatus === 'inactive')
                        >
                            Inactive
                        </option>
                    </select>

                    <div class="flex gap-2 md:col-span-4">
                        <button
                            type="submit"
                            class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-bold text-white hover:bg-sky-700"
                        >
                            Filter
                        </button>

                        <a
                            href="{{ route('admin.users.index') }}"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50"
                        >
                            Reset
                        </a>
                    </div>
                </form>

                <a
                    href="{{ route('admin.users.create') }}"
                    class="inline-flex justify-center rounded-xl bg-sky-600 px-5 py-3 text-sm font-bold text-white hover:bg-sky-700"
                >
                    Create User
                </a>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-sky-800"
                        >
                            User
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-sky-800"
                        >
                            Role
                        </th>

                        <th
                            class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-sky-800"
                        >
                            Status
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-sky-800"
                        >
                            Last Login
                        </th>

                        <th
                            class="px-6 py-4 text-right text-xs font-bold uppercase tracking-wider text-sky-800"
                        >
                            Actions
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($users as $managedUser)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $managedUser->name }}

                                    @if (auth()->id() === $managedUser->id)
                                        <span class="text-xs text-sky-600">
                                            (You)
                                        </span>
                                    @endif
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $managedUser->email }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $managedUser->role?->name ?? 'No Role' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($managedUser->is_active)
                                    <span
                                        class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200"
                                    >
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-200"
                                    >
                                        Inactive
                                    </span>
                                @endif
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $managedUser->last_login_at?->format('M d, Y h:i A') ?? 'Never' }}
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a
                                        href="{{ route('admin.users.edit', $managedUser) }}"
                                        class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700 hover:bg-sky-100"
                                    >
                                        Edit
                                    </a>

                                    @if (auth()->id() !== $managedUser->id)
                                        <form
                                            method="POST"
                                            action="{{ route('admin.users.toggle-status', $managedUser) }}"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button
                                                type="submit"
                                                class="rounded-lg px-3 py-2 text-xs font-bold
                                                    {{ $managedUser->is_active
                                                        ? 'border border-red-200 bg-red-50 text-red-700 hover:bg-red-100'
                                                        : 'border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}"
                                            >
                                                {{ $managedUser->is_active
                                                    ? 'Deactivate'
                                                    : 'Activate' }}
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-6 py-12 text-center text-sm text-slate-500"
                            >
                                No user accounts matched your filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="border-t border-slate-200 px-6 py-5">
                {{ $users->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>