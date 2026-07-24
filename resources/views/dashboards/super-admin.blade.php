<x-dashboard-shell
    title="Super Administrator Dashboard"
    subtitle="System-wide oversight, access control, records, security, and operational readiness."
    badge="Super Admin"
>
    <section class="clpmis-hero">
        <div class="relative z-10 flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-sky-200">Executive system oversight</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">
                    One secure view of people, profiles, reviews, and system health.
                </h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200">
                    Review access, profile workload, pending administrative actions, audit schedules, and the latest system activity from a formal control workspace.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <a href="{{ route('admin.users.index') }}" class="rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-slate-950 shadow-lg hover:bg-sky-50">Manage users</a>
                <a href="{{ route('security.status') }}" class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-extrabold text-white backdrop-blur hover:bg-white/15">Security status</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['Child profiles', $totalProfiles, 'All registered records', 'text-sky-800', 'bg-sky-100'],
            ['Pending review', $submittedProfiles, 'Submitted profiles', 'text-amber-800', 'bg-amber-100'],
            ['System users', $totalUsers, $activeUsers.' active accounts', 'text-indigo-800', 'bg-indigo-100'],
            ['Upcoming audits', $upcomingAudits, 'Scheduled reviews', 'text-emerald-800', 'bg-emerald-100'],
        ] as [$label, $value, $note, $textClass, $bgClass])
            <article class="clpmis-metric-card">
                <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl {{ $bgClass }} {{ $textClass }}">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 12h14M12 5v14" /></svg>
                </span>
                <p class="mt-5 text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ $label }}</p>
                <p class="mt-2 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($value) }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $note }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="clpmis-eyebrow">Access administration</p>
                    <h2 class="mt-1 text-lg font-extrabold text-slate-950">Recently added users</h2>
                </div>
                <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-600">{{ $inactiveUsers }} inactive</span>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr>
                            <th class="px-6 py-4 text-left">User</th>
                            <th class="px-6 py-4 text-left">Role</th>
                            <th class="px-6 py-4 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($recentUsers as $recentUser)
                            <tr>
                                <td class="px-6 py-4">
                                    <p class="text-sm font-extrabold text-slate-900">{{ $recentUser->name }}</p>
                                    <p class="mt-1 text-xs text-slate-500">{{ $recentUser->email }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm font-semibold text-slate-600">{{ $recentUser->role?->name ?? 'No role' }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-extrabold uppercase {{ $recentUser->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $recentUser->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-sm text-slate-500">No users found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </article>

        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
            <div class="border-b border-slate-200 px-6 py-5">
                <p class="clpmis-eyebrow">Records activity</p>
                <h2 class="mt-1 text-lg font-extrabold text-slate-950">Recently updated profiles</h2>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($recentProfiles as $profile)
                    <a href="{{ route('child-laborers.show', $profile) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-sky-50/60">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-xs font-extrabold text-sky-800">
                            {{ str($profile->first_name)->substr(0, 1)->upper() }}{{ str($profile->last_name)->substr(0, 1)->upper() }}
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-extrabold text-slate-900">{{ $profile->full_name }}</span>
                            <span class="mt-1 block truncate text-xs text-slate-500">{{ $profile->profile_number }} · {{ $profile->assignedOfficer?->name ?? 'Unassigned' }}</span>
                        </span>
                        <span class="rounded-full bg-slate-100 px-3 py-1 text-[10px] font-extrabold text-slate-600">{{ $profile->status }}</span>
                    </a>
                @empty
                    <p class="px-6 py-12 text-center text-sm text-slate-500">No profiles found.</p>
                @endforelse
            </div>
        </article>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Active accounts</p>
            <p class="mt-2 text-2xl font-extrabold text-emerald-700">{{ number_format($activeUsers) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Inactive accounts</p>
            <p class="mt-2 text-2xl font-extrabold text-red-700">{{ number_format($inactiveUsers) }}</p>
        </article>
        <article class="rounded-2xl border border-slate-200 bg-white p-5 shadow-soft">
            <p class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Active roles</p>
            <p class="mt-2 text-2xl font-extrabold text-sky-800">{{ number_format($totalRoles) }}</p>
        </article>
    </section>
</x-dashboard-shell>
