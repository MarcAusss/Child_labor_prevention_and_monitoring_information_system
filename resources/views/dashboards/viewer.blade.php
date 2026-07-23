<x-dashboard-shell
    title="Viewer Dashboard"
    subtitle="Read-only access to authorized CLPMIS information."
    badge="Viewer"
>
    <section
        class="rounded-3xl bg-gradient-to-br from-sky-700 via-sky-600 to-cyan-500 p-8 text-white shadow-xl shadow-sky-200/60">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-100">
            Information Portal
        </p>

        <h1 class="mt-3 text-3xl font-black">
            Welcome, {{ $user->name }}
        </h1>

        <p class="mt-4 max-w-2xl text-sm leading-7 text-sky-50">
            Your account has read-only access to authorized profiles,
            intervention summaries, documents, and reports.
        </p>
    </section>

    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <h2 class="text-xl font-bold text-slate-800">
            Viewer Access
        </h2>

        <p class="mt-3 text-sm leading-7 text-slate-500">
            Record editing, audit evaluation, user management, and system
            configuration are unavailable to Viewer accounts.
        </p>
    </section>
</x-dashboard-shell>