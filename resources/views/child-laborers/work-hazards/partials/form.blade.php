@php
    $record = $workHazard ?? null;

    $conditionFields = [
        'heavy_work' => [
            'title' => 'Heavy Work',
            'description' => 'Lifting, carrying, pushing, pulling, or physically demanding tasks.',
        ],

        'long_hours' => [
            'title' => 'Long Working Hours',
            'description' => 'Work schedules considered lengthy or excessive for the child.',
        ],

        'night_work' => [
            'title' => 'Night Work',
            'description' => 'Work performed during nighttime or very early morning hours.',
        ],

        'unsafe_conditions' => [
            'title' => 'Unsafe Conditions',
            'description' => 'Unsafe workplace, poor safeguards, dangerous surroundings, or inadequate supervision.',
        ],
    ];
@endphp

<div class="grid gap-6 md:grid-cols-2">
    {{-- Hazard type --}}
    <div>
        <label
            for="hazard_type"
            class="block text-sm font-semibold text-slate-700"
        >
            Hazard Type
            <span class="text-red-600">*</span>
        </label>

        <select
            id="hazard_type"
            name="hazard_type"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >
            <option value="">
                Select hazard type
            </option>

            @foreach ($hazardTypes as $hazardType)
                <option
                    value="{{ $hazardType }}"
                    @selected(
                        old(
                            'hazard_type',
                            $record?->hazard_type
                        ) === $hazardType
                    )
                >
                    {{ $hazardType }}
                </option>
            @endforeach
        </select>

        @error('hazard_type')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Exposure frequency --}}
    <div>
        <label
            for="exposure_frequency"
            class="block text-sm font-semibold text-slate-700"
        >
            Exposure Frequency
            <span class="text-red-600">*</span>
        </label>

        <select
            id="exposure_frequency"
            name="exposure_frequency"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >
            <option value="">
                Select exposure frequency
            </option>

            @foreach (
                $exposureFrequencies as $frequency
            )
                <option
                    value="{{ $frequency }}"
                    @selected(
                        old(
                            'exposure_frequency',
                            $record?->exposure_frequency
                        ) === $frequency
                    )
                >
                    {{ $frequency }}
                </option>
            @endforeach
        </select>

        @error('exposure_frequency')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Hazard description --}}
    <div class="md:col-span-2">
        <label
            for="hazard_description"
            class="block text-sm font-semibold text-slate-700"
        >
            Hazard Description
            <span class="text-red-600">*</span>
        </label>

        <textarea
            id="hazard_description"
            name="hazard_description"
            rows="4"
            required
            placeholder="Describe the hazard and how the child is exposed to it."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'hazard_description',
            $record?->hazard_description
        ) }}</textarea>

        @error('hazard_description')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Equipment --}}
    <div>
        <label
            for="equipment_machinery"
            class="block text-sm font-semibold text-slate-700"
        >
            Equipment or Machinery Used
        </label>

        <textarea
            id="equipment_machinery"
            name="equipment_machinery"
            rows="4"
            placeholder="Tools, blades, machines, vehicles, farm equipment, or powered equipment."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'equipment_machinery',
            $record?->equipment_machinery
        ) }}</textarea>

        @error('equipment_machinery')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Chemicals --}}
    <div>
        <label
            for="chemicals_substances"
            class="block text-sm font-semibold text-slate-700"
        >
            Chemicals or Harmful Substances
        </label>

        <textarea
            id="chemicals_substances"
            name="chemicals_substances"
            rows="4"
            placeholder="Pesticides, solvents, fumes, dust, fuel, cleaning agents, or other substances."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'chemicals_substances',
            $record?->chemicals_substances
        ) }}</textarea>

        @error('chemicals_substances')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Working-condition indicators --}}
    <div class="md:col-span-2">
        <h3 class="text-sm font-bold text-slate-800">
            Working-Condition Indicators
        </h3>

        <p class="mt-1 text-sm text-slate-500">
            Select every condition that applies to this hazard.
        </p>

        <div class="mt-4 grid gap-4 md:grid-cols-2">
            @foreach (
                $conditionFields as $field => $details
            )
                <div>
                    <input
                        type="hidden"
                        name="{{ $field }}"
                        value="0"
                    >

                    <label
                        class="flex cursor-pointer items-start
                               gap-3 rounded-2xl border
                               border-slate-200 bg-slate-50
                               p-4"
                    >
                        <input
                            type="checkbox"
                            name="{{ $field }}"
                            value="1"
                            @checked(
                                old(
                                    $field,
                                    $record?->{$field} ?? false
                                )
                            )
                            class="mt-1 rounded border-slate-300
                                   text-sky-600
                                   focus:ring-sky-500"
                        >

                        <span>
                            <span
                                class="block font-bold text-slate-800"
                            >
                                {{ $details['title'] }}
                            </span>

                            <span
                                class="mt-1 block text-sm
                                       leading-5 text-slate-500"
                            >
                                {{ $details['description'] }}
                            </span>
                        </span>
                    </label>

                    @error($field)
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            @endforeach
        </div>
    </div>

    {{-- PPE provided --}}
    <div class="md:col-span-2">
        <input
            type="hidden"
            name="ppe_provided"
            value="0"
        >

        <label
            class="flex cursor-pointer items-start
                   gap-3 rounded-2xl border
                   border-slate-200 bg-slate-50 p-4"
        >
            <input
                type="checkbox"
                name="ppe_provided"
                value="1"
                @checked(
                    old(
                        'ppe_provided',
                        $record?->ppe_provided ?? false
                    )
                )
                class="mt-1 rounded border-slate-300
                       text-sky-600 focus:ring-sky-500"
            >

            <span>
                <span class="block font-bold text-slate-800">
                    Personal Protective Equipment Provided
                </span>

                <span
                    class="mt-1 block text-sm text-slate-500"
                >
                    Check when the child receives protective
                    clothing, equipment, or safety devices.
                </span>
            </span>
        </label>

        @error('ppe_provided')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- PPE description --}}
    <div class="md:col-span-2">
        <label
            for="ppe_description"
            class="block text-sm font-semibold text-slate-700"
        >
            PPE Description
        </label>

        <textarea
            id="ppe_description"
            name="ppe_description"
            rows="3"
            placeholder="Describe the PPE provided, its condition, and whether it is regularly used."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'ppe_description',
            $record?->ppe_description
        ) }}</textarea>

        @error('ppe_description')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Injuries --}}
    <div class="md:col-span-2">
        <label
            for="injuries_incidents"
            class="block text-sm font-semibold text-slate-700"
        >
            Work-Related Injuries or Incidents
        </label>

        <textarea
            id="injuries_incidents"
            name="injuries_incidents"
            rows="4"
            placeholder="Describe previous injuries, illnesses, near misses, accidents, or harmful incidents."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'injuries_incidents',
            $record?->injuries_incidents
        ) }}</textarea>

        @error('injuries_incidents')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>