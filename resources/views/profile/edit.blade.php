<x-dashboard-shell
    title="Account Settings"
    subtitle="Manage your personal account information and security credentials."
    badge="My Account"
>
    <section class="grid gap-6 xl:grid-cols-[.75fr_1.25fr]">
        <aside class="rounded-3xl border border-slate-200 bg-white p-6 shadow-soft">
            <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-slate-950 text-2xl font-extrabold text-sky-200 shadow-panel">
                {{ str(auth()->user()->name)->substr(0, 1)->upper() }}
            </div>

            <p class="mt-5 text-xs font-extrabold uppercase tracking-[0.2em] text-sky-700">CLPMIS account</p>
            <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950">{{ auth()->user()->name }}</h2>
            <p class="mt-1 text-sm text-slate-500">{{ auth()->user()->email }}</p>

            <div class="mt-6 space-y-3">
                <div class="rounded-2xl bg-slate-50 p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wide text-slate-400">Assigned role</p>
                    <p class="mt-1 text-sm font-extrabold text-slate-800">{{ auth()->user()->role?->name ?? 'No assigned role' }}</p>
                </div>

                <div class="rounded-2xl bg-sky-50 p-4">
                    <p class="text-[10px] font-extrabold uppercase tracking-wide text-sky-600">Account status</p>
                    <p class="mt-1 text-sm font-extrabold text-sky-900">{{ auth()->user()->is_active ? 'Active and authorized' : 'Inactive' }}</p>
                </div>
            </div>

            <p class="mt-6 text-xs leading-6 text-slate-500">
                Role changes, account deactivation, and access restoration must be handled by an authorized administrator.
            </p>
        </aside>

        <div class="space-y-6">
            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>

            <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-soft sm:p-8">
                <div class="max-w-2xl">
                    @include('profile.partials.update-password-form')
                </div>
            </section>
        </div>
    </section>
</x-dashboard-shell>
