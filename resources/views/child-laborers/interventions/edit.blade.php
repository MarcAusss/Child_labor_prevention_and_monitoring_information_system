<x-dashboard-shell
    title="Edit Intervention"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $intervention->status }}"
>
    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200
                   bg-red-50 p-5"
        >
            <h2 class="font-bold text-red-800">
                Please correct the following:
            </h2>

            <ul
                class="mt-3 list-inside list-disc
                       space-y-1 text-sm text-red-700"
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
        <div class="mb-6 border-b border-slate-200 pb-5">
            <h2 class="text-xl font-bold text-slate-800">
                Intervention Details
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Update the assistance details, progress, completion
                status, and recorded value.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route(
                'child-laborers.interventions.update',
                [
                    $childLaborer,
                    $intervention,
                ]
            ) }}"
        >
            @csrf
            @method('PUT')

            @include(
                'child-laborers.interventions.partials.form'
            )

            <div
                class="mt-8 flex justify-end gap-3
                       border-t border-slate-200 pt-6"
            >
                <a
                    href="{{ route(
                        'child-laborers.interventions.index',
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
                           text-white hover:bg-sky-700"
                >
                    Save Changes
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>