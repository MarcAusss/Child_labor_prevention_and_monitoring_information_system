<div
    id="{{ $idPrefix }}-location-root"
    {{ $attributes->merge([
        'class' => 'grid gap-5 md:grid-cols-2',
    ]) }}
>
    {{-- Region --}}
    <div>
        <label
            for="{{ $idPrefix }}-region"
            class="block text-sm font-semibold text-slate-700"
        >
            Region

            @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>

        <select
            id="{{ $idPrefix }}-region"
            name="region_id"
            @required($required)
            class="mt-2 block w-full rounded-xl border-slate-300
                   shadow-sm focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select region
            </option>

            @foreach ($regions as $region)
                <option
                    value="{{ $region->id }}"
                    @selected(
                        (int) $selectedRegionId === (int) $region->id
                    )
                >
                    {{ $region->name }}
                </option>
            @endforeach
        </select>

        @error('region_id')
            <p class="mt-2 text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Province --}}
    <div>
        <label
            for="{{ $idPrefix }}-province"
            class="block text-sm font-semibold text-slate-700"
        >
            Province

            <span class="text-xs font-normal text-slate-500">
                (when applicable)
            </span>
        </label>

        <select
            id="{{ $idPrefix }}-province"
            name="province_id"
            disabled
            class="mt-2 block w-full rounded-xl border-slate-300
                   shadow-sm focus:border-sky-500 focus:ring-sky-500
                   disabled:bg-slate-100 disabled:text-slate-500"
        >
            <option value="">
                Select region first
            </option>
        </select>

        @error('province_id')
            <p class="mt-2 text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- City or Municipality --}}
    <div>
        <label
            for="{{ $idPrefix }}-top-locality"
            class="block text-sm font-semibold text-slate-700"
        >
            City or Municipality

            @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>

        <select
            id="{{ $idPrefix }}-top-locality"
            disabled
            class="mt-2 block w-full rounded-xl border-slate-300
                   shadow-sm focus:border-sky-500 focus:ring-sky-500
                   disabled:bg-slate-100 disabled:text-slate-500"
        >
            <option value="">
                Select province first
            </option>
        </select>

        <input
            id="{{ $idPrefix }}-top-locality-input"
            type="hidden"
            name="top_locality_id"
            value="{{ $selectedTopLocalityId }}"
        >

        @error('top_locality_id')
            <p class="mt-2 text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Sub-Municipality --}}
    <div
        id="{{ $idPrefix }}-child-container"
        class="hidden"
    >
        <label
            for="{{ $idPrefix }}-child-locality"
            class="block text-sm font-semibold text-slate-700"
        >
            Sub-Municipality

            @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>

        <select
            id="{{ $idPrefix }}-child-locality"
            disabled
            class="mt-2 block w-full rounded-xl border-slate-300
                   shadow-sm focus:border-sky-500 focus:ring-sky-500
                   disabled:bg-slate-100 disabled:text-slate-500"
        >
            <option value="">
                Select sub-municipality
            </option>
        </select>
    </div>

    {{-- Final selected locality --}}
    <input
        id="{{ $idPrefix }}-locality-input"
        type="hidden"
        name="locality_id"
        value="{{ $selectedLocalityId }}"
    >

    @error('locality_id')
        <div class="md:col-span-2">
            <p class="text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        </div>
    @enderror

    {{-- Barangay --}}
    <div class="md:col-span-2">
        <label
            for="{{ $idPrefix }}-barangay"
            class="block text-sm font-semibold text-slate-700"
        >
            Barangay

            @if ($required)
                <span class="text-red-600">*</span>
            @endif
        </label>

        <select
            id="{{ $idPrefix }}-barangay"
            name="barangay_id"
            disabled
            @required($required)
            class="mt-2 block w-full rounded-xl border-slate-300
                   shadow-sm focus:border-sky-500 focus:ring-sky-500
                   disabled:bg-slate-100 disabled:text-slate-500"
        >
            <option value="">
                Select city or municipality first
            </option>
        </select>

        @error('barangay_id')
            <p class="mt-2 text-sm font-medium text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Status --}}
    <div class="md:col-span-2">
        <p
            id="{{ $idPrefix }}-status"
            class="hidden rounded-xl border px-4 py-3 text-sm"
            role="status"
            aria-live="polite"
        ></p>
    </div>
</div>

<script>
(() => {
    const rootId = @json($idPrefix.'-location-root');
    const root = document.getElementById(rootId);

    if (!root || root.dataset.initialized === 'true') {
        return;
    }

    root.dataset.initialized = 'true';

    const required = @json($required);

    const initial = {
        regionId: @json($selectedRegionId),
        provinceId: @json($selectedProvinceId),
        topLocalityId: @json($selectedTopLocalityId),
        localityId: @json($selectedLocalityId),
        barangayId: @json($selectedBarangayId),
    };

    const urls = {
        provinces: @json(
            url('/lookup/locations/regions/__ID__/provinces')
        ),

        regionalLocalities: @json(
            url('/lookup/locations/regions/__ID__/localities')
        ),

        provincialLocalities: @json(
            url('/lookup/locations/provinces/__ID__/localities')
        ),

        childLocalities: @json(
            url('/lookup/locations/localities/__ID__/children')
        ),

        barangays: @json(
            url('/lookup/locations/localities/__ID__/barangays')
        ),
    };

    const regionSelect = document.getElementById(
        @json($idPrefix.'-region')
    );

    const provinceSelect = document.getElementById(
        @json($idPrefix.'-province')
    );

    const topLocalitySelect = document.getElementById(
        @json($idPrefix.'-top-locality')
    );

    const topLocalityInput = document.getElementById(
        @json($idPrefix.'-top-locality-input')
    );

    const childContainer = document.getElementById(
        @json($idPrefix.'-child-container')
    );

    const childLocalitySelect = document.getElementById(
        @json($idPrefix.'-child-locality')
    );

    const finalLocalityInput = document.getElementById(
        @json($idPrefix.'-locality-input')
    );

    const barangaySelect = document.getElementById(
        @json($idPrefix.'-barangay')
    );

    const statusElement = document.getElementById(
        @json($idPrefix.'-status')
    );

    let regionalLocalities = [];

    const buildUrl = (template, id) => {
        return template.replace(
            '__ID__',
            encodeURIComponent(id)
        );
    };

    const showLoading = (message) => {
        statusElement.className =
            'rounded-xl border border-sky-200 bg-sky-50 ' +
            'px-4 py-3 text-sm font-medium text-sky-700';

        statusElement.textContent = message;
        statusElement.classList.remove('hidden');
    };

    const showError = (message) => {
        statusElement.className =
            'rounded-xl border border-red-200 bg-red-50 ' +
            'px-4 py-3 text-sm font-medium text-red-700';

        statusElement.textContent = message;
        statusElement.classList.remove('hidden');
    };

    const hideStatus = () => {
        statusElement.textContent = '';
        statusElement.classList.add('hidden');
    };

    const fetchData = async (url) => {
        const response = await fetch(url, {
            method: 'GET',

            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },

            credentials: 'same-origin',
        });

        if (!response.ok) {
            throw new Error(
                `Location request failed with status ${response.status}.`
            );
        }

        const result = await response.json();

        return result.data ?? [];
    };

    const resetSelect = (
        select,
        placeholder
    ) => {
        select.innerHTML = '';

        const option = document.createElement('option');

        option.value = '';
        option.textContent = placeholder;
        option.selected = true;

        select.appendChild(option);
        select.disabled = true;
    };

    const populateSelect = (
        select,
        records,
        placeholder,
        labelCallback
    ) => {
        resetSelect(select, placeholder);

        records.forEach((record) => {
            const option = document.createElement('option');

            option.value = String(record.id);
            option.textContent = labelCallback(record);

            select.appendChild(option);
        });

        select.disabled = records.length === 0;
    };

    const resetProvince = () => {
        resetSelect(
            provinceSelect,
            'Select region first'
        );
    };

    const resetTopLocality = () => {
        resetSelect(
            topLocalitySelect,
            'Select province first'
        );
    };

    const resetChildLocality = () => {
        resetSelect(
            childLocalitySelect,
            'Select sub-municipality'
        );

        childLocalitySelect.required = false;
        childContainer.classList.add('hidden');
    };

    const resetBarangay = () => {
        resetSelect(
            barangaySelect,
            'Select city or municipality first'
        );
    };

    const clearLocalityValues = () => {
        topLocalityInput.value = '';
        finalLocalityInput.value = '';
    };

    const loadBarangays = async (
        localityId,
        selectedBarangayId = null
    ) => {
        resetBarangay();

        if (!localityId) {
            return;
        }

        showLoading('Loading barangays...');

        try {
            const barangays = await fetchData(
                buildUrl(
                    urls.barangays,
                    localityId
                )
            );

            populateSelect(
                barangaySelect,
                barangays,
                'Select barangay',
                (barangay) => barangay.name
            );

            if (selectedBarangayId) {
                barangaySelect.value =
                    String(selectedBarangayId);
            }

            hideStatus();
        } catch (error) {
            console.error(error);
            showError(error.message);
        }
    };

    const loadChildLocalities = async (
        topLocalityId,
        selectedLocalityId = null,
        selectedBarangayId = null
    ) => {
        resetChildLocality();
        resetBarangay();

        topLocalityInput.value =
            topLocalityId ?? '';

        finalLocalityInput.value = '';

        if (!topLocalityId) {
            return;
        }

        showLoading(
            'Checking city or municipality structure...'
        );

        try {
            const children = await fetchData(
                buildUrl(
                    urls.childLocalities,
                    topLocalityId
                )
            );

            if (children.length > 0) {
                childContainer.classList.remove('hidden');

                populateSelect(
                    childLocalitySelect,
                    children,
                    'Select sub-municipality',
                    (locality) => locality.name
                );

                childLocalitySelect.required = required;

                if (
                    selectedLocalityId &&
                    String(selectedLocalityId) !==
                        String(topLocalityId)
                ) {
                    childLocalitySelect.value =
                        String(selectedLocalityId);

                    finalLocalityInput.value =
                        String(selectedLocalityId);

                    await loadBarangays(
                        selectedLocalityId,
                        selectedBarangayId
                    );
                } else {
                    hideStatus();
                }

                return;
            }

            finalLocalityInput.value =
                String(topLocalityId);

            await loadBarangays(
                topLocalityId,
                selectedBarangayId
            );
        } catch (error) {
            console.error(error);
            showError(error.message);
        }
    };

    const loadTopLocalities = async (
        restoreSelection = false
    ) => {
        resetTopLocality();
        resetChildLocality();
        resetBarangay();
        clearLocalityValues();

        const provinceValue = provinceSelect.value;

        if (provinceValue === '') {
            return;
        }

        showLoading(
            'Loading cities and municipalities...'
        );

        try {
            let localities = [];

            if (provinceValue === '__REGIONAL__') {
                localities = regionalLocalities;
            } else {
                localities = await fetchData(
                    buildUrl(
                        urls.provincialLocalities,
                        provinceValue
                    )
                );
            }

            populateSelect(
                topLocalitySelect,
                localities,
                'Select city or municipality',
                (locality) => {
                    const level =
                        locality.geographic_level ??
                        'Locality';

                    return `${locality.name} (${level})`;
                }
            );

            if (
                restoreSelection &&
                initial.topLocalityId
            ) {
                topLocalitySelect.value =
                    String(initial.topLocalityId);

                await loadChildLocalities(
                    initial.topLocalityId,
                    initial.localityId,
                    initial.barangayId
                );
            } else {
                hideStatus();
            }
        } catch (error) {
            console.error(error);
            showError(error.message);
        }
    };

    const loadRegion = async (
        restoreSelection = false
    ) => {
        const regionId = regionSelect.value;

        resetProvince();
        resetTopLocality();
        resetChildLocality();
        resetBarangay();
        clearLocalityValues();

        regionalLocalities = [];

        if (!regionId) {
            hideStatus();
            return;
        }

        showLoading(
            'Loading provinces and regional localities...'
        );

        try {
            const results = await Promise.all([
                fetchData(
                    buildUrl(
                        urls.provinces,
                        regionId
                    )
                ),

                fetchData(
                    buildUrl(
                        urls.regionalLocalities,
                        regionId
                    )
                ),
            ]);

            const provinces = results[0];
            regionalLocalities = results[1];

            provinceSelect.innerHTML = '';

            const placeholder =
                document.createElement('option');

            placeholder.value = '';
            placeholder.textContent = 'Select province';
            placeholder.selected = true;

            provinceSelect.appendChild(placeholder);

            if (regionalLocalities.length > 0) {
                const regionalOption =
                    document.createElement('option');

                regionalOption.value = '__REGIONAL__';

                regionalOption.textContent =
                    'No province / region-level locality';

                provinceSelect.appendChild(
                    regionalOption
                );
            }

            provinces.forEach((province) => {
                const option =
                    document.createElement('option');

                option.value = String(province.id);
                option.textContent = province.name;

                provinceSelect.appendChild(option);
            });

            provinceSelect.disabled =
                provinces.length === 0 &&
                regionalLocalities.length === 0;

            if (restoreSelection) {
                if (initial.provinceId) {
                    provinceSelect.value =
                        String(initial.provinceId);
                } else if (
                    initial.topLocalityId &&
                    regionalLocalities.some(
                        (locality) =>
                            String(locality.id) ===
                            String(
                                initial.topLocalityId
                            )
                    )
                ) {
                    provinceSelect.value =
                        '__REGIONAL__';
                }
            }

            hideStatus();

            if (provinceSelect.value !== '') {
                await loadTopLocalities(
                    restoreSelection
                );
            }
        } catch (error) {
            console.error(error);
            showError(error.message);
        }
    };

    regionSelect.addEventListener(
        'change',
        () => loadRegion(false)
    );

    provinceSelect.addEventListener(
        'change',
        () => loadTopLocalities(false)
    );

    topLocalitySelect.addEventListener(
        'change',
        () => {
            loadChildLocalities(
                topLocalitySelect.value
            );
        }
    );

    childLocalitySelect.addEventListener(
        'change',
        () => {
            const localityId =
                childLocalitySelect.value;

            finalLocalityInput.value =
                localityId ?? '';

            loadBarangays(localityId);
        }
    );

    resetProvince();
    resetTopLocality();
    resetChildLocality();
    resetBarangay();

    if (initial.regionId) {
        regionSelect.value =
            String(initial.regionId);

        loadRegion(true);
    }
})();
</script>