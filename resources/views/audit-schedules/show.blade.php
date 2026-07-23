<x-dashboard-shell
    title="Audit Schedule"
    subtitle="{{ $auditSchedule->childLaborer->profile_number }} — {{ $auditSchedule->childLaborer->full_name }}"
    badge="{{ $auditSchedule->status }}"
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
            <ul
                class="list-inside list-disc space-y-1
                       text-sm text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section
        class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm"
    >
        <div
            class="flex flex-col gap-5 lg:flex-row
                   lg:items-start lg:justify-between"
        >
            <div
                class="grid flex-1 gap-5 sm:grid-cols-2
                       xl:grid-cols-4"
            >
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Scheduled Date
                    </p>

                    <p class="mt-2 font-bold text-slate-800">
                        {{ $auditSchedule
                            ->scheduled_at
                            ->format('F d, Y') }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $auditSchedule
                            ->scheduled_at
                            ->format('h:i A') }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Assigned Admin
                    </p>

                    <p class="mt-2 font-bold text-slate-800">
                        {{ $auditSchedule
                            ->assignedAdministrator
                            ->name }}
                    </p>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ $auditSchedule
                            ->assignedAdministrator
                            ->email }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Location
                    </p>

                    <p class="mt-2 font-semibold text-slate-700">
                        {{ $auditSchedule->location
                            ?: 'Not provided' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Created By
                    </p>

                    <p class="mt-2 font-semibold text-slate-700">
                        {{ $auditSchedule->creator->name }}
                    </p>
                </div>
            </div>

            <div class="flex shrink-0 flex-wrap gap-2">
                <a
                    href="{{ route(
                        'child-laborers.show',
                        $auditSchedule->childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-4 py-2 text-sm font-bold
                           text-slate-600"
                >
                    Child Profile
                </a>

                @can('update', $auditSchedule)
                    <a
                        href="{{ route(
                            'audit-schedules.edit',
                            $auditSchedule
                        ) }}"
                        class="rounded-xl bg-sky-600
                               px-4 py-2 text-sm font-bold
                               text-white"
                    >
                        Edit Schedule
                    </a>
                @endcan
            </div>
        </div>

        @if ($auditSchedule->remarks)
            <div
                class="mt-6 rounded-2xl bg-slate-50 p-5"
            >
                <p class="text-xs font-bold uppercase text-slate-400">
                    Remarks
                </p>

                <p
                    class="mt-2 whitespace-pre-line text-sm
                           leading-7 text-slate-700"
                >{{ $auditSchedule->remarks }}</p>
            </div>
        @endif
    </section>

    @if (
        $auditSchedule->isEditable()
        && auth()->user()->can(
            'create',
            [
                \App\Models\AuditEvaluation::class,
                $auditSchedule,
            ]
        )
    )
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Add Audit Evaluation
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record findings and recommendations. Save as
                    Draft, submit for review, or finalize the
                    evaluation.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'audit-schedules.evaluations.store',
                    $auditSchedule
                ) }}"
            >
                @csrf

                @include(
                    'audit-evaluations.partials.form',
                    [
                        'auditEvaluation' => null,
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
                        Save Evaluation
                    </button>
                </div>
            </form>
        </section>
    @endif

    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200 bg-white
               shadow-sm"
    >
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-xl font-bold text-slate-800">
                Evaluation History
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ $auditSchedule->evaluations->count() }}
                evaluation record(s)
            </p>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse (
                $auditSchedule->evaluations
                as $auditEvaluation
            )
                @php
                    $evaluationStatusClasses = match (
                        $auditEvaluation->status
                    ) {
                        'Draft' =>
                            'bg-slate-100 text-slate-700',

                        'Submitted' =>
                            'bg-sky-100 text-sky-700',

                        'Finalized' =>
                            'bg-emerald-100 text-emerald-700',

                        default =>
                            'bg-slate-100 text-slate-700',
                    };
                @endphp

                <article class="p-6">
                    <div
                        class="flex flex-col gap-4
                               lg:flex-row lg:items-start
                               lg:justify-between"
                    >
                        <div>
                            <div class="flex flex-wrap items-center gap-2">
                                <span
                                    class="rounded-full px-3 py-1
                                           text-xs font-bold
                                           {{ $evaluationStatusClasses }}"
                                >
                                    {{ $auditEvaluation->status }}
                                </span>

                                <span class="text-xs text-slate-400">
                                    {{ $auditEvaluation
                                        ->evaluation_date
                                        ->format('F d, Y') }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm text-slate-500">
                                Evaluated by
                                <span class="font-bold text-slate-700">
                                    {{ $auditEvaluation
                                        ->evaluator
                                        ->name }}
                                </span>
                            </p>
                        </div>

                        @can('update', $auditEvaluation)
                            <a
                                href="{{ route(
                                    'audit-schedules.evaluations.edit',
                                    [
                                        $auditSchedule,
                                        $auditEvaluation,
                                    ]
                                ) }}"
                                class="shrink-0 rounded-xl
                                       bg-sky-50 px-4 py-2
                                       text-xs font-bold
                                       text-sky-700"
                            >
                                Edit Evaluation
                            </a>
                        @endcan
                    </div>

                    <div class="mt-6 grid gap-5 lg:grid-cols-2">
                        <div
                            class="rounded-2xl border
                                   border-slate-200
                                   bg-slate-50 p-5"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       tracking-wide text-slate-400"
                            >
                                Findings
                            </p>

                            <p
                                class="mt-3 whitespace-pre-line
                                       text-sm leading-7
                                       text-slate-700"
                            >{{ $auditEvaluation->findings
                                ?: 'No findings recorded.' }}</p>
                        </div>

                        <div
                            class="rounded-2xl border
                                   border-slate-200
                                   bg-slate-50 p-5"
                        >
                            <p
                                class="text-xs font-bold uppercase
                                       tracking-wide text-slate-400"
                            >
                                Recommendations
                            </p>

                            <p
                                class="mt-3 whitespace-pre-line
                                       text-sm leading-7
                                       text-slate-700"
                            >{{ $auditEvaluation->recommendations
                                ?: 'No recommendations recorded.' }}</p>
                        </div>
                    </div>

                    @if ($auditEvaluation->finalized_at)
                        <p class="mt-4 text-xs text-slate-400">
                            Finalized
                            {{ $auditEvaluation
                                ->finalized_at
                                ->format(
                                    'F d, Y h:i A'
                                ) }}
                        </p>
                    @elseif ($auditEvaluation->submitted_at)
                        <p class="mt-4 text-xs text-slate-400">
                            Submitted
                            {{ $auditEvaluation
                                ->submitted_at
                                ->format(
                                    'F d, Y h:i A'
                                ) }}
                        </p>
                    @endif
                </article>
            @empty
                <div
                    class="px-6 py-14 text-center
                           text-sm text-slate-500"
                >
                    No evaluation has been recorded for this audit.
                </div>
            @endforelse
        </div>
    </section>
</x-dashboard-shell>