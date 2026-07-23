<x-dashboard-shell
    title="Audit Schedules"
    subtitle="Manage child laborer audit schedules and evaluations."
    badge="Admin Access"
>
    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4 md:grid-cols-2
                   xl:grid-cols-6"
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
                    value="{{ $search }}"
                    placeholder="Profile, child name, location, remarks..."
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="status"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Status
                </label>

                <select
                    id="status"
                    name="status"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All statuses
                    </option>

                    @foreach (
                        \App\Models\AuditSchedule::statuses()
                        as $scheduleStatus
                    )
                        <option
                            value="{{ $scheduleStatus }}"
                            @selected(
                                $status
                                    === $scheduleStatus
                            )
                        >
                            {{ $scheduleStatus }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="assigned_to"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Assigned Admin
                </label>

                <select
                    id="assigned_to"
                    name="assigned_to"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All administrators
                    </option>

                    @foreach (
                        $eligibleAdministrators as $administrator
                    )
                        <option
                            value="{{ $administrator->id }}"
                            @selected(
                                $assignedTo
                                    === $administrator->id
                            )
                        >
                            {{ $administrator->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="from"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    From
                </label>

                <input
                    id="from"
                    name="from"
                    type="date"
                    value="{{ $from }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="to"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    To
                </label>

                <input
                    id="to"
                    name="to"
                    type="date"
                    value="{{ $to }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div
                class="flex items-end gap-2
                       md:col-span-2 xl:col-span-6"
            >
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600
                           px-5 py-3 text-sm font-bold
                           text-white"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route(
                        'audit-schedules.index'
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Reset
                </a>
            </div>
        </form>
    </section>

    <section
        class="overflow-hidden rounded-3xl border
               border-slate-200 bg-white shadow-sm"
    >
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-xl font-bold text-slate-800">
                Scheduled Audits
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $auditSchedules->total() }}
                audit schedule(s)
            </p>
        </div>

        <div class="overflow-x-auto">
            <table
                class="min-w-[1050px] w-full
                       divide-y divide-slate-200"
            >
                <thead class="bg-sky-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Child Profile
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Schedule
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Assigned Admin
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Status
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-bold uppercase text-sky-800">
                            Evaluations
                        </th>

                        <th class="px-6 py-4 text-right text-xs font-bold uppercase text-sky-800">
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse (
                        $auditSchedules as $auditSchedule
                    )
                        @php
                            $statusClasses = match (
                                $auditSchedule->status
                            ) {
                                'Scheduled' =>
                                    'bg-sky-100 text-sky-700',

                                'In Progress' =>
                                    'bg-amber-100 text-amber-700',

                                'Completed' =>
                                    'bg-emerald-100 text-emerald-700',

                                'Cancelled' =>
                                    'bg-red-100 text-red-700',

                                default =>
                                    'bg-slate-100 text-slate-700',
                            };
                        @endphp

                        <tr>
                            <td class="px-6 py-4">
                                <a
                                    href="{{ route(
                                        'child-laborers.show',
                                        $auditSchedule
                                            ->childLaborer
                                    ) }}"
                                    class="font-bold text-sky-700"
                                >
                                    {{ $auditSchedule
                                        ->childLaborer
                                        ->profile_number }}
                                </a>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $auditSchedule
                                        ->childLaborer
                                        ->full_name }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $auditSchedule
                                        ->scheduled_at
                                        ->format(
                                            'F d, Y'
                                        ) }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $auditSchedule
                                        ->scheduled_at
                                        ->format(
                                            'h:i A'
                                        ) }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $auditSchedule->location
                                        ?: 'No location provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700">
                                    {{ $auditSchedule
                                        ->assignedAdministrator
                                        ->name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $auditSchedule
                                        ->assignedAdministrator
                                        ->email }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <span
                                    class="rounded-full px-3 py-1
                                           text-xs font-bold
                                           {{ $statusClasses }}"
                                >
                                    {{ $auditSchedule->status }}
                                </span>
                            </td>

                            <td
                                class="px-6 py-4 text-center
                                       font-bold text-slate-700"
                            >
                                {{ $auditSchedule
                                    ->evaluations_count }}
                            </td>

                            <td class="px-6 py-4 text-right">
                                <a
                                    href="{{ route(
                                        'audit-schedules.show',
                                        $auditSchedule
                                    ) }}"
                                    class="rounded-lg bg-sky-50
                                           px-4 py-2 text-xs
                                           font-bold text-sky-700"
                                >
                                    Open
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="6"
                                class="px-6 py-14 text-center
                                       text-sm text-slate-500"
                            >
                                No audit schedule matches the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($auditSchedules->hasPages())
            <div class="border-t border-slate-200 p-5">
                {{ $auditSchedules->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>