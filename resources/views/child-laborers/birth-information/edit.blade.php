<x-dashboard-shell
    title="Birth Information"
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
                Place of Birth
            </h2>

            <p class="mt-2 text-sm text-slate-500">
                Select the official PSGC location where the child was born.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route(
                'child-laborers.birth-information.update',
                $childLaborer
            ) }}"
        >
            @csrf
            @method('PUT')

            <x-location-selects
                id-prefix="birth-location"

                :selected-region-id="old(
                    'region_id',
                    $birthInformation?->region_id
                )"

                :selected-province-id="old(
                    'province_id',
                    $birthInformation?->province_id
                )"

                :selected-top-locality-id="old(
                    'top_locality_id',
                    $birthInformation?->locality?->parent_id
                        ?: $birthInformation?->locality_id
                )"

                :selected-locality-id="old(
                    'locality_id',
                    $birthInformation?->locality_id
                )"

                :selected-barangay-id="old(
                    'barangay_id',
                    $birthInformation?->barangay_id
                )"
            />

            <div class="mt-6">
                <label
                    for="place_of_birth"
                    class="block text-sm font-semibold text-slate-700"
                >
                    Specific Place of Birth
                </label>

                <input
                    id="place_of_birth"
                    name="place_of_birth"
                    type="text"
                    value="{{ old(
                        'place_of_birth',
                        $birthInformation?->place_of_birth
                    ) }}"
                    placeholder="Hospital, clinic, residence, or facility"
                    class="mt-2 block w-full rounded-xl border-slate-300"
                >

                @error('place_of_birth')
                    <p class="mt-2 text-sm text-red-600">
                        {{ $message }}
                    </p>
                @enderror
            </div>

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
                    Save Birth Information
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>