<x-dashboard-shell
    title="PSGC Location Test"
    subtitle="Test the automated Philippine location dropdowns."
    badge="Phase 2B"
>
    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 px-5 py-4 text-sm
                   font-semibold text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200
                   bg-red-50 px-5 py-4"
        >
            <p class="font-bold text-red-800">
                Please correct the following:
            </p>

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
        class="mx-auto max-w-4xl rounded-3xl
               border border-slate-200 bg-white
               p-6 shadow-sm sm:p-8"
    >
        <div class="mb-7 border-b border-slate-200 pb-5">
            <h2 class="text-xl font-bold text-slate-800">
                Select a Philippine Location
            </h2>

            <p class="mt-2 text-sm leading-6 text-slate-500">
                The options below are loaded from the imported
                Philippine Standard Geographic Code records.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route(
                'admin.location-test.store'
            ) }}"
        >
            @csrf

            <x-location-selects
                :selected-region-id="old('region_id')"
                :selected-province-id="old('province_id')"
                :selected-top-locality-id="old(
                    'top_locality_id'
                )"
                :selected-locality-id="old(
                    'locality_id'
                )"
                :selected-barangay-id="old(
                    'barangay_id'
                )"
            />

            <div
                class="mt-8 flex justify-end
                       border-t border-slate-200 pt-6"
            >
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600 px-6 py-3
                           text-sm font-bold text-white
                           hover:bg-sky-700"
                >
                    Validate Location
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>