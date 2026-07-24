<x-dashboard-shell
    title="Child Laborer Profiles"
    subtitle="Search, review, and manage registered child laborer records according to your role."
    badge="Case Registry"
>
    @if (session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-800">
            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">✓</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-soft">
        <div class="border-b border-slate-200 bg-gradient-to-r from-white to-sky-50/60 p-5 sm:p-6">
            <div class="flex flex-col gap-5 xl:flex-row xl:items-end xl:justify-between">
                <form method="GET" action="{{ route('child-laborers.index') }}" class="grid flex-1 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="md:col-span-2">
                        <label for="search" class="block text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Search registry</label>
                        <div class="relative mt-2">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="pointer-events-none absolute left-3.5 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m21 21-4.3-4.3m2.3-5.2a7.5 7.5 0 1 1-15 0 7.5 7.5 0 0 1 15 0Z" /></svg>
                            <input id="search" name="search" value="{{ $search }}" placeholder="Profile number or complete name" class="block w-full pl-10" />
                        </div>
                    </div>

                    <div>
                        <label for="status" class="block text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Workflow status</label>
                        <select id="status" name="status" class="mt-2 block w-full">
                            <option value="">All statuses</option>
                            @foreach ($statuses as $status)
                                <option value="{{ $status }}" @selected($selectedStatus === $status)>{{ $status }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="sex" class="block text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Sex</label>
                        <select id="sex" name="sex" class="mt-2 block w-full">
                            <option value="">All sexes</option>
                            <option value="Male" @selected($selectedSex === 'Male')>Male</option>
                            <option value="Female" @selected($selectedSex === 'Female')>Female</option>
                        </select>
                    </div>

                    <div class="flex flex-wrap gap-2 md:col-span-2 xl:col-span-4">
                        <button class="inline-flex items-center gap-2 rounded-xl bg-sky-700 px-5 py-2.5 text-sm font-extrabold text-white shadow-soft hover:bg-sky-800">
                            Apply filters
                        </button>
                        <a href="{{ route('child-laborers.index') }}" class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 text-sm font-extrabold text-slate-600 hover:border-sky-300 hover:bg-sky-50 hover:text-sky-800">Reset</a>
                    </div>
                </form>

                @can('create', App\Models\ChildLaborer::class)
                    <a href="{{ route('child-laborers.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-slate-950 px-5 py-3 text-sm font-extrabold text-white shadow-panel hover:bg-sky-900">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14M5 12h14" /></svg>
                        New profile
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[980px] w-full">
                <thead>
                    <tr>
                        <th class="px-6 py-4 text-left">Profile reference</th>
                        <th class="px-6 py-4 text-left">Child information</th>
                        <th class="px-6 py-4 text-left">Assigned officer</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-right">Action</th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($childLaborers as $childLaborer)
                        @php
                            $statusClasses = match ($childLaborer->status) {
                                'Approved' => 'bg-emerald-100 text-emerald-700 ring-emerald-200',
                                'Submitted' => 'bg-sky-100 text-sky-800 ring-sky-200',
                                'Returned' => 'bg-amber-100 text-amber-800 ring-amber-200',
                                'Archived' => 'bg-red-100 text-red-700 ring-red-200',
                                default => 'bg-slate-100 text-slate-700 ring-slate-200',
                            };
                        @endphp

                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-mono text-sm font-extrabold text-slate-900">{{ $childLaborer->profile_number }}</p>
                                <p class="mt-1 text-xs text-slate-500">Created {{ $childLaborer->created_at->format('M d, Y') }}</p>
                            </td>

                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-xs font-extrabold text-sky-800">
                                        {{ str($childLaborer->first_name)->substr(0, 1)->upper() }}{{ str($childLaborer->last_name)->substr(0, 1)->upper() }}
                                    </span>
                                    <span class="min-w-0">
                                        <span class="block truncate text-sm font-extrabold text-slate-900">{{ $childLaborer->full_name }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $childLaborer->sex }} · {{ $childLaborer->age }} years old</span>
                                    </span>
                                </div>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-700">{{ $childLaborer->assignedOfficer?->name ?? 'Not assigned' }}</p>
                                <p class="mt-1 text-xs text-slate-400">Profiling responsibility</p>
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full px-3 py-1 text-[10px] font-extrabold uppercase tracking-wide ring-1 {{ $statusClasses }}">{{ $childLaborer->status }}</span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('child-laborers.show', $childLaborer) }}" class="inline-flex items-center gap-2 rounded-xl bg-sky-50 px-3.5 py-2 text-xs font-extrabold text-sky-800 ring-1 ring-sky-200 hover:bg-sky-100">
                                    Open profile
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-3.5 w-3.5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" /></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <span class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-100 text-slate-400">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-6 w-6"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5h16v14H4zM8 9h8M8 13h5" /></svg>
                                </span>
                                <p class="mt-4 text-sm font-extrabold text-slate-700">No matching profiles</p>
                                <p class="mt-1 text-xs text-slate-500">Adjust the filters or create a new profile when authorized.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($childLaborers->hasPages())
            <div class="border-t border-slate-200 bg-slate-50/60 p-5">{{ $childLaborers->links() }}</div>
        @endif
    </section>
</x-dashboard-shell>
