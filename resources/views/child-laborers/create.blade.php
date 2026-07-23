<x-dashboard-shell
    title="Create Child Laborer Profile"
    subtitle="Enter the child laborer's initial personal information."
    badge="Draft Profile"
>
    @if ($errors->any())
        <div
            class="rounded-2xl border border-red-200 bg-red-50 p-5"
        >
            <h2 class="font-bold text-red-800">
                Please correct the following:
            </h2>

            <ul
                class="mt-3 list-inside list-disc space-y-1
                       text-sm text-red-700"
            >
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    <section
        class="mx-auto max-w-5xl overflow-hidden rounded-3xl
               border border-slate-200 bg-white shadow-sm"
    >
        <div
            class="border-b border-slate-200 bg-sky-50 px-6 py-5
                   sm:px-8"
        >
            <h2 class="text-xl font-bold text-slate-800">
                Personal Information
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                Required fields are marked with an asterisk.
            </p>
        </div>

        <form
            method="POST"
            action="{{ route('child-laborers.store') }}"
            enctype="multipart/form-data"
            class="p-6 sm:p-8"
        >
            @csrf

            <div class="grid gap-6 md:grid-cols-2">
                {{-- First name --}}
                <div>
                    <label
                        for="first_name"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        First Name
                        <span class="text-red-600">*</span>
                    </label>

                    <input
                        id="first_name"
                        name="first_name"
                        type="text"
                        value="{{ old('first_name') }}"
                        required
                        autofocus
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('first_name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Middle name --}}
                <div>
                    <label
                        for="middle_name"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Middle Name
                    </label>

                    <input
                        id="middle_name"
                        name="middle_name"
                        type="text"
                        value="{{ old('middle_name') }}"
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('middle_name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Last name --}}
                <div>
                    <label
                        for="last_name"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Last Name
                        <span class="text-red-600">*</span>
                    </label>

                    <input
                        id="last_name"
                        name="last_name"
                        type="text"
                        value="{{ old('last_name') }}"
                        required
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('last_name')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Suffix --}}
                <div>
                    <label
                        for="suffix"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Suffix
                    </label>

                    <input
                        id="suffix"
                        name="suffix"
                        type="text"
                        value="{{ old('suffix') }}"
                        placeholder="Jr., Sr., II, III"
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('suffix')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Sex --}}
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
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >
                        <option value="">
                            Select sex
                        </option>

                        <option
                            value="Male"
                            @selected(old('sex') === 'Male')
                        >
                            Male
                        </option>

                        <option
                            value="Female"
                            @selected(old('sex') === 'Female')
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

                {{-- Birth date --}}
                <div>
                    <label
                        for="birth_date"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Birth Date
                        <span class="text-red-600">*</span>
                    </label>

                    <input
                        id="birth_date"
                        name="birth_date"
                        type="date"
                        value="{{ old('birth_date') }}"
                        max="{{ now()->format('Y-m-d') }}"
                        required
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('birth_date')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Civil status --}}
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
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
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
                                    old('civil_status')
                                    === $civilStatus
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

                {{-- Nationality --}}
                <div>
                    <label
                        for="nationality"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Nationality
                        <span class="text-red-600">*</span>
                    </label>

                    <input
                        id="nationality"
                        name="nationality"
                        type="text"
                        value="{{ old(
                            'nationality',
                            'Filipino'
                        ) }}"
                        required
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('nationality')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Religion --}}
                <div>
                    <label
                        for="religion"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Religion
                    </label>

                    <input
                        id="religion"
                        name="religion"
                        type="text"
                        value="{{ old('religion') }}"
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('religion')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Contact number --}}
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
                        value="{{ old('contact_number') }}"
                        placeholder="09XXXXXXXXX"
                        class="mt-2 block w-full rounded-xl
                               border-slate-300 shadow-sm
                               focus:border-sky-500
                               focus:ring-sky-500"
                    >

                    @error('contact_number')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Assigned officer --}}
                @if ($canAssign)
                    <div>
                        <label
                            for="assigned_to"
                            class="block text-sm font-semibold text-slate-700"
                        >
                            Assigned Profiling Officer
                        </label>

                        <select
                            id="assigned_to"
                            name="assigned_to"
                            class="mt-2 block w-full rounded-xl
                                   border-slate-300 shadow-sm
                                   focus:border-sky-500
                                   focus:ring-sky-500"
                        >
                            <option value="">
                                Not assigned
                            </option>

                            @foreach (
                                $profilingOfficers as $officer
                            )
                                <option
                                    value="{{ $officer->id }}"
                                    @selected(
                                        (int) old(
                                            'assigned_to'
                                        ) === $officer->id
                                    )
                                >
                                    {{ $officer->name }}
                                    — {{ $officer->email }}
                                </option>
                            @endforeach
                        </select>

                        @error('assigned_to')
                            <p class="mt-2 text-sm text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>
                @endif

                {{-- Photo --}}
                <div class="{{ $canAssign ? '' : 'md:col-span-2' }}">
                    <label
                        for="photo"
                        class="block text-sm font-semibold text-slate-700"
                    >
                        Profile Photo
                    </label>

                    <input
                        id="photo"
                        name="photo"
                        type="file"
                        accept=".jpg,.jpeg,.png,.webp"
                        class="mt-2 block w-full rounded-xl
                               border border-slate-300 bg-white p-2
                               text-sm"
                    >

                    <p class="mt-2 text-xs text-slate-500">
                        JPG, JPEG, PNG or WebP, up to 5 MB.
                    </p>

                    @error('photo')
                        <p class="mt-2 text-sm text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>
            </div>

            <div
                class="mt-8 flex flex-col-reverse gap-3
                       border-t border-slate-200 pt-6
                       sm:flex-row sm:justify-end"
            >
                <a
                    href="{{ route('child-laborers.index') }}"
                    class="rounded-xl border border-slate-300
                           px-6 py-3 text-center text-sm
                           font-bold text-slate-600
                           hover:bg-slate-50"
                >
                    Cancel
                </a>

                <button
                    type="submit"
                    class="rounded-xl bg-sky-600 px-6 py-3
                           text-sm font-bold text-white
                           transition hover:bg-sky-700"
                >
                    Save Draft Profile
                </button>
            </div>
        </form>
    </section>
</x-dashboard-shell>