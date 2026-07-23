@php
    $record = $educationRecord ?? null;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    {{-- Enrollment status --}}
    <div>
        <label
            for="enrollment_status"
            class="block text-sm font-semibold text-slate-700"
        >
            Enrollment Status
            <span class="text-red-600">*</span>
        </label>

        <select
            id="enrollment_status"
            name="enrollment_status"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select enrollment status
            </option>

            @foreach ($enrollmentStatuses as $status)
                <option
                    value="{{ $status }}"
                    @selected(
                        old(
                            'enrollment_status',
                            $record?->enrollment_status
                        ) === $status
                    )
                >
                    {{ $status }}
                </option>
            @endforeach
        </select>

        @error('enrollment_status')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- School year --}}
    <div>
        <label
            for="school_year"
            class="block text-sm font-semibold text-slate-700"
        >
            School Year
        </label>

        <input
            id="school_year"
            name="school_year"
            type="text"
            value="{{ old(
                'school_year',
                $record?->school_year
            ) }}"
            placeholder="2025-2026"
            maxlength="20"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('school_year')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- School name --}}
    <div class="md:col-span-2">
        <label
            for="school_name"
            class="block text-sm font-semibold text-slate-700"
        >
            School Name
        </label>

        <input
            id="school_name"
            name="school_name"
            type="text"
            value="{{ old(
                'school_name',
                $record?->school_name
            ) }}"
            placeholder="Name of school or learning center"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('school_name')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Grade or year level --}}
    <div>
        <label
            for="grade_year_level"
            class="block text-sm font-semibold text-slate-700"
        >
            Grade or Year Level
        </label>

        <select
            id="grade_year_level"
            name="grade_year_level"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select grade or year level
            </option>

            @foreach ($gradeYearLevels as $level)
                <option
                    value="{{ $level }}"
                    @selected(
                        old(
                            'grade_year_level',
                            $record?->grade_year_level
                        ) === $level
                    )
                >
                    {{ $level }}
                </option>
            @endforeach
        </select>

        @error('grade_year_level')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Last grade completed --}}
    <div>
        <label
            for="last_grade_completed"
            class="block text-sm font-semibold text-slate-700"
        >
            Last Grade Completed
        </label>

        <input
            id="last_grade_completed"
            name="last_grade_completed"
            type="text"
            value="{{ old(
                'last_grade_completed',
                $record?->last_grade_completed
            ) }}"
            placeholder="Example: Grade 6"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('last_grade_completed')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- School address --}}
    <div class="md:col-span-2">
        <label
            for="school_address"
            class="block text-sm font-semibold text-slate-700"
        >
            School Address
        </label>

        <input
            id="school_address"
            name="school_address"
            type="text"
            value="{{ old(
                'school_address',
                $record?->school_address
            ) }}"
            placeholder="Barangay, City or Municipality, Province"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('school_address')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Date enrolled --}}
    <div>
        <label
            for="date_enrolled"
            class="block text-sm font-semibold text-slate-700"
        >
            Date Enrolled
        </label>

        <input
            id="date_enrolled"
            name="date_enrolled"
            type="date"
            value="{{ old(
                'date_enrolled',
                $record?->date_enrolled?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('date_enrolled')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Date ended --}}
    <div>
        <label
            for="date_ended"
            class="block text-sm font-semibold text-slate-700"
        >
            Date Ended
        </label>

        <input
            id="date_ended"
            name="date_ended"
            type="date"
            value="{{ old(
                'date_ended',
                $record?->date_ended?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('date_ended')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Reason not attending --}}
    <div class="md:col-span-2">
        <label
            for="reason_not_attending"
            class="block text-sm font-semibold text-slate-700"
        >
            Reason for Not Attending School
        </label>

        <textarea
            id="reason_not_attending"
            name="reason_not_attending"
            rows="3"
            placeholder="Required when the child is not enrolled or has dropped out"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >{{ old(
            'reason_not_attending',
            $record?->reason_not_attending
        ) }}</textarea>

        @error('reason_not_attending')
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

    {{-- Current record --}}
    <div class="md:col-span-2">
        <input
            type="hidden"
            name="is_current"
            value="0"
        >

        <label
            class="flex cursor-pointer items-start gap-3 rounded-2xl
                   border border-slate-200 bg-slate-50 p-4"
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
                class="mt-1 rounded border-slate-300 text-sky-600
                       focus:ring-sky-500"
            >

            <span>
                <span class="block font-bold text-slate-800">
                    Current Education Record
                </span>

                <span class="mt-1 block text-sm text-slate-500">
                    Mark this as the child’s latest education
                    situation. Only one record can be current.
                </span>
            </span>
        </label>

        @error('is_current')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>