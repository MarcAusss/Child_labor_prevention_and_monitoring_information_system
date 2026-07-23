<x-dashboard-shell
    title="Profile Activity"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $childLaborer->status }}"
>
    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4 md:grid-cols-2
                   xl:grid-cols-5"
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
                    placeholder="Description, user, role..."
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div>
                <label
                    for="action"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Action
                </label>

                <select
                    id="action"
                    name="action"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All actions
                    </option>

                    @foreach ($actions as $action)
                        <option
                            value="{{ $action }}"
                            @selected(
                                $filters['action']
                                    === $action
                            )
                        >
                            {{ str($action)->headline() }}
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
                    value="{{ $filters['from'] }}"
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
                    value="{{ $filters['to'] }}"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
            </div>

            <div
                class="flex items-end gap-2
                       md:col-span-2 xl:col-span-5"
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
                        'child-laborers.activity-logs.index',
                        $childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Reset
                </a>

                <a
                    href="{{ route(
                        'child-laborers.show',
                        $childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Back to Profile
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
                Profile History
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $logs->total() }}
                activity record(s)
            </p>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse ($logs as $log)
                @php
                    $oldValues = $log->old_values ?? [];
                    $newValues = $log->new_values ?? [];

                    $fields = collect(
                        array_keys($oldValues)
                    )
                        ->merge(
                            array_keys($newValues)
                        )
                        ->unique();

                    $displayValue = function (mixed $value): string {
                        if ($value === null) {
                            return '—';
                        }

                        if (is_bool($value)) {
                            return $value ? 'Yes' : 'No';
                        }

                        if (is_array($value)) {
                            return json_encode(
                                $value,
                                JSON_UNESCAPED_UNICODE
                                | JSON_UNESCAPED_SLASHES
                            ) ?: '—';
                        }

                        return (string) $value;
                    };
                @endphp

                <article class="p-6">
                    <div
                        class="flex flex-col gap-4
                               sm:flex-row sm:items-start
                               sm:justify-between"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-full bg-sky-100
                                           px-3 py-1 text-xs
                                           font-bold text-sky-700"
                                >
                                    {{ $log->action_label }}
                                </span>

                                <span class="text-xs text-slate-400">
                                    {{ $log->entity_label }}
                                </span>
                            </div>

                            <p class="mt-3 font-semibold text-slate-800">
                                {{ $log->description }}
                            </p>

                            <p class="mt-2 text-sm text-slate-500">
                                {{ $log->actor_display }}
                                @if ($log->role_name)
                                    · {{ $log->role_name }}
                                @endif
                            </p>
                        </div>

                        <div class="shrink-0 text-right">
                            <p class="text-sm font-bold text-slate-700">
                                {{ $log->created_at
                                    ->format('M d, Y') }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $log->created_at
                                    ->format('h:i:s A') }}
                            </p>

                            <p class="mt-1 text-xs text-slate-400">
                                {{ $log->ip_address
                                    ?: 'No IP recorded' }}
                            </p>
                        </div>
                    </div>

                    @if ($fields->isNotEmpty())
                        <details
                            class="mt-5 rounded-2xl border
                                   border-slate-200"
                        >
                            <summary
                                class="cursor-pointer px-4 py-3
                                       text-sm font-bold
                                       text-sky-700"
                            >
                                View Before and After Values
                            </summary>

                            <div class="overflow-x-auto border-t border-slate-200">
                                <table class="min-w-full divide-y divide-slate-200">
                                    <thead class="bg-slate-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">
                                                Field
                                            </th>

                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">
                                                Before
                                            </th>

                                            <th class="px-4 py-3 text-left text-xs font-bold uppercase text-slate-500">
                                                After
                                            </th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-slate-100">
                                        @foreach ($fields as $field)
                                            <tr>
                                                <td class="px-4 py-3 align-top text-sm font-bold text-slate-700">
                                                    {{ str($field)->headline() }}
                                                </td>

                                                <td class="px-4 py-3 align-top text-sm text-red-700">
                                                    {{ $displayValue(
                                                        $oldValues[$field]
                                                            ?? null
                                                    ) }}
                                                </td>

                                                <td class="px-4 py-3 align-top text-sm text-emerald-700">
                                                    {{ $displayValue(
                                                        $newValues[$field]
                                                            ?? null
                                                    ) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    @endif
                </article>
            @empty
                <div
                    class="px-6 py-14 text-center
                           text-sm text-slate-500"
                >
                    No profile activity matches the selected filters.
                </div>
            @endforelse
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-200 p-5">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>