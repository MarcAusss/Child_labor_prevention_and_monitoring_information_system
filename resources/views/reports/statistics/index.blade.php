<x-dashboard-shell
    title="Statistical and Summary Reports"
    subtitle="Geographic, demographic, education, employment, hazard, intervention, and audit summaries."
    badge="Reports"
>
    @php
        $sections = [
            [
                'title' => 'Sex Distribution',
                'items' => $report['sexDistribution'],
            ],
            [
                'title' => 'Age Distribution',
                'items' => $report['ageDistribution'],
            ],
            [
                'title' => 'Profile Status',
                'items' => $report['statusDistribution'],
            ],
            [
                'title' => 'Profile Creation Trend',
                'items' => $report['profileTrend'],
            ],
            [
                'title' => 'Profiles by Region',
                'items' => $report['regions'],
            ],
            [
                'title' => 'Profiles by Province',
                'items' => $report['provinces'],
            ],
            [
                'title' => 'Current Education Status',
                'items' => $report['education'],
            ],
            [
                'title' => 'Employment Coverage',
                'items' => $report['employment'],
            ],
            [
                'title' => 'Current Work Types',
                'items' => $report['workTypes'],
            ],
            [
                'title' => 'Recorded Work Hazards',
                'items' => $report['hazards'],
            ],
            [
                'title' => 'Intervention Types',
                'items' => $report['interventionTypes'],
            ],
            [
                'title' => 'Intervention Status',
                'items' => $report['interventionStatuses'],
            ],
            [
                'title' => 'Audit Schedule Status',
                'items' => $report['auditScheduleStatuses'],
            ],
            [
                'title' => 'Audit Evaluation Status',
                'items' => $report['auditEvaluationStatuses'],
            ],
        ];
    @endphp

    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4 md:grid-cols-2
                   xl:grid-cols-5"
        >
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

                    @foreach ($statusOptions as $status)
                        <option
                            value="{{ $status }}"
                            @selected(
                                $filters['status'] === $status
                            )
                        >
                            {{ $status }}
                        </option>
                    @endforeach
                </select>
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
                                (int) $filters['region_id']
                                    === $region->id
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

                    @foreach ($provinces as $province)
                        <option
                            value="{{ $province->id }}"
                            @selected(
                                (int) $filters['province_id']
                                    === $province->id
                            )
                        >
                            {{ $province->name }}
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
                    Profiles Created From
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
                    Profiles Created To
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
                class="flex flex-wrap items-end gap-2
                       md:col-span-2 xl:col-span-5"
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
                        'reports.statistics.index'
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Reset
                </a>

                @can('export-reports')
                    <a
                        href="{{ route(
                            'reports.statistics.export.csv',
                            request()->query()
                        ) }}"
                        class="rounded-xl bg-emerald-600
                               px-5 py-3 text-sm font-bold
                               text-white"
                    >
                        Export CSV
                    </a>
                @endcan

                @can('print-reports')
                    <a
                        href="{{ route(
                            'reports.statistics.print',
                            request()->query()
                        ) }}"
                        target="_blank"
                        class="rounded-xl bg-slate-800
                               px-5 py-3 text-sm font-bold
                               text-white"
                    >
                        Print Report
                    </a>
                @endcan
            </div>
        </form>
    </section>

    <section
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-3"
    >
        @foreach ([
            [
                'label' => 'Total Profiles',
                'value' => number_format(
                    $report['summary']['total_profiles']
                ),
                'classes' =>
                    'border-sky-200 bg-sky-50 text-sky-700',
            ],
            [
                'label' => 'Currently Working',
                'value' => number_format(
                    $report['summary']['currently_working']
                ),
                'classes' =>
                    'border-amber-200 bg-amber-50 text-amber-700',
            ],
            [
                'label' => 'Profiles With Hazards',
                'value' => number_format(
                    $report['summary']['with_hazards']
                ),
                'classes' =>
                    'border-red-200 bg-red-50 text-red-700',
            ],
            [
                'label' => 'With Interventions',
                'value' => number_format(
                    $report['summary']['with_interventions']
                ),
                'classes' =>
                    'border-emerald-200 bg-emerald-50 text-emerald-700',
            ],
            [
                'label' => 'Completed Audits',
                'value' => number_format(
                    $report['summary']['completed_audits']
                ),
                'classes' =>
                    'border-violet-200 bg-violet-50 text-violet-700',
            ],
            [
                'label' => 'Intervention Value',
                'value' => '₱'.number_format(
                    $report['summary']['intervention_value'],
                    2
                ),
                'classes' =>
                    'border-blue-200 bg-blue-50 text-blue-700',
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
                    {{ $card['value'] }}
                </p>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        @foreach ($sections as $section)
            <article
                class="rounded-3xl border border-slate-200
                       bg-white p-6 shadow-sm"
            >
                <div class="mb-5">
                    <h2 class="text-lg font-bold text-slate-800">
                        {{ $section['title'] }}
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        {{ number_format(
                            $section['items']->sum('total')
                        ) }}
                        recorded item(s)
                    </p>
                </div>

                @if ($section['items']->isNotEmpty())
                    <div class="space-y-4">
                        @foreach ($section['items'] as $item)
                            <div>
                                <div
                                    class="mb-2 flex items-center
                                           justify-between gap-4"
                                >
                                    <p
                                        class="min-w-0 truncate
                                               text-sm font-semibold
                                               text-slate-700"
                                        title="{{ $item['label'] }}"
                                    >
                                        {{ $item['label'] }}
                                    </p>

                                    <p
                                        class="shrink-0 text-sm
                                               font-bold text-slate-800"
                                    >
                                        {{ number_format(
                                            $item['total']
                                        ) }}
                                        <span
                                            class="font-normal
                                                   text-slate-400"
                                        >
                                            ({{ number_format(
                                                $item['percentage'],
                                                2
                                            ) }}%)
                                        </span>
                                    </p>
                                </div>

                                <div
                                    class="h-2 overflow-hidden
                                           rounded-full bg-slate-100"
                                >
                                    <div
                                        class="h-full rounded-full
                                               bg-sky-500"
                                        style="width: {{ min(
                                            100,
                                            $item['percentage']
                                        ) }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p
                        class="rounded-2xl bg-slate-50
                               px-4 py-8 text-center
                               text-sm text-slate-500"
                    >
                        No data is available for this section.
                    </p>
                @endif
            </article>
        @endforeach
    </section>
</x-dashboard-shell>
