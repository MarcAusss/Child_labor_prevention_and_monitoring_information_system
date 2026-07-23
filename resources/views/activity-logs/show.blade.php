<x-dashboard-shell
    title="Activity Log Details"
    subtitle="Read-only request, actor, entity, and change information."
    badge="{{ $activityLog->action_label }}"
>
    @php
        $oldValues = $activityLog->old_values ?? [];
        $newValues = $activityLog->new_values ?? [];

        $changedFields = collect(
            array_keys($oldValues)
        )
            ->merge(
                array_keys($newValues)
            )
            ->unique()
            ->values();

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
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_UNICODE
                    | JSON_UNESCAPED_SLASHES
                ) ?: '—';
            }

            return (string) $value;
        };
    @endphp

    <section
        class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm"
    >
        <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    Actor
                </p>

                <p class="mt-2 font-bold text-slate-800">
                    {{ $activityLog->actor_display }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $activityLog->role_name
                        ?: 'No role recorded' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    Activity
                </p>

                <p class="mt-2 font-bold text-slate-800">
                    {{ $activityLog->action_label }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $activityLog->entity_label }}
                    @if ($activityLog->entity_id)
                        #{{ $activityLog->entity_id }}
                    @endif
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    Date and Time
                </p>

                <p class="mt-2 font-bold text-slate-800">
                    {{ $activityLog->created_at
                        ->format('F d, Y') }}
                </p>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $activityLog->created_at
                        ->format('h:i:s A') }}
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    Child Profile
                </p>

                @if ($activityLog->childLaborer)
                    <a
                        href="{{ route(
                            'child-laborers.show',
                            $activityLog->childLaborer
                        ) }}"
                        class="mt-2 block font-bold text-sky-700"
                    >
                        {{ $activityLog->childLaborer
                            ->profile_number }}
                    </a>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $activityLog->childLaborer
                            ->full_name }}
                    </p>
                @else
                    <p class="mt-2 text-sm text-slate-500">
                        Not profile-specific
                    </p>
                @endif
            </div>
        </div>

        <div
            class="mt-6 rounded-2xl bg-slate-50 p-5"
        >
            <p class="text-xs font-bold uppercase text-slate-400">
                Description
            </p>

            <p class="mt-2 leading-7 text-slate-700">
                {{ $activityLog->description }}
            </p>
        </div>
    </section>

    <section
        class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm"
    >
        <h2 class="text-xl font-bold text-slate-800">
            Request Information
        </h2>

        <div class="mt-5 grid gap-5 md:grid-cols-2">
            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    IP Address
                </p>

                <p class="mt-2 break-all text-sm text-slate-700">
                    {{ $activityLog->ip_address ?: 'Not recorded' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    Route
                </p>

                <p class="mt-2 break-all text-sm text-slate-700">
                    {{ $activityLog->route_name ?: 'Not recorded' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    Request Method
                </p>

                <p class="mt-2 text-sm text-slate-700">
                    {{ $activityLog->request_method ?: 'Not recorded' }}
                </p>
            </div>

            <div>
                <p class="text-xs font-bold uppercase text-slate-400">
                    URL
                </p>

                <p class="mt-2 break-all text-sm text-slate-700">
                    {{ $activityLog->url ?: 'Not recorded' }}
                </p>
            </div>

            <div class="md:col-span-2">
                <p class="text-xs font-bold uppercase text-slate-400">
                    User Agent
                </p>

                <p class="mt-2 break-all text-sm leading-6 text-slate-700">
                    {{ $activityLog->user_agent ?: 'Not recorded' }}
                </p>
            </div>
        </div>
    </section>

    <section
        class="overflow-hidden rounded-3xl border
               border-slate-200 bg-white shadow-sm"
    >
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-xl font-bold text-slate-800">
                Before and After Values
            </h2>
        </div>

        @if ($changedFields->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-sky-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                                Field
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                                Before
                            </th>

                            <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                                After
                            </th>
                        </tr>
                    </thead>

                    <tbody class="divide-y divide-slate-100">
                        @foreach ($changedFields as $field)
                            <tr>
                                <td class="px-6 py-4 align-top font-bold text-slate-700">
                                    {{ str($field)->headline() }}
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <pre class="max-w-xl whitespace-pre-wrap break-words font-sans text-sm text-red-700">{{ $displayValue(
                                        $oldValues[$field] ?? null
                                    ) }}</pre>
                                </td>

                                <td class="px-6 py-4 align-top">
                                    <pre class="max-w-xl whitespace-pre-wrap break-words font-sans text-sm text-emerald-700">{{ $displayValue(
                                        $newValues[$field] ?? null
                                    ) }}</pre>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="px-6 py-12 text-center text-sm text-slate-500">
                This activity did not contain before-and-after values.
            </p>
        @endif
    </section>

    @if ($activityLog->metadata)
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm"
        >
            <h2 class="text-xl font-bold text-slate-800">
                Additional Metadata
            </h2>

            <pre
                class="mt-5 overflow-x-auto rounded-2xl
                       bg-slate-950 p-5 text-xs
                       leading-6 text-slate-100"
            >{{ json_encode(
                $activityLog->metadata,
                JSON_PRETTY_PRINT
                | JSON_UNESCAPED_UNICODE
                | JSON_UNESCAPED_SLASHES
            ) }}</pre>
        </section>
    @endif

    <div class="flex justify-end">
        <a
            href="{{ route('activity-logs.index') }}"
            class="rounded-xl border border-slate-300
                   px-5 py-3 text-sm font-bold
                   text-slate-600"
        >
            Back to Activity Logs
        </a>
    </div>
</x-dashboard-shell>