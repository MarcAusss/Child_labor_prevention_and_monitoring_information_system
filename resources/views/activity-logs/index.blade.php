<x-dashboard-shell
    title="Activity Logs"
    subtitle="System-wide, read-only audit trail of user and record activity."
    badge="Restricted"
>
    <section
        class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"
    >
        <article
            class="rounded-2xl border border-sky-200
                   bg-sky-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-sky-600"
            >
                Displayed Results
            </p>

            <p class="mt-2 text-3xl font-bold text-sky-700">
                {{ number_format($logs->total()) }}
            </p>
        </article>

        <article
            class="rounded-2xl border border-emerald-200
                   bg-emerald-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-emerald-600"
            >
                Activity Today
            </p>

            <p
                class="mt-2 text-3xl font-bold
                       text-emerald-700"
            >
                {{ number_format($todayCount) }}
            </p>
        </article>

        <article
            class="rounded-2xl border border-violet-200
                   bg-violet-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-violet-600"
            >
                Last Seven Days
            </p>

            <p
                class="mt-2 text-3xl font-bold
                       text-violet-700"
            >
                {{ number_format($lastSevenDaysCount) }}
            </p>
        </article>

        <article
            class="rounded-2xl border border-red-200
                   bg-red-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-red-600"
            >
                Failed Logins
            </p>

            <p class="mt-2 text-3xl font-bold text-red-700">
                {{ number_format($failedLoginCount) }}
            </p>
        </article>
    </section>

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
                    value="{{ $filters['search'] }}"
                    placeholder="Description, actor, profile, role..."
                    class="mt-2 block w-full rounded-xl
                           border-slate-300
                           focus:border-sky-500
                           focus:ring-sky-500"
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
                    for="entity_type"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Entity
                </label>

                <select
                    id="entity_type"
                    name="entity_type"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All entities
                    </option>

                    @foreach ($entityTypes as $entityType)
                        <option
                            value="{{ $entityType }}"
                            @selected(
                                $filters['entity_type']
                                    === $entityType
                            )
                        >
                            {{ str(
                                class_basename($entityType)
                            )->headline() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label
                    for="user_id"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    User
                </label>

                <select
                    id="user_id"
                    name="user_id"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All users
                    </option>

                    @foreach ($users as $user)
                        <option
                            value="{{ $user->id }}"
                            @selected(
                                (int) $filters['user_id']
                                    === $user->id
                            )
                        >
                            {{ $user->name }}
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
                       md:col-span-2 xl:col-span-4"
            >
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600 px-5 py-3
                           text-sm font-bold text-white"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route('activity-logs.index') }}"
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
        <div
            class="border-b border-slate-200 px-6 py-5"
        >
            <h2 class="text-xl font-bold text-slate-800">
                Recorded Activity
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Activity logs cannot be edited or deleted.
            </p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase
                                   text-sky-800"
                        >
                            Date and User
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase
                                   text-sky-800"
                        >
                            Activity
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase
                                   text-sky-800"
                        >
                            Profile / Entity
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase
                                   text-sky-800"
                        >
                            Request
                        </th>

                        <th
                            class="px-6 py-4 text-right text-xs
                                   font-bold uppercase
                                   text-sky-800"
                        >
                            Action
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $log)
                        @php
                            $actionClasses = match ($log->action) {
                                'created',
                                'approved',
                                'restored',
                                'login' =>
                                    'bg-emerald-100 text-emerald-700',

                                'updated',
                                'submitted',
                                'downloaded' =>
                                    'bg-sky-100 text-sky-700',

                                'returned',
                                'archived' =>
                                    'bg-amber-100 text-amber-700',

                                'removed',
                                'login_failed' =>
                                    'bg-red-100 text-red-700',

                                'logout' =>
                                    'bg-slate-100 text-slate-700',

                                default =>
                                    'bg-violet-100 text-violet-700',
                            };
                        @endphp

                        <tr>
                            <td class="px-6 py-4 align-top">
                                <p class="font-bold text-slate-800">
                                    {{ $log->actor_display }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $log->role_name
                                        ?: 'No role recorded' }}
                                </p>

                                <p class="mt-2 text-xs text-slate-400">
                                    {{ $log->created_at
                                        ->format('M d, Y h:i:s A') }}
                                </p>
                            </td>

                            <td class="px-6 py-4 align-top">
                                <span
                                    class="inline-flex rounded-full
                                           px-3 py-1 text-xs
                                           font-bold
                                           {{ $actionClasses }}"
                                >
                                    {{ $log->action_label }}
                                </span>

                                <p
                                    class="mt-3 max-w-md text-sm
                                           leading-6 text-slate-700"
                                >
                                    {{ $log->description }}
                                </p>
                            </td>

                            <td class="px-6 py-4 align-top">
                                @if ($log->childLaborer)
                                    <a
                                        href="{{ route(
                                            'child-laborers.show',
                                            $log->childLaborer
                                        ) }}"
                                        class="font-bold text-sky-700"
                                    >
                                        {{ $log->childLaborer
                                            ->profile_number }}
                                    </a>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $log->childLaborer
                                            ->full_name }}
                                    </p>
                                @else
                                    <p class="font-semibold text-slate-700">
                                        {{ $log->entity_label }}
                                    </p>
                                @endif

                                @if ($log->entity_id)
                                    <p class="mt-2 text-xs text-slate-400">
                                        Entity ID:
                                        {{ $log->entity_id }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-6 py-4 align-top">
                                <p class="text-xs font-bold text-slate-600">
                                    {{ $log->request_method
                                        ?: 'SYSTEM' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $log->ip_address
                                        ?: 'No IP recorded' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $log->route_name
                                        ?: 'No route recorded' }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-right align-top">
                                <a
                                    href="{{ route(
                                        'activity-logs.show',
                                        $log
                                    ) }}"
                                    class="rounded-lg bg-sky-50
                                           px-3 py-2 text-xs
                                           font-bold text-sky-700"
                                >
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-6 py-14 text-center
                                       text-sm text-slate-500"
                            >
                                No activity matches the selected filters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="border-t border-slate-200 p-5">
                {{ $logs->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>