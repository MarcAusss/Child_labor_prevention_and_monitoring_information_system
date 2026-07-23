<x-dashboard-shell
    title="Child Laborer Profiles"
    subtitle="Manage and monitor registered child laborer records."
    badge="Profiles"
>
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    <section class="rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
                <form
                    method="GET"
                    action="{{ route('child-laborers.index') }}"
                    class="grid flex-1 gap-3 md:grid-cols-4"
                >
                    <input
                        name="search"
                        value="{{ $search }}"
                        placeholder="Profile number or name"
                        class="rounded-xl border-slate-300 md:col-span-2"
                    >

                    <select name="status" class="rounded-xl border-slate-300">
                        <option value="">All statuses</option>

                        @foreach ($statuses as $status)
                            <option value="{{ $status }}" @selected($selectedStatus === $status)>
                                {{ $status }}
                            </option>
                        @endforeach
                    </select>

                    <select name="sex" class="rounded-xl border-slate-300">
                        <option value="">All sexes</option>
                        <option value="Male" @selected($selectedSex === 'Male')>Male</option>
                        <option value="Female" @selected($selectedSex === 'Female')>Female</option>
                    </select>

                    <div class="flex gap-2 md:col-span-4">
                        <button class="rounded-xl bg-sky-600 px-5 py-2.5 text-sm font-bold text-white">
                            Filter
                        </button>

                        <a
                            href="{{ route('child-laborers.index') }}"
                            class="rounded-xl border border-slate-300 px-5 py-2.5 text-sm font-bold text-slate-600"
                        >
                            Reset
                        </a>
                    </div>
                </form>

                @can('create', App\Models\ChildLaborer::class)
                    <a
                        href="{{ route('child-laborers.create') }}"
                        class="rounded-xl bg-sky-600 px-5 py-3 text-center text-sm font-bold text-white"
                    >
                        Create Profile
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Profile
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Personal Information
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Assigned Officer
                        </th>
                        <th class="px-6 py-4 text-center text-xs font-bold uppercase text-sky-800">
                            Status
                        </th>
                        <th class="px-6 py-4 text-right text-xs font-bold uppercase text-sky-800">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($childLaborers as $childLaborer)
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $childLaborer->profile_number }}
                                </p>
                                <p class="mt-1 text-xs text-slate-500">
                                    Created {{ $childLaborer->created_at->format('M d, Y') }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $childLaborer->full_name }}
                                </p>
                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $childLaborer->sex }},
                                    {{ $childLaborer->age }} years old
                                </p>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $childLaborer->assignedOfficer?->name ?? 'Not assigned' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-bold text-slate-700">
                                    {{ $childLaborer->status }}
                                </span>
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route('child-laborers.show', $childLaborer) }}"
                                    class="rounded-lg bg-sky-50 px-3 py-2 text-xs font-bold text-sky-700"
                                >
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-sm text-slate-500">
                                No profiles found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($childLaborers->hasPages())
            <div class="border-t border-slate-200 p-6">
                {{ $childLaborers->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>