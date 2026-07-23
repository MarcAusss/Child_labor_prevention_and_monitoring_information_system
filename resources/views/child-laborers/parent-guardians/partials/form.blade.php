@php
    $record = $parentGuardian ?? null;
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

        <select
            id="relationship"
            name="relationship"
            required
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select relationship
            </option>

            @foreach (
                [
                    'Mother',
                    'Father',
                    'Stepmother',
                    'Stepfather',
                    'Grandmother',
                    'Grandfather',
                    'Aunt',
                    'Uncle',
                    'Sibling',
                    'Legal Guardian',
                    'Other',
                ] as $relationship
            )
                <option
                    value="{{ $relationship }}"
                    @selected(
                        old(
                            'relationship',
                            $record?->relationship
                        ) === $relationship
                    )
                >
                    {{ $relationship }}
                </option>
            @endforeach
        </select>

        @error('relationship')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>

    <div>
        <label
            for="contact_number"
            class="block text-sm font-semibold text-slate-700"
        >
            Contact Number
        </label>

        <input
            id="contact_number"
            name="contact_number"
            type="text"
            value="{{ old(
                'contact_number',
                $record?->contact_number
            ) }}"
            placeholder="09XXXXXXXXX"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >

        @error('contact_number')
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
            for="educational_attainment"
            class="block text-sm font-semibold text-slate-700"
        >
            Educational Attainment
        </label>

        <select
            id="educational_attainment"
            name="educational_attainment"
            class="mt-2 block w-full rounded-xl border-slate-300
                   focus:border-sky-500 focus:ring-sky-500"
        >
            <option value="">
                Select educational attainment
            </option>

            @foreach (
                [
                    'No Formal Education',
                    'Elementary Level',
                    'Elementary Graduate',
                    'Junior High School Level',
                    'Junior High School Graduate',
                    'Senior High School Level',
                    'Senior High School Graduate',
                    'College Level',
                    'College Graduate',
                    'Postgraduate',
                    'Technical or Vocational',
                ] as $education
            )
                <option
                    value="{{ $education }}"
                    @selected(
                        old(
                            'educational_attainment',
                            $record?->educational_attainment
                        ) === $education
                    )
                >
                    {{ $education }}
                </option>
            @endforeach
        </select>

        @error('educational_attainment')
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

    <div class="md:col-span-2">
        <input
            type="hidden"
            name="is_primary"
            value="0"
        >

        <label
            class="flex cursor-pointer items-start gap-3 rounded-2xl
                   border border-slate-200 bg-slate-50 p-4"
        >
            <input
                name="is_primary"
                type="checkbox"
                value="1"
                @checked(
                    old(
                        'is_primary',
                        $record?->is_primary ?? false
                    )
                )
                class="mt-1 rounded border-slate-300 text-sky-600
                       focus:ring-sky-500"
            >

            <span>
                <span class="block font-bold text-slate-800">
                    Primary Parent or Guardian
                </span>

                <span class="mt-1 block text-sm text-slate-500">
                    Use this person as the profile’s main family
                    contact.
                </span>
            </span>
        </label>

        @error('is_primary')
            <p class="mt-2 text-sm text-red-600">
                {{ $message }}
            </p>
        @enderror
    </div>
</div>