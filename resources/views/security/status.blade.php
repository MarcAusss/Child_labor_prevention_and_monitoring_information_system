<x-workspace-shell
    title="System Security"
    subtitle="Administrative security configuration, storage, database, and runtime checks."
>
    @php
        $statusClasses = [
            'pass' =>
                'border-emerald-200 bg-emerald-50 text-emerald-700',

            'warning' =>
                'border-amber-200 bg-amber-50 text-amber-700',

            'critical' =>
                'border-red-200 bg-red-50 text-red-700',
        ];
    @endphp

    <section
        class="relative overflow-hidden
               rounded-[28px]
               bg-gradient-to-br from-slate-950
               via-slate-900 to-sky-950
               p-6 text-white shadow-2xl
               sm:p-8"
    >
        <div
            class="absolute -right-16 -top-20
                   h-56 w-56 rounded-full
                   bg-sky-400/10 blur-3xl"
        ></div>

        <div
            class="relative flex flex-col gap-6
                   xl:flex-row xl:items-end
                   xl:justify-between"
        >
            <div>
                <p
                    class="text-xs font-black uppercase
                           tracking-[0.22em]
                           text-sky-300"
                >
                    Security Readiness
                </p>

                <h2
                    class="mt-3 text-3xl font-black
                           tracking-tight"
                >
                    {{ $summary['score'] }}% configuration score
                </h2>

                <p
                    class="mt-3 max-w-3xl text-sm
                           leading-7 text-slate-300"
                >
                    This page reports configuration and
                    infrastructure checks only. It does not
                    replace an independent penetration test,
                    code review, database backup, or server
                    security assessment.
                </p>
            </div>

            <div
                class="grid grid-cols-3 gap-3"
            >
                <div
                    class="rounded-2xl bg-white/10
                           px-5 py-4 text-center"
                >
                    <p
                        class="text-2xl font-black
                               text-emerald-300"
                    >
                        {{ $summary['passed'] }}
                    </p>

                    <p
                        class="mt-1 text-[10px]
                               font-bold uppercase
                               text-slate-300"
                    >
                        Passed
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-white/10
                           px-5 py-4 text-center"
                >
                    <p
                        class="text-2xl font-black
                               text-amber-300"
                    >
                        {{ $summary['warnings'] }}
                    </p>

                    <p
                        class="mt-1 text-[10px]
                               font-bold uppercase
                               text-slate-300"
                    >
                        Warnings
                    </p>
                </div>

                <div
                    class="rounded-2xl bg-white/10
                           px-5 py-4 text-center"
                >
                    <p
                        class="text-2xl font-black
                               text-red-300"
                    >
                        {{ $summary['critical'] }}
                    </p>

                    <p
                        class="mt-1 text-[10px]
                               font-bold uppercase
                               text-slate-300"
                    >
                        Critical
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-4"
    >
        @foreach ([
            'Application Environment' =>
                $environment[
                    'application_environment'
                ],

            'PHP Version' =>
                $environment[
                    'php_version'
                ],

            'Laravel Version' =>
                $environment[
                    'laravel_version'
                ],

            'Database Connection' =>
                $environment[
                    'database_connection'
                ],

            'Session Driver' =>
                $environment[
                    'session_driver'
                ],

            'Cache Store' =>
                $environment[
                    'cache_store'
                ],

            'Queue Connection' =>
                $environment[
                    'queue_connection'
                ],
        ] as $label => $value)
            <article
                class="rounded-2xl border
                       border-slate-200 bg-white
                       p-5 shadow-sm"
            >
                <p
                    class="text-[10px] font-black
                           uppercase tracking-wide
                           text-slate-400"
                >
                    {{ $label }}
                </p>

                <p
                    class="mt-3 break-words text-lg
                           font-black text-slate-800"
                >
                    {{ $value ?: 'Not configured' }}
                </p>
            </article>
        @endforeach
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
                Security Checks
            </h2>

            <p
                class="mt-1 text-sm text-slate-500"
            >
                {{ $summary['total'] }}
                checks covering application,
                transport, session, data,
                storage, migrations, and PHP.
            </p>
        </div>

        <div class="divide-y divide-slate-100">
            @foreach ($checks as $check)
                <article
                    class="grid gap-5 px-6 py-5
                           lg:grid-cols-[220px_1fr_1fr]"
                >
                    <div>
                        <span
                            class="inline-flex rounded-full
                                   border px-3 py-1
                                   text-[10px] font-black
                                   uppercase
                                   {{ $statusClasses[
                                        $check['status']
                                    ] }}"
                        >
                            {{ $check['status'] }}
                        </span>

                        <p
                            class="mt-3 text-xs font-bold
                                   uppercase tracking-wide
                                   text-slate-400"
                        >
                            {{ $check['category'] }}
                        </p>

                        <h3
                            class="mt-1 text-sm font-black
                                   text-slate-800"
                        >
                            {{ $check['label'] }}
                        </h3>
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-black
                                   uppercase tracking-wide
                                   text-slate-400"
                        >
                            Current Result
                        </p>

                        <p
                            class="mt-2 text-sm leading-6
                                   text-slate-600"
                        >
                            {{ $check['details'] }}
                        </p>
                    </div>

                    <div>
                        <p
                            class="text-[10px] font-black
                                   uppercase tracking-wide
                                   text-slate-400"
                        >
                            Recommendation
                        </p>

                        <p
                            class="mt-2 text-sm leading-6
                                   text-slate-600"
                        >
                            {{ $check[
                                'recommendation'
                            ] }}
                        </p>
                    </div>
                </article>
            @endforeach
        </div>
    </section>

    <section
        class="rounded-3xl border
               border-sky-200 bg-sky-50
               p-6"
    >
        <h2
            class="text-lg font-black
                   text-sky-900"
        >
            Command-line verification
        </h2>

        <p
            class="mt-2 text-sm leading-6
                   text-sky-800"
        >
            Run this command after deployment or
            configuration changes:
        </p>

        <pre
            class="mt-4 overflow-x-auto
                   rounded-2xl bg-slate-950
                   p-4 text-sm text-sky-200"
        ><code>php artisan clpmis:security-check</code></pre>
    </section>
</x-workspace-shell>
