<x-app-layout>
    <x-slot name="header">
        <div>
            <h2 class="text-xl font-bold leading-tight text-slate-800">
                Account Profile
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Update your account information and password.
            </p>
        </div>
    </x-slot>

    <div class="min-h-[calc(100vh-130px)] bg-slate-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            <section
                class="relative overflow-hidden rounded-3xl bg-gradient-to-br
                       from-sky-700 via-sky-600 to-cyan-500 p-8 text-white
                       shadow-xl shadow-sky-200/60">

                <div class="relative z-10">
                    <p class="text-sm font-bold uppercase tracking-[0.2em] text-sky-100">
                        CLPMIS Account
                    </p>

                    <h1 class="mt-3 text-3xl font-black">
                        {{ auth()->user()->name }}
                    </h1>

                    <p class="mt-3 text-sm text-sky-50">
                        {{ auth()->user()->role?->name ?? 'No assigned role' }}
                    </p>
                </div>

                <div
                    class="absolute -right-20 -top-20 h-64 w-64
                           rounded-full bg-white/10">
                </div>
            </section>

            <div class="grid gap-6 xl:grid-cols-2">
                <section
                    class="rounded-3xl border border-slate-200
                           bg-white p-6 shadow-sm sm:p-8">

                    <div class="max-w-xl">
                        @include('profile.partials.update-profile-information-form')
                    </div>
                </section>

                <section
                    class="rounded-3xl border border-slate-200
                           bg-white p-6 shadow-sm sm:p-8">

                    <div class="max-w-xl">
                        @include('profile.partials.update-password-form')
                    </div>
                </section>
            </div>

            <section
                class="rounded-3xl border border-sky-200
                       bg-sky-50 p-6 sm:p-8">

                <h2 class="text-lg font-bold text-sky-900">
                    Account Management
                </h2>

                <p class="mt-2 text-sm leading-6 text-sky-800">
                    CLPMIS accounts cannot be deleted through the personal
                    profile page. Contact an authorized administrator when an
                    account needs to be deactivated or its role needs to be
                    changed.
                </p>
            </section>
        </div>
    </div>
</x-app-layout>