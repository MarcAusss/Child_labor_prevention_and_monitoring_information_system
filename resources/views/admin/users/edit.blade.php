<x-dashboard-shell
    title="Edit User"
    subtitle="Update account information, role, status, or password."
    badge="User Management"
>
    @if (session('success'))
        <div
            class="mx-auto max-w-3xl rounded-xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    <div class="mx-auto max-w-3xl space-y-6">
        <section
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Account Information
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $managedUser->email }}
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.users.update', $managedUser) }}"
            >
                @csrf
                @method('PATCH')

                @include('admin.users.partials.form', [
                    'managedUser' => $managedUser,
                ])

                <div
                    class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end"
                >
                    <a
                        href="{{ route('admin.users.index') }}"
                        class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-50"
                    >
                        Back
                    </a>

                    <button
                        type="submit"
                        class="rounded-xl bg-sky-600 px-5 py-3 text-sm font-bold text-white hover:bg-sky-700"
                    >
                        Save Changes
                    </button>
                </div>
            </form>
        </section>

        <section
            class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Reset Password
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Assign a new password to this account.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route('admin.users.reset-password', $managedUser) }}"
                class="space-y-6"
            >
                @csrf
                @method('PATCH')

                <div>
                    <label
                        for="reset_password"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        New Password
                    </label>

                    <input
                        id="reset_password"
                        type="password"
                        name="password"
                        required
                        autocomplete="new-password"
                        class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                    >

                    <p class="mt-2 text-xs leading-5 text-slate-500">
                        Use at least 12 characters with uppercase and
                        lowercase letters, a number, and a symbol.
                    </p>

                    @error('password')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="reset_password_confirmation"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Confirm New Password
                    </label>

                    <input
                        id="reset_password_confirmation"
                        type="password"
                        name="password_confirmation"
                        required
                        autocomplete="new-password"
                        class="mt-2 block w-full rounded-xl border-slate-300 shadow-sm focus:border-sky-500 focus:ring-sky-500"
                    >
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        class="rounded-xl bg-slate-800 px-5 py-3 text-sm font-bold text-white hover:bg-slate-900"
                    >
                        Reset Password
                    </button>
                </div>
            </form>
        </section>
    </div>
</x-dashboard-shell>