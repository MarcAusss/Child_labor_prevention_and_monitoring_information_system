@php
    $record = $healthInformation ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    {{-- Assessment date --}}
    <div>
        <label
            for="assessment_date"
            class="block text-sm font-semibold text-slate-700"
        >
            Assessment Date
            <span class="text-red-600">*</span>
        </label>

        <input
            id="assessment_date"
            name="assessment_date"
            type="date"
            value="{{ old(
                'assessment_date',
                $record?->assessment_date?->format('Y-m-d')
                    ?? now()->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            required
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        @error('assessment_date')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Current assessment --}}
    <div>
        <input
            type="hidden"
            name="is_current"
            value="0"
        >

        <label
            class="flex h-full cursor-pointer items-start
                   gap-3 rounded-2xl border
                   border-slate-200 bg-slate-50 p-4"
        >
            <input
                name="is_current"
                type="checkbox"
                value="1"
                @checked(
                    old(
                        'is_current',
                        $record?->is_current ?? false
                    )
                )
                class="mt-1 rounded border-slate-300
                       text-sky-600 focus:ring-sky-500"
            >

            <span>
                <span class="block font-bold text-slate-800">
                    Current Health Assessment
                </span>

                <span class="mt-1 block text-sm text-slate-500">
                    Mark this as the latest health record.
                    Only one assessment can be current.
                </span>
            </span>
        </label>
    </div>

    {{-- Health condition --}}
    <div class="md:col-span-2">
        <label
            for="health_condition"
            class="block text-sm font-semibold text-slate-700"
        >
            Reported Health Condition
        </label>

        <textarea
            id="health_condition"
            name="health_condition"
            rows="4"
            placeholder="Describe known or reported medical conditions. Enter 'No known condition' when applicable."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'health_condition',
            $record?->health_condition
        ) }}</textarea>

        @error('health_condition')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Disability --}}
    <div class="md:col-span-2">
        <input
            type="hidden"
            name="has_disability"
            value="0"
        >

        <label
            class="flex cursor-pointer items-start
                   gap-3 rounded-2xl border
                   border-slate-200 bg-slate-50 p-4"
        >
            <input
                name="has_disability"
                type="checkbox"
                value="1"
                @checked(
                    old(
                        'has_disability',
                        $record?->has_disability ?? false
                    )
                )
                class="mt-1 rounded border-slate-300
                       text-sky-600 focus:ring-sky-500"
            >

            <span>
                <span class="block font-bold text-slate-800">
                    Disability Reported
                </span>

                <span class="mt-1 block text-sm text-slate-500">
                    Check when a disability has been reported
                    or documented.
                </span>
            </span>
        </label>

        @error('has_disability')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Disability details --}}
    <div class="md:col-span-2">
        <label
            for="disability_details"
            class="block text-sm font-semibold text-slate-700"
        >
            Disability Details
        </label>

        <textarea
            id="disability_details"
            name="disability_details"
            rows="3"
            placeholder="Describe the reported disability, limitations, accommodations, or assistive devices."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'disability_details',
            $record?->disability_details
        ) }}</textarea>

        @error('disability_details')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Injury history --}}
    <div>
        <label
            for="injury_history"
            class="block text-sm font-semibold text-slate-700"
        >
            Injury History
        </label>

        <textarea
            id="injury_history"
            name="injury_history"
            rows="4"
            placeholder="Previous injuries, accidents, dates, and related circumstances."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'injury_history',
            $record?->injury_history
        ) }}</textarea>

        @error('injury_history')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Current complaints --}}
    <div>
        <label
            for="current_complaints"
            class="block text-sm font-semibold text-slate-700"
        >
            Current Health Complaints
        </label>

        <textarea
            id="current_complaints"
            name="current_complaints"
            rows="4"
            placeholder="Pain, fatigue, breathing problems, dizziness, skin irritation, or other complaints."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'current_complaints',
            $record?->current_complaints
        ) }}</textarea>

        @error('current_complaints')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Treatment --}}
    <div>
        <label
            for="treatment_received"
            class="block text-sm font-semibold text-slate-700"
        >
            Treatment Received
        </label>

        <textarea
            id="treatment_received"
            name="treatment_received"
            rows="4"
            placeholder="Medication, consultation, hospitalization, therapy, first aid, or other treatment."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'treatment_received',
            $record?->treatment_received
        ) }}</textarea>

        @error('treatment_received')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Health facility --}}
    <div>
        <label
            for="health_facility"
            class="block text-sm font-semibold text-slate-700"
        >
            Health Facility or Provider
        </label>

        <input
            id="health_facility"
            name="health_facility"
            type="text"
            value="{{ old(
                'health_facility',
                $record?->health_facility
            ) }}"
            placeholder="Hospital, health center, clinic, doctor, or provider"
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >

        @error('health_facility')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Mental health --}}
    <div class="md:col-span-2">
        <label
            for="mental_health_concerns"
            class="block text-sm font-semibold text-slate-700"
        >
            Mental-Health or Psychosocial Concerns
        </label>

        <textarea
            id="mental_health_concerns"
            name="mental_health_concerns"
            rows="4"
            placeholder="Reported stress, anxiety, fear, trauma, emotional concerns, sleep issues, or behavioral concerns."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'mental_health_concerns',
            $record?->mental_health_concerns
        ) }}</textarea>

        @error('mental_health_concerns')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Remarks --}}
    <div class="md:col-span-2">
        <label
            for="remarks"
            class="block text-sm font-semibold text-slate-700"
        >
            Remarks
        </label>

        <textarea
            id="remarks"
            name="remarks"
            rows="4"
            placeholder="Additional observations, referrals, follow-up needs, or relevant information."
            class="mt-2 block w-full rounded-xl
                   border-slate-300 focus:border-sky-500
                   focus:ring-sky-500"
        >{{ old(
            'remarks',
            $record?->remarks
        ) }}</textarea>

        @error('remarks')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>