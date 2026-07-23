@php
    $record = $householdMember ?? null;
@endphp

<div class="grid gap-5 md:grid-cols-2">
    <div>
        <label
            for="full_name"
            class="block text-sm font-semibold text-slate-700"
        >
            Full Name
            <span class="text-red-600">*</span>
        </label>

        <input
            id="full_name"
            name="full_name"
            type="text"
            value="{{ old(
                'full_name',
                $record?->full_name
            ) }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('full_name')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="relationship"
            class="block text-sm font-semibold text-slate-700"
        >
            Relationship to Child
            <span class="text-red-600">*</span>
        </label>

        <input
            id="relationship"
            name="relationship"
            type="text"
            value="{{ old(
                'relationship',
                $record?->relationship
            ) }}"
            placeholder="Brother, sister, grandmother, cousin"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('relationship')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="sex"
            class="block text-sm font-semibold text-slate-700"
        >
            Sex
            <span class="text-red-600">*</span>
        </label>

        <select
            id="sex"
            name="sex"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select sex
            </option>

            <option
                value="Male"
                @selected(
                    old(
                        'sex',
                        $record?->sex
                    ) === 'Male'
                )
            >
                Male
            </option>

            <option
                value="Female"
                @selected(
                    old(
                        'sex',
                        $record?->sex
                    ) === 'Female'
                )
            >
                Female
            </option>
        </select>

        @error('sex')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="birth_date"
            class="block text-sm font-semibold text-slate-700"
        >
            Birth Date
        </label>

        <input
            id="birth_date"
            name="birth_date"
            type="date"
            value="{{ old(
                'birth_date',
                $record?->birth_date?->format('Y-m-d')
            ) }}"
            max="{{ now()->format('Y-m-d') }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('birth_date')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="civil_status"
            class="block text-sm font-semibold text-slate-700"
        >
            Civil Status
        </label>

        <select
            id="civil_status"
            name="civil_status"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select civil status
            </option>

            @foreach (
                [
                    'Single',
                    'Married',
                    'Widowed',
                    'Separated',
                ] as $civilStatus
            )
                <option
                    value="{{ $civilStatus }}"
                    @selected(
                        old(
                            'civil_status',
                            $record?->civil_status
                        ) === $civilStatus
                    )
                >
                    {{ $civilStatus }}
                </option>
            @endforeach
        </select>

        @error('civil_status')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="educational_attainment"
            class="block text-sm font-semibold text-slate-700"
        >
            Educational Attainment
        </label>

        <input
            id="educational_attainment"
            name="educational_attainment"
            type="text"
            value="{{ old(
                'educational_attainment',
                $record?->educational_attainment
            ) }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('educational_attainment')
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
            Occupation
        </label>

        <input
            id="occupation"
            name="occupation"
            type="text"
            value="{{ old(
                'occupation',
                $record?->occupation
            ) }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('occupation')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="monthly_income"
            class="block text-sm font-semibold text-slate-700"
        >
            Estimated Monthly Income
        </label>

        <input
            id="monthly_income"
            name="monthly_income"
            type="number"
            step="0.01"
            min="0"
            value="{{ old(
                'monthly_income',
                $record?->monthly_income
            ) }}"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('monthly_income')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>