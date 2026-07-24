<x-dashboard-shell
    title="Viewer Dashboard"
    subtitle="Read-only access to authorized records, reports, and monitoring information."
    badge="Viewer"
>
    <section class="clpmis-hero">
        <div class="relative z-10 flex flex-col gap-7 xl:flex-row xl:items-end xl:justify-between">
            <div class="max-w-3xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.24em] text-sky-200">Read-only information portal</p>
                <h2 class="mt-4 text-balance text-3xl font-extrabold tracking-tight sm:text-4xl">Welcome, {{ str($user->name)->before(' ') }}.</h2>
                <p class="mt-4 max-w-2xl text-sm leading-7 text-slate-200">
                    Review authorized submitted and approved profiles, monitor aggregate trends, and produce permitted reports without changing protected records.
                </p>
            </div>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('reports.child-laborers.index') }}" class="rounded-xl bg-white px-5 py-3 text-sm font-extrabold text-slate-950">Open reports</a>
                <a href="{{ route('reports.statistics.index') }}" class="rounded-xl border border-white/20 bg-white/10 px-5 py-3 text-sm font-extrabold text-white">View statistics</a>
            </div>
        </div>
    </section>

    <section class="grid gap-4 sm:grid-cols-3">
        @foreach ([
            ['Visible profiles', $visibleProfiles, 'Authorized records'],
            ['Approved profiles', $approvedProfiles, 'Validated information'],
            ['Submitted profiles', $submittedProfiles, 'Under review'],
        ] as [$label, $value, $note])
            <article class="clpmis-metric-card">
                <p class="text-[10px] font-extrabold uppercase tracking-[0.16em] text-slate-400">{{ $label }}</p>
                <p class="mt-3 text-4xl font-extrabold tracking-tight text-slate-950">{{ number_format($value) }}</p>
                <p class="mt-2 text-xs text-slate-500">{{ $note }}</p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.15fr_.85fr]">
        <article class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
            <div class="flex items-center justify-between border-b border-slate-200 px-6 py-5">
                <div>
                    <p class="clpmis-eyebrow">Authorized registry</p>
                    <h2 class="mt-1 text-lg font-extrabold text-slate-950">Recently updated profiles</h2>
                </div>
                <a href="{{ route('child-laborers.index') }}" class="text-xs font-extrabold text-sky-700">View profiles</a>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse ($recentProfiles as $profile)
                    <a href="{{ route('child-laborers.show', $profile) }}" class="flex items-center gap-4 px-6 py-4 hover:bg-sky-50/60">
                        <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-sky-100 text-xs font-extrabold text-sky-800">{{ str($profile->first_name)->substr(0, 1)->upper() }}{{ str($profile->last_name)->substr(0, 1)->upper() }}</span>
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-extrabold text-slate-900">{{ $profile->full_name }}</span>
                            <span class="mt-1 block truncate text-xs text-slate-500">{{ $profile->profile_number }} · {{ $profile->sex }}, {{ $profile->age }} years old</span>
                        </span>
                        <span class="rounded-full px-3 py-1 text-[10px] font-extrabold {{ $profile->status === 'Approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-800' }}">{{ $profile->status }}</span>
                    </a>
                @empty
                    <p class="px-6 py-14 text-center text-sm text-slate-500">No authorized profiles are currently available.</p>
                @endforelse
            </div>
        </article>

        <article class="rounded-3xl border border-amber-200 bg-amber-50 p-6 shadow-soft">
            <p class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-amber-700">Access guidance</p>
            <h2 class="mt-2 text-xl font-extrabold text-amber-950">Read-only by design</h2>
            <p class="mt-3 text-sm leading-7 text-amber-900/80">
                Viewer accounts cannot create, edit, approve, archive, evaluate, or administer records. Sensitive information remains subject to role and document-level restrictions.
            </p>
            <div class="mt-6 space-y-3">
                @foreach (['Review permitted profiles', 'Use approved reports and summaries', 'Export or print only when authorized', 'Report corrections to an administrator'] as $item)
                    <div class="flex items-center gap-3 rounded-xl border border-amber-200 bg-white/60 px-4 py-3 text-sm font-semibold text-amber-900">
                        <span class="h-2 w-2 rounded-full bg-amber-600"></span>{{ $item }}
                    </div>
                @endforeach
            </div>
        </article>
    </section>
</x-dashboard-shell>
