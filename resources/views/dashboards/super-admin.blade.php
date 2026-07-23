<x-dashboard-shell
    title="Super Admin Dashboard"
    subtitle="Complete administration and system monitoring."
    badge="Super Admin"
>
    <section
        class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-700 via-sky-600 to-cyan-500 p-8 text-white shadow-xl shadow-sky-200/60">
        <div class="relative z-10 max-w-3xl">
            <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-100">
                CLPMIS
            </p>

            <h1 class="mt-3 text-3xl font-black sm:text-4xl">
                Child Labor Prevention and Monitoring
            </h1>

            <p class="mt-4 max-w-2xl text-sm leading-7 text-sky-50 sm:text-base">
                Manage user accounts, system access, child laborer records,
                interventions, audits, reports, and system configuration.
            </p>
        </div>

        <div
            class="absolute -right-20 -top-20 h-64 w-64 rounded-full bg-white/10">
        </div>

        <div
            class="absolute -bottom-24 right-24 h-60 w-60 rounded-full bg-cyan-300/20">
        </div>
    </section>

    <section class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Total Users
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
                Inactive Users
            </p>

            <p class="mt-3 text-3xl font-black text-red-600">
                {{ number_format($inactiveUsers) }}
            </p>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm font-semibold text-slate-500">
                Active Roles
            </p>

            <p class="mt-3 text-3xl font-black text-sky-600">
                {{ number_format($totalRoles) }}
            </p>
        </div>
    </section>

    <section class="overflow-hidden rounded-3xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 px-6 py-5">
            <h2 class="text-lg font-bold text-slate-800">
                Recently Added Users
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-sky-800">
                            Name
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-sky-800">
                            Email
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase tracking-wider text-sky-800">
                            Role
                        </th>

                        <th class="px-6 py-4 text-center text-xs font-bold uppercase tracking-wider text-sky-800">
                            Status
                        </th>
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100 bg-white">
                    @forelse ($recentUsers as $recentUser)
                        <tr>
                            <td class="px-6 py-4 text-sm font-semibold text-slate-800">
                                {{ $recentUser->name }}
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $recentUser->email }}
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-600">
                                {{ $recentUser->role?->name ?? 'No Role' }}
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($recentUser->is_active)
                                    <span
                                        class="inline-flex rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700 ring-1 ring-emerald-200">
                                        Active
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full bg-red-100 px-3 py-1 text-xs font-bold text-red-700 ring-1 ring-red-200">
                                        Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="4"
                                class="px-6 py-10 text-center text-sm text-slate-500">
                                No users found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-shell>