<x-dashboard-shell
    title="Admin Dashboard"
    subtitle="Manage profiles, interventions, audits, documents, and reports."
    badge="Admin"
>
    <section
        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-700 via-sky-600 to-cyan-500 p-8 text-white shadow-xl shadow-sky-200/60">
        <div class="relative z-10 max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-100">
                Administration
            </p>

            <h1 class="mt-3 text-3xl font-black">
                Welcome, {{ auth()->user()->name }}
            </h1>

            <p class="mt-4 max-w-2xl text-sm leading-7 text-sky-50">
                The Admin account includes profile management, intervention
                tracking, audit scheduling, evaluation, findings,
                recommendations, document management, and reporting.
            </p>
        </div>

        <div class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10"></div>
    </section>

    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Managed Users
            </p>

            <p class="mt-3 text-3xl font-black text-slate-800">
                {{ number_format($totalUsers) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Active Users
            </p>

            <p class="mt-3 text-3xl font-black text-emerald-600">
                {{ number_format($activeUsers) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Profiling Officers
            </p>

            <p class="mt-3 text-3xl font-black text-sky-600">
                {{ number_format($profilingOfficers) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Viewers
            </p>

            <p class="mt-3 text-3xl font-black text-cyan-600">
                {{ number_format($viewers) }}
            </p>
        </div>
    </section>

    <section class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-700">
                <span class="text-xl font-black">P</span>
            </div>

            <h2 class="mt-5 text-lg font-bold text-slate-800">
                Child Laborer Profiles
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Create, review, update, approve, archive, and monitor profiles.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-cyan-100 text-cyan-700">
                <span class="text-xl font-black">I</span>
            </div>

            <h2 class="mt-5 text-lg font-bold text-slate-800">
                Interventions
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Record and monitor assistance provided to child laborers.
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-indigo-100 text-indigo-700">
                <span class="text-xl font-black">A</span>
            </div>

            <h2 class="mt-5 text-lg font-bold text-slate-800">
                Audit Management
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                Schedule evaluations and record findings and recommendations.
            </p>
        </div>
    </section>
</x-dashboard-shell>