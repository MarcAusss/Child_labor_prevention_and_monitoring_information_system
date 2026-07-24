<x-dashboard-shell
    title="Profiling Officer Dashboard"
    subtitle="Create, complete, submit, and maintain the profiles assigned to your fieldwork."
    badge="Profiling Officer"
>
    <section class="clpmis-hero">
        <div class="relative z-10 flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-sky-200">Field profiling workspace</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">Welcome, {{ str($user->name)->before(' ') }}.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200">
                    Continue unfinished profiles, resolve returned records, document household and work conditions, and submit complete information for administrative review.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                @can('create', App\Models\ChildLaborer::class)
                    <a href="{{ route('child-laborers.create') }}" class="rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-slate-950">Create profile</a>
                @endcan
                <a href="{{ route('child-laborers.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-extrabold text-white">My profiles</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach ([
            ['Assigned profiles', $totalProfiles, 'text-slate-950', 'bg-slate-100'],
            ['Draft', $draftProfiles, 'text-amber-800', 'bg-amber-100'],
            ['Submitted', $submittedProfiles, 'text-sky-800', 'bg-sky-100'],
            ['Returned', $returnedProfiles, 'text-red-700', 'bg-red-100'],
            ['Approved', $approvedProfiles, 'text-emerald-700', 'bg-emerald-100'],
        ] as [$label, $value, $text, $bg])
            <article class="clpmis-metric-card">
                <span class="inline-flex rounded-full {{ $bg }} px-3 py-1 text-[10px] font-extrabold uppercase {{ $text }}">{{ $label }}</span>
                <p class="mt-4 text-4xl font-extrabold tracking-tight {{ $text }}">{{ number_format($value) }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.2fr_.8fr]">
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="clpmis-eyebrow">Current workload</p>
                    <h2 class="mt-1 text-lg font-extrabold text-slate-950">Recently updated profiles</h2>
                </div>
                <a href="{{ route('child-laborers.index') }}" class="text-xs font-extrabold text-sky-700">Open registry</a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse ($recentProfiles as $profile)
                    <a href="{{ route('child-laborers.show', $profile) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-sky-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-xs font-extrabold text-sky-800">{{ str($profile->first_name)->substr(0, 1)->upper() }}{{ str($profile->last_name)->substr(0, 1)->upper() }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-extrabold text-slate-900">{{ $profile->full_name }}</span>
                            <span class="mt-1 block truncate text-xs text-slate-500">{{ $profile->profile_number }} · Updated {{ $profile->updated_at->diffForHumans() }}</span>
                        </span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-extrabold {{ match($profile->status) { 'Approved' => 'bg-emerald-100 text-emerald-700', 'Submitted' => 'bg-sky-100 text-sky-800', 'Returned' => 'bg-red-100 text-red-700', default => 'bg-amber-100 text-amber-800' } }}">{{ $profile->status }}</span>
                    </a>
                @empty
                    <p class="px-6 py-14 text-center text-sm text-slate-500">No assigned profile yet.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-soft">
            <p class="clpmis-eyebrow">Recommended sequence</p>
            <h2 class="mt-1 text-lg font-extrabold text-slate-950">Complete a profile confidently</h2>
            <ol class="mt-6 space-y-4">
                @foreach ([
                    ['01', 'Identity and location', 'Confirm personal, birth, and residential information.'],
                    ['02', 'Household and education', 'Record guardians, household members, and schooling.'],
                    ['03', 'Work and protection needs', 'Document employment, hazards, health, and intervention needs.'],
                    ['04', 'Review and submit', 'Check completeness before administrative review.'],
                ] as [$number, $label, $description])
                    <li class="flex gap-3">
                        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-slate-950 text-[10px] font-extrabold text-sky-200">{{ $number }}</span>
                        <span><span class="block text-sm font-extrabold text-slate-900">{{ $label }}</span><span class="mt-1 block text-xs leading-5 text-slate-500">{{ $description }}</span></span>
                    </li>
                @endforeach
            </ol>
        </article>
    </section>
</x-dashboard-shell>
