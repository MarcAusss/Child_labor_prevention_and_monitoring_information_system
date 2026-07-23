<x-dashboard-shell
    title="Health Information"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="Restricted Information"
>
    <div
        class="rounded-2xl border border-amber-200
               bg-amber-50 px-5 py-4 text-sm
               leading-6 text-amber-800"
    >
        Health information contains sensitive information about
        a minor. Access and updates are limited to authorized
        personnel.
    </div>

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

    @can('updateHealth', $childLaborer)
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Add Health Assessment
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record reported health conditions, complaints,
                    injuries, treatment, disability information,
                    and psychosocial concerns.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.health-information.store',
                    $childLaborer
                ) }}"
            >
                @csrf

                @include(
                    'child-laborers.health-information.partials.form',
                    [
                        'healthInformation' => null,
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
                        Add Health Assessment
                    </button>
                </div>
            </form>
        </section>
    @endcan

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
                    Health Assessment History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $healthInformationRecords->count() }}
                    health assessment(s)
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
                $healthInformationRecords
                as $healthInformation
            )
                <article class="p-6">
                    <div
                        class="flex flex-col gap-5
                               lg:flex-row lg:items-start
                               lg:justify-between"
                    >
                        <div>
                            <div
                                class="flex flex-wrap
                                       items-center gap-2"
                            >
                                <span
                                    class="rounded-full bg-sky-100
                                           px-3 py-1 text-xs
                                           font-bold text-sky-700"
                                >
                                    {{ $healthInformation
                                        ->assessment_date
                                        ->format('F d, Y') }}
                                </span>

                                @if (
                                    $healthInformation
                                        ->is_current
                                )
                                    <span
                                        class="rounded-full
                                               bg-emerald-100
                                               px-3 py-1 text-xs
                                               font-bold
                                               text-emerald-700"
                                    >
                                        Current
                                    </span>
                                @endif

                                @if (
                                    $healthInformation
                                        ->has_disability
                                )
                                    <span
                                        class="rounded-full
                                               bg-violet-100
                                               px-3 py-1 text-xs
                                               font-bold
                                               text-violet-700"
                                    >
                                        Disability Reported
                                    </span>
                                @endif
                            </div>

                            <p
                                class="mt-4 text-base
                                       font-semibold leading-7
                                       text-slate-800"
                            >
                                {{ $healthInformation
                                    ->health_condition
                                    ?: 'No specific health condition recorded' }}
                            </p>

                            @if (
                                $healthInformation
                                    ->concern_indicators
                            )
                                <div
                                    class="mt-4 flex flex-wrap
                                           gap-2"
                                >
                                    @foreach (
                                        $healthInformation
                                            ->concern_indicators
                                        as $indicator
                                    )
                                        <span
                                            class="rounded-lg
                                                   bg-amber-50
                                                   px-3 py-1.5
                                                   text-xs font-bold
                                                   text-amber-700"
                                        >
                                            {{ $indicator }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>

                        @can(
                            'updateHealth',
                            $childLaborer
                        )
                            <div class="flex shrink-0 gap-2">
                                <a
                                    href="{{ route(
                                        'child-laborers.health-information.edit',
                                        [
                                            $childLaborer,
                                            $healthInformation,
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
                                        'child-laborers.health-information.destroy',
                                        [
                                            $childLaborer,
                                            $healthInformation,
                                        ]
                                    ) }}"
                                    onsubmit="return confirm('Remove this health assessment?')"
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
                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Disability Information
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                @if (
                                    $healthInformation
                                        ->has_disability
                                )
                                    {{ $healthInformation
                                        ->disability_details }}
                                @else
                                    No disability reported.
                                @endif
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Injury History
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                {{ $healthInformation
                                    ->injury_history
                                    ?: 'No injury history recorded' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Current Complaints
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                {{ $healthInformation
                                    ->current_complaints
                                    ?: 'No current complaints recorded' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Mental-Health Concerns
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                {{ $healthInformation
                                    ->mental_health_concerns
                                    ?: 'No concerns recorded' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Treatment Received
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                {{ $healthInformation
                                    ->treatment_received
                                    ?: 'No treatment recorded' }}
                            </p>
                        </div>

                        <div class="rounded-2xl bg-slate-50 p-4">
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Health Facility
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                {{ $healthInformation
                                    ->health_facility
                                    ?: 'No facility recorded' }}
                            </p>
                        </div>
                    </div>

                    @if ($healthInformation->remarks)
                        <div
                            class="mt-4 rounded-2xl
                                   border border-slate-200 p-4"
                        >
                            <p
                                class="text-xs font-bold
                                       uppercase text-slate-400"
                            >
                                Remarks
                            </p>

                            <p
                                class="mt-2 text-sm leading-6
                                       text-slate-700"
                            >
                                {{ $healthInformation->remarks }}
                            </p>
                        </div>
                    @endif
                </article>
            @empty
                <div
                    class="px-6 py-12 text-center
                           text-sm text-slate-500"
                >
                    No health assessment has been recorded.
                </div>
            @endforelse
        </div>
    </section>
</x-dashboard-shell>