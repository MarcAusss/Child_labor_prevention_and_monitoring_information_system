<x-dashboard-shell
    title="Residential Address"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $childLaborer->status }}"
>
    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-5">
            <ul class="list-inside list-disc space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section
        class="mx-auto max-w-5xl rounded-3xl border border-slate-200 bg-white p-8 shadow-sm"
    >
        <div class="mb-7 border-b border-slate-200 pb-5">
            <h2 class="text-xl font-bold text-slate-800">
                Current Residential Address
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Enter the current address where the child normally resides.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route(
                'child-laborers.residential-address.update',
                $childLaborer
            ) }}"
        >
            @csrf
            @method('PUT')

            <div class="mb-7 grid gap-5 md:grid-cols-2">
                <div>
                    <label
                        for="house_number"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        House or Building Number
                    </label>

                    <input
                        id="house_number"
                        name="house_number"
                        type="text"
                        value="{{ old(
                            'house_number',
                            $residentialAddress?->house_number
                        ) }}"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >

                    @error('house_number')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="street"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Street
                    </label>

                    <input
                        id="street"
                        name="street"
                        type="text"
                        value="{{ old(
                            'street',
                            $residentialAddress?->street
                        ) }}"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >

                    @error('street')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="sitio_purok"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Sitio or Purok
                    </label>

                    <input
                        id="sitio_purok"
                        name="sitio_purok"
                        type="text"
                        value="{{ old(
                            'sitio_purok',
                            $residentialAddress?->sitio_purok
                        ) }}"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >

                    @error('sitio_purok')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label
                        for="postal_code"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Postal Code
                    </label>

                    <input
                        id="postal_code"
                        name="postal_code"
                        type="text"
                        value="{{ old(
                            'postal_code',
                            $residentialAddress?->postal_code
                        ) }}"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >

                    @error('postal_code')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="md:col-span-2">
                    <label
                        for="landmark"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Nearby Landmark
                    </label>

                    <input
                        id="landmark"
                        name="landmark"
                        type="text"
                        value="{{ old(
                            'landmark',
                            $residentialAddress?->landmark
                        ) }}"
                        class="mt-2 block w-full rounded-xl border-slate-300"
                    >

                    @error('landmark')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <x-location-selects
                id-prefix="residential-location"

                :selected-region-id="old(
                    'region_id',
                    $residentialAddress?->region_id
                )"

                :selected-province-id="old(
                    'province_id',
                    $residentialAddress?->province_id
                )"

                :selected-top-locality-id="old(
                    'top_locality_id',
                    $residentialAddress?->locality?->parent_id
                        ?: $residentialAddress?->locality_id
                )"

                :selected-locality-id="old(
                    'locality_id',
                    $residentialAddress?->locality_id
                )"

                :selected-barangay-id="old(
                    'barangay_id',
                    $residentialAddress?->barangay_id
                )"
            />

            <div
                class="mt-8 flex justify-end gap-3 border-t border-slate-200 pt-6"
            >
                <a
                    href="{{ route(
                        'child-laborers.show',
                        $childLaborer
                    ) }}"
                    class="rounded-xl border border-slate-300 px-5 py-3 text-sm font-bold text-slate-600"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-sky-600 px-5 py-3 text-sm font-bold text-white"
                >
                    Save Residential Address
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>