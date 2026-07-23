<x-dashboard-shell
    title="Edit Health Assessment"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="Restricted Information"
>
    <div
        class="rounded-2xl border border-amber-200
               bg-amber-50 px-5 py-4 text-sm
               text-amber-800"
    >
        This page contains sensitive health information about a
        minor and is restricted to authorized personnel.
    </div>

    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200
                   bg-red-50 p-5"
        >
            <ul
                class="list-inside list-disc space-y-1
                       text-sm text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section
        class="mx-auto max-w-5xl rounded-3xl
               border border-slate-200 bg-white
               p-6 shadow-sm sm:p-8"
    >
        <form
            method="POST"
            action="{{ route(
                'child-laborers.health-information.update',
                [
                    $childLaborer,
                    $healthInformation,
                ]
            ) }}"
        >
            @csrf
            @method('PUT')

            @include(
                'child-laborers.health-information.partials.form'
            )

            <div
                class="mt-8 flex justify-end gap-3
                       border-t border-slate-200 pt-6"
            >
                <a
                    href="{{ route(
                        'child-laborers.health-information.index',
                        $childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-sky-600
                           px-5 py-3 text-sm font-bold
                           text-white"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>