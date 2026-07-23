<x-dashboard-shell
    title="Profiling Officer Dashboard"
    subtitle="Create and maintain child laborer profiles."
    badge="Profiling Officer"
>
    <section
        class="rounded-3xl bg-gradient-to-br from-sky-700 via-sky-600 to-cyan-500 p-8 text-white shadow-xl shadow-sky-200/60">
        <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-100">
            Profiling Workspace
        </p>

        <h1 class="mt-3 text-3xl font-black">
            Welcome, {{ $user->name }}
        </h1>

        <p class="mt-4 max-w-2xl text-sm leading-7 text-sky-50">
            Create child laborer profiles, save unfinished records as drafts,
            submit completed profiles, add interventions, and upload
            supporting documents.
        </p>
    </section>

    <section class="grid gap-6 md:grid-cols-3">
        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Draft Profiles
            </p>

            <p class="mt-3 text-3xl font-black text-amber-600">
                0
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Submitted Profiles
            </p>

            <p class="mt-3 text-3xl font-black text-sky-600">
                0
            </p>
        </div>

        <div class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Returned Profiles
            </p>

            <p class="mt-3 text-3xl font-black text-red-600">
                0
            </p>
        </div>
    </section>
</x-dashboard-shell>