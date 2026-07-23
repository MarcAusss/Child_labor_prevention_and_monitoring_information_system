@php
    $record = $employmentRecord ?? null;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label
            for="employer_name"
            class="block text-sm font-semibold text-slate-700"
        >
            Employer or Business Name
        </label>

        <input
            id="employer_name"
            name="employer_name"
            type="text"
            value="{{ old(
                'employer_name',
                $record?->employer_name
            ) }}"
            placeholder="Employer, establishment, farm, or household"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('employer_name')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="occupation"
            class="block text-sm font-semibold text-slate-700"
        >
            Occupation or Work Performed
            <span class="text-red-600">*</span>
        </label>

        <input
            id="occupation"
            name="occupation"
            type="text"
            value="{{ old(
                'occupation',
                $record?->occupation
            ) }}"
            placeholder="Example: Farm worker, vendor, helper"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('occupation')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label
            for="employer_address"
            class="block text-sm font-semibold text-slate-700"
        >
            Employer or Workplace Address
        </label>

        <input
            id="employer_address"
            name="employer_address"
            type="text"
            value="{{ old(
                'employer_address',
                $record?->employer_address
            ) }}"
            placeholder="Complete location of the workplace"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('employer_address')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="work_type"
            class="block text-sm font-semibold text-slate-700"
        >
            Work Type
            <span class="text-red-600">*</span>
        </label>

        <select
            id="work_type"
            name="work_type"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select work type
            </option>

            @foreach ($workTypes as $workType)
                <option
                    value="{{ $workType }}"
                    @selected(
                        old(
                            'work_type',
                            $record?->work_type
                        ) === $workType
                    )
                >
                    {{ $workType }}
                </option>
            @endforeach
        </select>

        @error('work_type')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="employment_arrangement"
            class="block text-sm font-semibold text-slate-700"
        >
            Employment Arrangement
            <span class="text-red-600">*</span>
        </label>

        <select
            id="employment_arrangement"
            name="employment_arrangement"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select arrangement
            </option>

            @foreach (
                $employmentArrangements as $arrangement
            )
                <option
                    value="{{ $arrangement }}"
                    @selected(
                        old(
                            'employment_arrangement',
                            $record?->employment_arrangement
                        ) === $arrangement
                    )
                >
                    {{ $arrangement }}
                </option>
            @endforeach
        </select>

        @error('employment_arrangement')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div class="md:col-span-2">
        <label
            for="industry"
            class="block text-sm font-semibold text-slate-700"
        >
            Industry or Sector
        </label>

        <input
            id="industry"
            name="industry"
            type="text"
            value="{{ old(
                'industry',
                $record?->industry
            ) }}"
            placeholder="Agriculture, fishing, construction, retail, domestic work"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('industry')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="start_date"
            class="block text-sm font-semibold text-slate-700"
        >
            Employment Start Date
            <span class="text-red-600">*</span>
        </label>

        <input
            id="start_date"
            name="start_date"
            type="date"
            value="{{ old(
                'start_date',
                $record?->start_date?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('start_date')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="end_date"
            class="block text-sm font-semibold text-slate-700"
        >
            Employment End Date
        </label>

        <input
            id="end_date"
            name="end_date"
            type="date"
            value="{{ old(
                'end_date',
                $record?->end_date?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        <p class="mt-2 text-xs text-slate-500">
            Leave blank when this is the current employment.
        </p>

        @error('end_date')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="days_per_week"
            class="block text-sm font-semibold text-slate-700"
        >
            Workdays Per Week
            <span class="text-red-600">*</span>
        </label>

        <input
            id="days_per_week"
            name="days_per_week"
            type="number"
            min="1"
            max="7"
            value="{{ old(
                'days_per_week',
                $record?->days_per_week
            ) }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('days_per_week')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="hours_per_day"
            class="block text-sm font-semibold text-slate-700"
        >
            Working Hours Per Day
            <span class="text-red-600">*</span>
        </label>

        <input
            id="hours_per_day"
            name="hours_per_day"
            type="number"
            min="0.25"
            max="24"
            step="0.25"
            value="{{ old(
                'hours_per_day',
                $record?->hours_per_day
            ) }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('hours_per_day')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="income_amount"
            class="block text-sm font-semibold text-slate-700"
        >
            Income Amount
        </label>

        <input
            id="income_amount"
            name="income_amount"
            type="number"
            min="0"
            step="0.01"
            value="{{ old(
                'income_amount',
                $record?->income_amount
            ) }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('income_amount')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="income_frequency"
            class="block text-sm font-semibold text-slate-700"
        >
            Income Frequency
            <span class="text-red-600">*</span>
        </label>

        <select
            id="income_frequency"
            name="income_frequency"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select payment frequency
            </option>

            @foreach (
                $incomeFrequencies as $frequency
            )
                <option
                    value="{{ $frequency }}"
                    @selected(
                        old(
                            'income_frequency',
                            $record?->income_frequency
                        ) === $frequency
                    )
                >
                    {{ $frequency }}
                </option>
            @endforeach
        </select>

        @error('income_frequency')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

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
            rows="3"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
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

    <div class="md:col-span-2">
        <input
            type="hidden"
            name="is_current"
            value="0"
        >

        <label
            class="flex cursor-pointer items-start gap-3
                   rounded-2xl border border-slate-200
                   bg-slate-50 p-4"
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
                    Current Employment
                </span>

                <span class="mt-1 block text-sm text-slate-500">
                    Mark this as the child’s present work.
                    Only one record can be current.
                </span>
            </span>
        </label>
    </div>
</div>