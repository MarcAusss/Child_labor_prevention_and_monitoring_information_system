<x-dashboard-shell
    title="Work Hazards"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $employmentRecord->occupation }}"
>
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

    {{-- Employment summary --}}
    <section
        class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm"
    >
        <div
            class="grid gap-5 sm:grid-cols-2
                   lg:grid-cols-4"
        >
            <div>
                <p
                    class="text-xs font-bold uppercase
                           tracking-wide text-slate-400"
                >
                    Occupation
                </p>

                <p class="mt-2 font-bold text-slate-800">
                    {{ $employmentRecord->occupation }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-bold uppercase
                           tracking-wide text-slate-400"
                >
                    Employer
                </p>

                <p class="mt-2 font-semibold text-slate-700">
                    {{ $employmentRecord->employer_name
                        ?: 'Not provided' }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-bold uppercase
                           tracking-wide text-slate-400"
                >
                    Work Arrangement
                </p>

                <p class="mt-2 font-semibold text-slate-700">
                    {{ $employmentRecord->work_type }}
                    ·
                    {{ $employmentRecord
                        ->employment_arrangement }}
                </p>
            </div>

            <div>
                <p
                    class="text-xs font-bold uppercase
                           tracking-wide text-slate-400"
                >
                    Work Schedule
                </p>

                <p class="mt-2 font-semibold text-slate-700">
                    {{ $employmentRecord->days_per_week }}
                    day(s) weekly,
                    {{ number_format(
                        (float) $employmentRecord
                            ->hours_per_day,
                        2
                    ) }}
                    hour(s) daily
                </p>
            </div>
        </div>
    </section>

    {{-- Add hazard --}}
    @can('update', $childLaborer)
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Add Work Hazard
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record a hazard or unsafe condition connected
                    to this employment record.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.work-hazards.store',
                    [
                        $childLaborer,
                        $employmentRecord,
                    ]
                ) }}"
            >
                @csrf

                @include(
                    'child-laborers.work-hazards.partials.form',
                    [
                        'workHazard' => null,
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
                        Add Work Hazard
                    </button>
                </div>
            </form>
        </section>
    @endcan

    {{-- Hazard records --}}
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
                    Recorded Hazards
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $workHazards->count() }}
                    work hazard(s)
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a
                    href="{{ route(
                        'child-laborers.employment-records.index',
                        $childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-4 py-2 text-center text-sm
                           font-bold text-slate-600"
                >
                    Employment Records
                </a>

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
        </div>

        <div class="divide-y divide-slate-200">
            @forelse ($workHazards as $workHazard)
                <article class="p-6">
                    <div
                        class="flex flex-col gap-5
                               lg:flex-row
                               lg:items-start
                               lg:justify-between"
                    >
                        <div class="max-w-4xl">
                            <div
                                class="flex flex-wrap
                                       items-center gap-2"
                            >
                                <span
                                    class="rounded-full bg-red-100
                                           px-3 py-1 text-xs
                                           font-bold text-red-700"
                                >
                                    {{ $workHazard->hazard_type }}
                                </span>

                                <span
                                    class="rounded-full bg-slate-100
                                           px-3 py-1 text-xs
                                           font-bold text-slate-600"
                                >
                                    {{ $workHazard
                                        ->exposure_frequency }}
                                </span>

                                @if (
                                    $workHazard->ppe_provided
                                )
                                    <span
                                        class="rounded-full
                                               bg-emerald-100
                                               px-3 py-1 text-xs
                                               font-bold
                                               text-emerald-700"
                                    >
                                        PPE Provided
                                    </span>
                                @else
                                    <span
                                        class="rounded-full
                                               bg-amber-100
                                               px-3 py-1 text-xs
                                               font-bold
                                               text-amber-700"
                                    >
                                        No PPE
                                    </span>
                                @endif
                            </div>

                            <p
                                class="mt-4 leading-7
                                       text-slate-700"
                            >
                                {{ $workHazard
                                    ->hazard_description }}
                            </p>

                            @if (
                                $workHazard
                                    ->flagged_conditions
                            )
                                <div
                                    class="mt-4 flex flex-wrap
                                           gap-2"
                                >
                                    @foreach (
                                        $workHazard
                                            ->flagged_conditions
                                        as $condition
                                    )
                                        <span
                                            class="rounded-lg
                                                   bg-red-50
                                                   px-3 py-1.5
                                                   text-xs font-bold
                                                   text-red-700"
                                        >
                                            {{ $condition }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @can('update', $childLaborer)
                            <div class="flex shrink-0 gap-2">
                                <a
                                    href="{{ route(
                                        'child-laborers.work-hazards.edit',
                                        [
                                            $childLaborer,
                                            $employmentRecord,
                                            $workHazard,
                                        ]
                                    ) }}"
                                    class="rounded-lg bg-sky-50
                                           px-3 py-2 text-xs
                                           font-bold text-sky-700"
                                >
                                    Edit
                                </a>

                                <form
                                    method="POST"
                                    action="{{ route(
                                        'child-laborers.work-hazards.destroy',
                                        [
                                            $childLaborer,
                                            $employmentRecord,
                                            $workHazard,
                                        ]
                                    ) }}"
                                    onsubmit="return confirm('Remove this work hazard?')"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="rounded-lg bg-red-50
                                               px-3 py-2 text-xs
                                               font-bold text-red-700"
                                    >
                                        Remove
                                    </button>
                                </form>
                            </div>
                        @endcan
                    </div>

                    <div
                        class="mt-6 grid gap-4
                               md:grid-cols-2"
                    >
                        <div
                            class="rounded-2xl bg-slate-50
                                   p-4"
                        >
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Equipment or Machinery
                            </p>

                            <p
                                class="mt-2 text-sm
                                       leading-6 text-slate-700"
                            >
                                {{ $workHazard
                                    ->equipment_machinery
                                    ?: 'None recorded' }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50
                                   p-4"
                        >
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Chemicals or Substances
                            </p>

                            <p
                                class="mt-2 text-sm
                                       leading-6 text-slate-700"
                            >
                                {{ $workHazard
                                    ->chemicals_substances
                                    ?: 'None recorded' }}
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50
                                   p-4"
                        >
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Protective Equipment
                            </p>

                            <p
                                class="mt-2 text-sm
                                       leading-6 text-slate-700"
                            >
                                @if ($workHazard->ppe_provided)
                                    {{ $workHazard
                                        ->ppe_description }}
                                @else
                                    No personal protective
                                    equipment was reported.
                                @endif
                            </p>
                        </div>

                        <div
                            class="rounded-2xl bg-slate-50
                                   p-4"
                        >
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Injuries or Incidents
                            </p>

                            <p
                                class="mt-2 text-sm
                                       leading-6 text-slate-700"
                            >
                                {{ $workHazard
                                    ->injuries_incidents
                                    ?: 'No injuries or incidents recorded' }}
                            </p>
                        </div>
                    </div>
                </article>
            @empty
                <div
                    class="px-6 py-12 text-center
                           text-sm text-slate-500"
                >
                    No work hazard has been recorded for this
                    employment.
                </div>
            @endforelse
        </div>
    </section>
</x-dashboard-shell>