<x-dashboard-shell
    title="Child Laborer Reports"
    subtitle="Filtered master list, data export, printing, and comprehensive profile reports."
    badge="Reports"
>
    <section
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-5"
    >
        @foreach ([
            [
                'label' => 'Filtered Records',
                'value' => $summary['total'],
                'classes' => 'border-sky-200 bg-sky-50 text-sky-700',
            ],
            [
                'label' => 'Male',
                'value' => $summary['male'],
                'classes' => 'border-blue-200 bg-blue-50 text-blue-700',
            ],
            [
                'label' => 'Female',
                'value' => $summary['female'],
                'classes' => 'border-violet-200 bg-violet-50 text-violet-700',
            ],
            [
                'label' => 'Currently Working',
                'value' => $summary['currently_working'],
                'classes' => 'border-amber-200 bg-amber-50 text-amber-700',
            ],
            [
                'label' => 'With Interventions',
                'value' => $summary['with_interventions'],
                'classes' => 'border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
        ] as $card)
            <article
                class="rounded-2xl border p-5
                       {{ $card['classes'] }}"
            >
                <p
                    class="text-xs font-bold uppercase
                           tracking-wide opacity-75"
                >
                    {{ $card['label'] }}
                </p>

                <p class="mt-2 text-3xl font-black">
                    {{ number_format(
                        $card['value']
                    ) }}
                </p>
            </article>
        @endforeach
    </section>

    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4 md:grid-cols-2
                   xl:grid-cols-4"
        >
            <div class="md:col-span-2">
                <label
                    for="search"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Search
                </label>

                <input
                    id="search"
                    name="search"
                    value="{{ $filters['search'] }}"
                    placeholder="Profile number, child name, guardian, address..."
                    class="mt-2 block w-full rounded-xl
                           border-slate-300
                           focus:border-sky-500
                           focus:ring-sky-500"
                >
            </div>

            <div>
                <label
                    for="status"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Profile Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All permitted statuses
                    </option>

                    @foreach (
                        $statusOptions
                        as $status
                    )
                        <option
                            value="{{ $status }}"
                            @selected(
                                $filters['status']
                                    === $status
                            )
                        >
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="sex"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Sex
                </label>

                <select
                    id="sex"
                    name="sex"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All
                    </option>

                    @foreach (
                        ['Male', 'Female']
                        as $sex
                    )
                        <option
                            value="{{ $sex }}"
                            @selected(
                                $filters['sex']
                                    === $sex
                            )
                        >
                            {{ $sex }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="age_min"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Minimum Age
                </label>

                <input
                    id="age_min"
                    name="age_min"
                    type="number"
                    min="0"
                    max="99"
                    value="{{ $filters['age_min'] }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="age_max"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Maximum Age
                </label>

                <input
                    id="age_max"
                    name="age_max"
                    type="number"
                    min="0"
                    max="99"
                    value="{{ $filters['age_max'] }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="region_id"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Region
                </label>

                <select
                    id="region_id"
                    name="region_id"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All regions
                    </option>

                    @foreach ($regions as $region)
                        <option
                            value="{{ $region->id }}"
                            @selected(
                                (int) $filters[
                                    'region_id'
                                ] === $region->id
                            )
                        >
                            {{ $region->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="province_id"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Province
                </label>

                <select
                    id="province_id"
                    name="province_id"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All provinces
                    </option>

                    @foreach (
                        $provinces as $province
                    )
                        <option
                            value="{{ $province->id }}"
                            @selected(
                                (int) $filters[
                                    'province_id'
                                ] === $province->id
                            )
                        >
                            {{ $province->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="employment"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Employment
                </label>

                <select
                    id="employment"
                    name="employment"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All
                    </option>

                    <option
                        value="current"
                        @selected(
                            $filters['employment']
                                === 'current'
                        )
                    >
                        Currently working
                    </option>

                    <option
                        value="none"
                        @selected(
                            $filters['employment']
                                === 'none'
                        )
                    >
                        No current employment
                    </option>
                </select>
            </div>

            <div>
                <label
                    for="education"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Education Record
                </label>

                <select
                    id="education"
                    name="education"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All
                    </option>

                    <option
                        value="current"
                        @selected(
                            $filters['education']
                                === 'current'
                        )
                    >
                        Has current education record
                    </option>

                    <option
                        value="none"
                        @selected(
                            $filters['education']
                                === 'none'
                        )
                    >
                        No current education record
                    </option>
                </select>
            </div>

            <div>
                <label
                    for="intervention"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Interventions
                </label>

                <select
                    id="intervention"
                    name="intervention"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All
                    </option>

                    <option
                        value="with"
                        @selected(
                            $filters[
                                'intervention'
                            ] === 'with'
                        )
                    >
                        With intervention
                    </option>

                    <option
                        value="without"
                        @selected(
                            $filters[
                                'intervention'
                            ] === 'without'
                        )
                    >
                        Without intervention
                    </option>
                </select>
            </div>

            <div>
                <label
                    for="created_from"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Created From
                </label>

                <input
                    id="created_from"
                    name="created_from"
                    type="date"
                    value="{{ $filters[
                        'created_from'
                    ] }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="created_to"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Created To
                </label>

                <input
                    id="created_to"
                    name="created_to"
                    type="date"
                    value="{{ $filters[
                        'created_to'
                    ] }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="sort"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Sort By
                </label>

                <select
                    id="sort"
                    name="sort"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    @foreach ([
                        'created_at' => 'Profile Created',
                        'profile_number' => 'Profile Number',
                        'name' => 'Child Name',
                        'birth_date' => 'Birth Date',
                        'status' => 'Status',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(
                                $filters['sort']
                                    === $value
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="direction"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Direction
                </label>

                <select
                    id="direction"
                    name="direction"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option
                        value="asc"
                        @selected(
                            $filters[
                                'direction'
                            ] === 'asc'
                        )
                    >
                        Ascending
                    </option>

                    <option
                        value="desc"
                        @selected(
                            $filters[
                                'direction'
                            ] === 'desc'
                        )
                    >
                        Descending
                    </option>
                </select>
            </div>

            <div
                class="flex flex-wrap items-end gap-2
                       md:col-span-2 xl:col-span-4"
            >
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600
                           px-5 py-3 text-sm font-bold
                           text-white hover:bg-sky-700"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route(
                        'reports.child-laborers.index'
                    ) }}"
                    class="rounded-xl border
                           border-slate-300 px-5 py-3
                           text-sm font-bold
                           text-slate-600"
                >
                    Reset
                </a>

                @can('export-reports')
                    <a
                        href="{{ route(
                            'reports.child-laborers.export.csv',
                            request()->query()
                        ) }}"
                        class="rounded-xl bg-emerald-600
                               px-5 py-3 text-sm
                               font-bold text-white"
                    >
                        Export CSV
                    </a>
                @endcan

                @can('print-reports')
                    <a
                        href="{{ route(
                            'reports.child-laborers.print',
                            request()->query()
                        ) }}"
                        target="_blank"
                        class="rounded-xl bg-slate-800
                               px-5 py-3 text-sm
                               font-bold text-white"
                    >
                        Print Master List
                    </a>
                @endcan
            </div>
        </form>
    </section>

    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200 bg-white
               shadow-sm"
    >
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-xl font-bold text-slate-800">
                Child Laborer Master List
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ number_format(
                    $childLaborers->total()
                ) }}
                matching profile(s)
            </p>
        </div>

        <div class="overflow-x-auto">
            <table
                class="min-w-[1500px] w-full
                       divide-y divide-slate-200"
            >
                <thead class="bg-sky-50">
                    <tr>
                        @foreach ([
                            'Profile',
                            'Child Laborer',
                            'Age / Sex',
                            'Address',
                            'Guardian',
                            'Education',
                            'Employment',
                            'Interventions',
                            'Status',
                            'Action',
                        ] as $heading)
                            <th
                                class="px-5 py-4 text-left
                                       text-xs font-bold
                                       uppercase tracking-wide
                                       text-sky-800"
                            >
                                {{ $heading }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse (
                        $childLaborers as $row
                    )
                        @php
                            $statusClasses = match (
                                $row['status']
                            ) {
                                'Draft' =>
                                    'bg-slate-100 text-slate-700',

                                'Submitted' =>
                                    'bg-sky-100 text-sky-700',

                                'Returned' =>
                                    'bg-amber-100 text-amber-700',

                                'Approved' =>
                                    'bg-emerald-100 text-emerald-700',

                                'Archived' =>
                                    'bg-red-100 text-red-700',

                                default =>
                                    'bg-slate-100 text-slate-700',
                            };
                        @endphp

                        <tr class="hover:bg-slate-50">
                            <td class="px-5 py-4 align-top">
                                <p class="font-bold text-sky-700">
                                    {{ $row[
                                        'profile_number'
                                    ] }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $row['created_at'] }}
                                </p>
                            </td>

                            <td class="px-5 py-4 align-top">
                                <p class="font-bold text-slate-800">
                                    {{ $row['full_name'] }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $row['birth_date']
                                        ?: 'No birth date' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-slate-700">
                                    {{ $row['age'] !== null
                                        ? $row['age'].' years'
                                        : '—' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $row['sex'] }}
                                </p>
                            </td>

                            <td
                                class="max-w-xs px-5 py-4
                                       align-top text-sm
                                       leading-6 text-slate-600"
                            >
                                {{ $row['address'] }}
                            </td>

                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-slate-700">
                                    {{ $row[
                                        'guardian_name'
                                    ] ?: 'Not recorded' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $row[
                                        'guardian_contact'
                                    ] ?: 'No contact' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-slate-700">
                                    {{ $row[
                                        'education_status'
                                    ] ?: 'No current record' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $row[
                                        'grade_year_level'
                                    ] ?: '—' }}
                                </p>
                            </td>

                            <td class="px-5 py-4 align-top">
                                <p class="font-semibold text-slate-700">
                                    {{ $row[
                                        'currently_working'
                                    ] }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $row['occupation']
                                        ?: 'No current occupation' }}
                                </p>
                            </td>

                            <td
                                class="px-5 py-4 align-top
                                       text-center font-bold
                                       text-slate-700"
                            >
                                {{ number_format(
                                    $row[
                                        'interventions_count'
                                    ]
                                ) }}
                            </td>

                            <td class="px-5 py-4 align-top">
                                <span
                                    class="inline-flex
                                           rounded-full px-3
                                           py-1 text-xs font-bold
                                           {{ $statusClasses }}"
                                >
                                    {{ $row['status'] }}
                                </span>
                            </td>

                            <td class="px-5 py-4 align-top">
                                <div class="flex flex-col gap-2">
                                    <a
                                        href="{{ route(
                                            'reports.child-laborers.profile',
                                            $row['id']
                                        ) }}"
                                        class="rounded-lg
                                               bg-sky-50 px-3
                                               py-2 text-center
                                               text-xs font-bold
                                               text-sky-700"
                                    >
                                        View Report
                                    </a>

                                    @can('print-reports')
                                        <a
                                            href="{{ route(
                                                'reports.child-laborers.profile.print',
                                                $row['id']
                                            ) }}"
                                            target="_blank"
                                            class="rounded-lg
                                                   bg-slate-100
                                                   px-3 py-2
                                                   text-center
                                                   text-xs font-bold
                                                   text-slate-700"
                                        >
                                            Print
                                        </a>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="10"
                                class="px-6 py-14
                                       text-center text-sm
                                       text-slate-500"
                            >
                                No child laborer matches the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($childLaborers->hasPages())
            <div class="border-t border-slate-200 p-5">
                {{ $childLaborers->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>