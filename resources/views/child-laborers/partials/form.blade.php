@php
    $record = $childLaborer ?? null;
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <label for="first_name" class="block text-sm font-semibold text-slate-700">
            First Name <span class="text-red-600">*</span>
        </label>

        <input
            id="first_name"
            name="first_name"
            type="text"
            value="{{ old('first_name', $record?->first_name) }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('first_name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="middle_name" class="block text-sm font-semibold text-slate-700">
            Middle Name
        </label>

        <input
            id="middle_name"
            name="middle_name"
            type="text"
            value="{{ old('middle_name', $record?->middle_name) }}"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('middle_name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="last_name" class="block text-sm font-semibold text-slate-700">
            Last Name <span class="text-red-600">*</span>
        </label>

        <input
            id="last_name"
            name="last_name"
            type="text"
            value="{{ old('last_name', $record?->last_name) }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('last_name')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="suffix" class="block text-sm font-semibold text-slate-700">
            Suffix
        </label>

        <input
            id="suffix"
            name="suffix"
            type="text"
            value="{{ old('suffix', $record?->suffix) }}"
            placeholder="Jr., Sr., III"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('suffix')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="sex" class="block text-sm font-semibold text-slate-700">
            Sex <span class="text-red-600">*</span>
        </label>

        <select
            id="sex"
            name="sex"
            required
            class="mt-2 block w-full rounded-xl border-slate-300"
        >
            <option value="">Select sex</option>
            <option value="Male" @selected(old('sex', $record?->sex) === 'Male')>
                Male
            </option>
            <option value="Female" @selected(old('sex', $record?->sex) === 'Female')>
                Female
            </option>
        </select>

        @error('sex')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="birth_date" class="block text-sm font-semibold text-slate-700">
            Birth Date <span class="text-red-600">*</span>
        </label>

        <input
            id="birth_date"
            name="birth_date"
            type="date"
            value="{{ old('birth_date', $record?->birth_date?->format('Y-m-d')) }}"
            max="{{ now()->format('Y-m-d') }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('birth_date')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="civil_status" class="block text-sm font-semibold text-slate-700">
            Civil Status
        </label>

        <select
            id="civil_status"
            name="civil_status"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >
            <option value="">Select status</option>

            @foreach (['Single', 'Married', 'Widowed', 'Separated'] as $civilStatus)
                <option
                    value="{{ $civilStatus }}"
                    @selected(old('civil_status', $record?->civil_status) === $civilStatus)
                >
                    {{ $civilStatus }}
                </option>
            @endforeach
        </select>

        @error('civil_status')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="nationality" class="block text-sm font-semibold text-slate-700">
            Nationality <span class="text-red-600">*</span>
        </label>

        <input
            id="nationality"
            name="nationality"
            type="text"
            value="{{ old('nationality', $record?->nationality ?? 'Filipino') }}"
            required
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('nationality')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="religion" class="block text-sm font-semibold text-slate-700">
            Religion
        </label>

        <input
            id="religion"
            name="religion"
            type="text"
            value="{{ old('religion', $record?->religion) }}"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('religion')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="contact_number" class="block text-sm font-semibold text-slate-700">
            Contact Number
        </label>

        <input
            id="contact_number"
            name="contact_number"
            type="text"
            value="{{ old('contact_number', $record?->contact_number) }}"
            class="mt-2 block w-full rounded-xl border-slate-300"
        >

        @error('contact_number')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    @if ($canAssign)
        <div>
            <label for="assigned_to" class="block text-sm font-semibold text-slate-700">
                Assigned Profiling Officer
            </label>

            <select
                id="assigned_to"
                name="assigned_to"
                class="mt-2 block w-full rounded-xl border-slate-300"
            >
                <option value="">Not assigned</option>

                @foreach ($profilingOfficers as $officer)
                    <option
                        value="{{ $officer->id }}"
                        @selected(
                            (int) old(
                                'assigned_to',
                                $record?->assigned_to
                            ) === $officer->id
                        )
                    >
                        {{ $officer->name }}
                    </option>
                @endforeach
            </select>

            @error('assigned_to')
                <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <div>
        <label for="photo" class="block text-sm font-semibold text-slate-700">
            Profile Photo
        </label>

        <input
            id="photo"
            name="photo"
            type="file"
            accept=".jpg,.jpeg,.png,.webp"
            class="mt-2 block w-full rounded-xl border border-slate-300 p-2"
        >

        <p class="mt-2 text-xs text-slate-500">
            JPG, PNG or WebP. Maximum file size: 5 MB.
        </p>

        @error('photo')
            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>