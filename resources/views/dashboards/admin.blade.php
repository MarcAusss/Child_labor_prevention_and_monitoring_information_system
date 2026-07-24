<x-dashboard-shell
    title="Administrator Dashboard"
    subtitle="Review profiles, coordinate interventions, manage audits, and oversee authorized users."
    badge="Admin"
>
    <section class="clpmis-hero">
        <div class="relative z-10 flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-sky-200">Administrative coordination</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">Good day, {{ str(auth()->user()->name)->before(' ') }}.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200">
                    Prioritize submitted records, coordinate profile assignments, review scheduled audits, and keep interventions moving through an accountable workflow.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('child-laborers.index', ['status' => 'Submitted']) }}" class="rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-slate-950">Review submissions</a>
                <a href="{{ route('audit-schedules.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-extrabold text-white">Open audits</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach ([
            ['All profiles', $totalProfiles, 'Current registry'],
            ['Submitted', $submittedProfiles, 'Awaiting review'],
            ['Approved', $approvedProfiles, 'Validated profiles'],
            ['Upcoming audits', $upcomingAudits, 'Scheduled actions'],
        ] as [$label, $value, $note])
            <article class="clpmis-metric-card">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ $label }}</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($value) }}</p>
                <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-2/3 rounded-full bg-sky-600"></div></div>
                <p class="mt-3 text-xs text-slate-500">{{ $note }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.1fr_.9fr]">
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="clpmis-eyebrow">Case registry</p>
                    <h2 class="mt-1 text-lg font-extrabold text-slate-950">Recently updated profiles</h2>
                </div>
                <a href="{{ route('child-laborers.index') }}" class="text-xs font-extrabold text-sky-700">View all</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentProfiles as $profile)
                    <a href="{{ route('child-laborers.show', $profile) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-sky-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-xs font-extrabold text-sky-800">{{ str($profile->first_name)->substr(0, 1)->upper() }}{{ str($profile->last_name)->substr(0, 1)->upper() }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-extrabold text-slate-900">{{ $profile->full_name }}</span>
                            <span class="mt-1 block truncate text-xs text-slate-500">{{ $profile->profile_number }} · {{ $profile->assignedOfficer?->name ?? 'Not assigned' }}</span>
                        </span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-extrabold {{ match($profile->status) { 'Approved' => 'bg-emerald-100 text-emerald-700', 'Submitted' => 'bg-sky-100 text-sky-800', 'Returned' => 'bg-amber-100 text-amber-800', default => 'bg-slate-100 text-slate-600' } }}">{{ $profile->status }}</span>
                    </a>
                @empty
                    <p class="px-6 py-14 text-center text-sm text-slate-500">No profile activity yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-soft">
            <p class="clpmis-eyebrow">Team availability</p>
            <h2 class="mt-1 text-lg font-extrabold text-slate-950">Authorized users</h2>
            <div class="mt-6 space-y-4">
                @foreach ([
                    ['Managed users', $totalUsers, 'bg-sky-600'],
                    ['Active users', $activeUsers, 'bg-emerald-600'],
                    ['Profiling officers', $profilingOfficers, 'bg-amber-600'],
                    ['Viewers', $viewers, 'bg-indigo-600'],
                ] as [$label, $value, $bar])
                    <div>
                        <div class="flex items-center justify-between text-sm"><span class="font-semibold text-slate-600">{{ $label }}</span><strong class="text-slate-950">{{ number_format($value) }}</strong></div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full {{ $bar }}" style="width: {{ $totalUsers > 0 ? max(8, min(100, ($value / $totalUsers) * 100)) : 0 }}%"></div></div>
                    </div>
                @endforeach
            </div>
            <a href="{{ route('admin.users.index') }}" class="mt-7 inline-flex rounded-xl bg-slate-950 px-4 py-3 text-xs font-extrabold uppercase tracking-wide text-white">Manage accounts</a>
        </article>
    </section>
</x-dashboard-shell>
