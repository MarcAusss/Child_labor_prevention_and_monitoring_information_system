<x-dashboard-shell
    title="Create User"
    subtitle="Create a new authorized CLPMIS account."
    badge="User Management"
>
    <section
        class="mx-auto max-w-3xl rounded-3xl border border-slate-200 bg-white p-6 shadow-sm sm:p-8"
    >
        <form
            method="POST"
            action="{{ route('admin.users.store') }}"
        >
            @csrf

            @include('admin.users.partials.form', [
                'managedUser' => null,
            ])

            <div
                class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end"
            >
                <a
                    href="{{ route('admin.users.index') }}"
                    class="rounded-xl border border-slate-300 px-5 py-3 text-center text-sm font-bold text-slate-600 hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-sky-600 px-5 py-3 text-sm font-bold text-white hover:bg-sky-700"
                >
                    Create Account
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>