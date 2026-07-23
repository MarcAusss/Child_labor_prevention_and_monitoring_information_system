<x-dashboard-shell
    title="Interventions and Assistance"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $childLaborer->status }}"
>
    @php
        $pendingCount = $allInterventions
            ->where('status', 'Pending')
            ->count();

        $ongoingCount = $allInterventions
            ->where('status', 'Ongoing')
            ->count();

        $completedCount = $allInterventions
            ->where('status', 'Completed')
            ->count();

        $totalValue = $allInterventions->sum(
            fn ($item) =>
                (float) ($item->amount ?? 0)
        );
    @endphp

    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 p-4 text-sm font-semibold
                   text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200
                   bg-red-50 p-5"
        >
            <h2 class="font-bold text-red-800">
                Please correct the following:
            </h2>

            <ul
                class="mt-3 list-inside list-disc
                       space-y-1 text-sm text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Summary --}}
    <section
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-4"
    >
        <article
            class="rounded-2xl border border-amber-200
                   bg-amber-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-amber-600"
            >
                Pending
            </p>

            <p class="mt-2 text-3xl font-bold text-amber-700">
                {{ $pendingCount }}
            </p>
        </article>

        <article
            class="rounded-2xl border border-sky-200
                   bg-sky-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-sky-600"
            >
                Ongoing
            </p>

            <p class="mt-2 text-3xl font-bold text-sky-700">
                {{ $ongoingCount }}
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
                Completed
            </p>

            <p
                class="mt-2 text-3xl font-bold
                       text-emerald-700"
            >
                {{ $completedCount }}
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
                Total Recorded Value
            </p>

            <p
                class="mt-2 text-2xl font-bold
                       text-violet-700"
            >
                ₱{{ number_format(
                    $totalValue,
                    2
                ) }}
            </p>
        </article>
    </section>

    {{-- Add intervention --}}
    @can('manageInterventions', $childLaborer)
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Add Intervention or Assistance
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record assistance, referrals, support,
                    training, services, or benefits provided to
                    the child laborer.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.interventions.store',
                    $childLaborer
                ) }}"
            >
                @csrf

                @include(
                    'child-laborers.interventions.partials.form',
                    [
                        'intervention' => null,
                    ]
                )

                <div
                    class="mt-8 flex justify-end
                           border-t border-slate-200 pt-6"
                >
                    <button
                        type="submit"
                        class="rounded-xl bg-sky-600
                               px-6 py-3 text-sm font-bold
                               text-white hover:bg-sky-700"
                    >
                        Add Intervention
                    </button>
                </div>
            </form>
        </section>
    @endcan

    {{-- Filters --}}
    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4 md:grid-cols-[1fr_1fr_auto]"
        >
            <div>
                <label
                    for="filter_status"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Status
                </label>

                <select
                    id="filter_status"
                    name="status"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300 focus:border-sky-500
                           focus:ring-sky-500"
                >
                    <option value="">
                        All statuses
                    </option>

                    @foreach ($statuses as $status)
                        <option
                            value="{{ $status }}"
                            @selected(
                                $selectedStatus
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
                    for="filter_type"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Intervention Type
                </label>

                <select
                    id="filter_type"
                    name="type"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300 focus:border-sky-500
                           focus:ring-sky-500"
                >
                    <option value="">
                        All intervention types
                    </option>

                    @foreach (
                        $interventionTypes
                        as $interventionType
                    )
                        <option
                            value="{{ $interventionType }}"
                            @selected(
                                $selectedType
                                    === $interventionType
                            )
                        >
                            {{ $interventionType }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-end gap-2">
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600
                           px-5 py-3 text-sm font-bold
                           text-white"
                >
                    Filter
                </button>

                <a
                    href="{{ route(
                        'child-laborers.interventions.index',
                        $childLaborer
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

    {{-- Intervention list --}}
    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200 bg-white
               shadow-sm"
    >
        <div
            class="flex flex-col gap-4 border-b
                   border-slate-200 p-6 sm:flex-row
                   sm:items-center sm:justify-between"
        >
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Intervention History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $interventions->count() }}
                    displayed intervention(s)
                </p>
            </div>

            <a
                href="{{ route(
                    'child-laborers.show',
                    $childLaborer
                ) }}"
                class="rounded-xl border border-slate-300
                       px-4 py-2 text-center text-sm
                       font-bold text-slate-600"
            >
                Back to Profile
            </a>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse (
                $interventions as $intervention
            )
                @php
                    $statusClasses = match (
                        $intervention->status
                    ) {
                        'Pending' =>
                            'bg-amber-100 text-amber-700',

                        'Ongoing' =>
                            'bg-sky-100 text-sky-700',

                        'Completed' =>
                            'bg-emerald-100 text-emerald-700',

                        'Cancelled' =>
                            'bg-slate-200 text-slate-700',

                        'Discontinued' =>
                            'bg-red-100 text-red-700',

                        default =>
                            'bg-slate-100 text-slate-700',
                    };
                @endphp

                <article class="p-6">
                    <div
                        class="flex flex-col gap-5
                               lg:flex-row lg:items-start
                               lg:justify-between"
                    >
                        <div class="max-w-4xl">
                            <div
                                class="flex flex-wrap
                                       items-center gap-2"
                            >
                                <span
                                    class="rounded-full bg-violet-100
                                           px-3 py-1 text-xs
                                           font-bold text-violet-700"
                                >
                                    {{ $intervention
                                        ->intervention_type }}
                                </span>

                                <span
                                    class="rounded-full px-3 py-1
                                           text-xs font-bold
                                           {{ $statusClasses }}"
                                >
                                    {{ $intervention->status }}
                                </span>
                            </div>

                            <h3
                                class="mt-4 text-lg font-bold
                                       text-slate-800"
                            >
                                {{ $intervention->provider }}
                            </h3>

                            <p
                                class="mt-3 whitespace-pre-line
                                       text-sm leading-7
                                       text-slate-700"
                            >{{ $intervention->description }}</p>
                        </div>

                        @can(
                            'manageInterventions',
                            $childLaborer
                        )
                            <a
                                href="{{ route(
                                    'child-laborers.interventions.edit',
                                    [
                                        $childLaborer,
                                        $intervention,
                                    ]
                                ) }}"
                                class="shrink-0 rounded-lg
                                       bg-sky-50 px-4 py-2
                                       text-xs font-bold
                                       text-sky-700"
                            >
                                Edit Intervention
                            </a>
                        @endcan
                    </div>

                    <div
                        class="mt-6 grid gap-4
                               sm:grid-cols-2
                               lg:grid-cols-4"
                    >
                        <div
                            class="rounded-2xl bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-400"
                            >
                                Date Provided
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-700"
                            >
                                {{ $intervention->date_provided
                                    ?->format('F d, Y')
                                    ?? 'Not yet provided' }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-400"
                            >
                                Date Completed
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-700"
                            >
                                {{ $intervention->date_completed
                                    ?->format('F d, Y')
                                    ?? 'Not completed' }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-400"
                            >
                                Recorded Value
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-700"
                            >
                                @if (
                                    $intervention->amount
                                    !== null
                                )
                                    ₱{{ number_format(
                                        (float) $intervention
                                            ->amount,
                                        2
                                    ) }}
                                @else
                                    Not recorded
                                @endif
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-400"
                            >
                                Recorded By
                            </p>

                            <p
                                class="mt-2 text-sm font-semibold
                                       text-slate-700"
                            >
                                {{ $intervention
                                    ->recorder?->name
                                    ?? 'Unknown user' }}
                            </p>
                        </div>
                    </div>

                    @if ($intervention->remarks)
                        <div
                            class="mt-4 rounded-2xl
                                   border border-slate-200
                                   bg-slate-50 p-4"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       text-slate-400"
                            >
                                Remarks
                            </p>

                            <p
                                class="mt-2 whitespace-pre-line
                                       text-sm leading-6
                                       text-slate-700"
                            >{{ $intervention->remarks }}</p>
                        </div>
                    @endif

                    <p
                        class="mt-4 text-xs text-slate-400"
                    >
                        Last updated
                        {{ $intervention->updated_at
                            ->format('F d, Y \a\t h:i A') }}

                        @if ($intervention->lastUpdater)
                            by
                            {{ $intervention
                                ->lastUpdater
                                ->name }}
                        @endif
                    </p>
                </article>
            @empty
                <div
                    class="px-6 py-12 text-center
                           text-sm text-slate-500"
                >
                    No intervention matches the selected filters.
                </div>
            @endforelse
        </div>
    </section>
</x-dashboard-shell>