<x-workspace-shell
    title="Quality Assurance"
    subtitle="Automated test runs, migration checks, route compilation, security checks, and production asset builds."
>
    @php
        $latest = $reports[0] ?? null;

        $statusClasses = [
            'passed' =>
                'border-emerald-200 bg-emerald-50 text-emerald-700',

            'failed' =>
                'border-red-200 bg-red-50 text-red-700',
        ];
    @endphp

    <section
        class="relative overflow-hidden
               rounded-[28px]
               bg-gradient-to-br from-slate-950
               via-slate-900 to-violet-950
               p-6 text-white shadow-2xl
               sm:p-8"
    >
        <div
            class="absolute -right-16 -top-20
                   h-64 w-64 rounded-full
                   bg-violet-400/10 blur-3xl"
        ></div>

        <div
            class="relative flex flex-col gap-6
                   xl:flex-row xl:items-end
                   xl:justify-between"
        >
            <div class="max-w-3xl">
                <p
                    class="text-xs font-black uppercase
                           tracking-[0.22em]
                           text-violet-300"
                >
                    Release Confidence
                </p>

                <h2
                    class="mt-3 text-3xl font-black
                           tracking-tight"
                >
                    Dedicated CLPMIS QA pipeline
                </h2>

                <p
                    class="mt-3 text-sm leading-7
                           text-slate-300"
                >
                    The pipeline checks migrations and routes,
                    runs the isolated PHPUnit suite, performs
                    the security audit, and compiles frontend
                    production assets. Results are stored as
                    private JSON reports.
                </p>
            </div>

            <div
                class="rounded-2xl bg-white/10
                       px-6 py-5"
            >
                <p
                    class="text-[10px] font-black
                           uppercase tracking-wide
                           text-slate-300"
                >
                    Latest Result
                </p>

                <p
                    class="mt-2 text-3xl font-black
                           {{ $latest
                                && $latest['status']
                                    === 'passed'
                                ? 'text-emerald-300'
                                : 'text-amber-300' }}"
                >
                    {{ $latest
                        ? strtoupper(
                            $latest['status']
                        )
                        : 'NOT RUN' }}
                </p>
            </div>
        </div>
    </section>

    <section
        class="rounded-3xl border
               border-amber-200 bg-amber-50
               p-6"
    >
        <h2
            class="text-lg font-black
                   text-amber-900"
        >
            Dedicated test database required
        </h2>

        <p
            class="mt-2 text-sm leading-7
                   text-amber-800"
        >
            The QA suite is configured for
            <strong>CLPMIS_testing</strong>.
            Never change it to the live CLPMIS database
            because RefreshDatabase recreates test data.
        </p>

        <pre
            class="mt-4 overflow-x-auto
                   rounded-2xl bg-slate-950
                   p-4 text-sm text-amber-200"
        ><code>php artisan clpmis:qa</code></pre>
    </section>

    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200
               bg-white shadow-sm"
    >
        <div
            class="border-b border-slate-200
                   px-6 py-5"
        >
            <h2
                class="text-xl font-black
                       text-slate-900"
            >
                Recent QA Reports
            </h2>

            <p
                class="mt-1 text-sm
                       text-slate-500"
            >
                Reports are stored in
                storage/app/private/quality-assurance.
            </p>
        </div>

        <div class="divide-y divide-slate-100">
            @forelse ($reports as $report)
                <article class="px-6 py-5">
                    <div
                        class="flex flex-col gap-4
                               lg:flex-row
                               lg:items-start
                               lg:justify-between"
                    >
                        <div>
                            <span
                                class="inline-flex rounded-full
                                       border px-3 py-1
                                       text-[10px] font-black
                                       uppercase
                                       {{ $statusClasses[
                                            $report['status']
                                        ] ?? $statusClasses[
                                            'failed'
                                        ] }}"
                            >
                                {{ $report['status'] }}
                            </span>

                            <h3
                                class="mt-3 text-base
                                       font-black
                                       text-slate-800"
                            >
                                QA run
                                {{ \Illuminate\Support\Carbon::parse(
                                    $report['started_at']
                                )->format(
                                    'F d, Y h:i A'
                                ) }}
                            </h3>

                            <p
                                class="mt-1 text-sm
                                       text-slate-500"
                            >
                                {{ $report[
                                    'summary'
                                ]['passed'] }}
                                passed ·
                                {{ $report[
                                    'summary'
                                ]['failed'] }}
                                failed ·
                                {{ $report[
                                    'duration_seconds'
                                ] }}
                                seconds
                            </p>
                        </div>

                        <div
                            class="grid grid-cols-2
                                   gap-3 text-sm
                                   sm:grid-cols-4"
                        >
                            @foreach ([
                                'PHP' =>
                                    $report[
                                        'environment'
                                    ]['php_version'],

                                'Laravel' =>
                                    $report[
                                        'environment'
                                    ]['laravel_version'],

                                'Database' =>
                                    $report[
                                        'environment'
                                    ]['database_name'],

                                'Environment' =>
                                    $report[
                                        'environment'
                                    ]['application_environment'],
                            ] as $label => $value)
                                <div
                                    class="rounded-xl
                                           bg-slate-50
                                           px-3 py-2"
                                >
                                    <p
                                        class="text-[9px]
                                               font-black
                                               uppercase
                                               text-slate-400"
                                    >
                                        {{ $label }}
                                    </p>

                                    <p
                                        class="mt-1 truncate
                                               font-bold
                                               text-slate-700"
                                    >
                                        {{ $value }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div
                        class="mt-5 grid gap-3
                               md:grid-cols-2
                               xl:grid-cols-3"
                    >
                        @foreach (
                            $report['steps']
                            as $step
                        )
                            <div
                                class="rounded-2xl border
                                       p-4
                                       {{ $statusClasses[
                                            $step['status']
                                        ] ?? $statusClasses[
                                            'failed'
                                        ] }}"
                            >
                                <div
                                    class="flex items-start
                                           justify-between
                                           gap-3"
                                >
                                    <p
                                        class="text-sm
                                               font-black"
                                    >
                                        {{ $step['label'] }}
                                    </p>

                                    <span
                                        class="text-[10px]
                                               font-black
                                               uppercase"
                                    >
                                        {{ $step['status'] }}
                                    </span>
                                </div>

                                <p
                                    class="mt-2 text-xs
                                           opacity-75"
                                >
                                    Exit {{ $step[
                                        'exit_code'
                                    ] }}
                                    ·
                                    {{ $step[
                                        'duration_seconds'
                                    ] }}
                                    seconds
                                </p>
                            </div>
                        @endforeach
                    </div>
                </article>
            @empty
                <div
                    class="px-6 py-16 text-center
                           text-sm text-slate-500"
                >
                    No QA report has been generated.
                    Run php artisan clpmis:qa.
                </div>
            @endforelse
        </div>
    </section>
</x-workspace-shell>
