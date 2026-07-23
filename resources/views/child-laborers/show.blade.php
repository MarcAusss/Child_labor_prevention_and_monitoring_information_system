<x-dashboard-shell title="{{ $childLaborer->full_name }}" subtitle="{{ $childLaborer->profile_number }}"
    badge="{{ $childLaborer->status }}">
    @if (session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <ul class="list-inside list-disc text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Personal information --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-8 shadow-sm">
        <div class="flex flex-col gap-8 md:flex-row">
            <div class="h-48 w-48 shrink-0 overflow-hidden rounded-2xl bg-slate-100">
                @if ($childLaborer->photo_path)
                    <img src="{{ route('child-laborers.photo', $childLaborer) }}" alt="{{ $childLaborer->full_name }}"
                        class="h-full w-full object-cover">
                @else
                    <div class="flex h-full items-center justify-center text-sm font-bold text-slate-400">
                        No Photo
                    </div>
                @endif
            </div>

            <div class="grid flex-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Full Name
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->full_name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Sex
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->sex }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Birth Date
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->birth_date->format('F d, Y') }}
                        ({{ $childLaborer->age }} years old)
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Civil Status
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->civil_status ?: 'Not provided' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Nationality
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->nationality }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Religion
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->religion ?: 'Not provided' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Contact Number
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->contact_number ?: 'Not provided' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Created By
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->creator?->name }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Assigned Officer
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->assignedOfficer?->name ?? 'Not assigned' }}
                    </p>
                </div>

                <div>
                    <p class="text-xs font-bold uppercase text-slate-400">
                        Reviewed By
                    </p>

                    <p class="mt-1 font-semibold text-slate-800">
                        {{ $childLaborer->reviewer?->name ?? 'Not reviewed' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    @if ($childLaborer->return_reason)
        <section class="rounded-2xl border border-amber-200 bg-amber-50 p-5">
            <h2 class="font-bold text-amber-900">
                Returned for correction
            </h2>

            <p class="mt-2 text-sm text-amber-800">
                {{ $childLaborer->return_reason }}
            </p>
        </section>
    @endif

    {{-- Profile sections --}}
    <section class="grid gap-6 lg:grid-cols-2">
        {{-- Birth information --}}
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Birth Information
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Official place where the child was born.
                    </p>
                </div>

                @can('update', $childLaborer)
                    <a href="{{ route('child-laborers.birth-information.edit', $childLaborer) }}"
                        class="rounded-xl bg-sky-50 px-4 py-2 text-xs font-bold text-sky-700">
                        {{ $childLaborer->birthInformation ? 'Edit' : 'Add' }}
                    </a>
                @endcan
            </div>

            @if ($childLaborer->birthInformation)
                <div class="mt-5 space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            PSGC Location
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{ $childLaborer->birthInformation->location_label }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Specific Place
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{ $childLaborer->birthInformation->place_of_birth ?: 'Not provided' }}
                        </p>
                    </div>
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500">
                    Birth information has not been added.
                </p>
            @endif
        </article>

        {{-- Residential address --}}
        <article class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Residential Address
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Current location where the child resides.
                    </p>
                </div>

                @can('update', $childLaborer)
                    <a href="{{ route('child-laborers.residential-address.edit', $childLaborer) }}"
                        class="rounded-xl bg-sky-50 px-4 py-2 text-xs font-bold text-sky-700">
                        {{ $childLaborer->residentialAddress ? 'Edit' : 'Add' }}
                    </a>
                @endcan
            </div>

            @if ($childLaborer->residentialAddress)
                <div class="mt-5 space-y-4">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Complete Address
                        </p>

                        <p class="mt-1 font-semibold leading-7 text-slate-800">
                            {{ $childLaborer->residentialAddress->full_address }}
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Landmark
                        </p>

                        <p class="mt-1 font-semibold text-slate-800">
                            {{ $childLaborer->residentialAddress->landmark ?: 'Not provided' }}
                        </p>
                    </div>
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50 px-4 py-5 text-sm text-slate-500">
                    Residential address has not been added.
                </p>
            @endif
        </article>
    </section>
    {{-- Family information --}}
    <section class="grid gap-6 lg:grid-cols-2">
        {{-- Parent or guardian --}}
        <article class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Parents and Guardians
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Primary family contact and additional guardians.
                    </p>
                </div>

                <a href="{{ route('child-laborers.parent-guardians.index', $childLaborer) }}"
                    class="rounded-xl bg-sky-50 px-4 py-2
                       text-xs font-bold text-sky-700">
                    @can('update', $childLaborer)
                        Manage
                    @else
                        View
                    @endcan
                </a>
            </div>

            @if ($childLaborer->primaryGuardian)
                <div class="mt-5 rounded-2xl border border-emerald-200
                       bg-emerald-50 p-5">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p
                                class="text-xs font-bold uppercase
                                   tracking-wide text-emerald-700">
                                Primary Contact
                            </p>

                            <p class="mt-1 font-bold text-slate-800">
                                {{ $childLaborer->primaryGuardian->full_name }}
                            </p>

                            <p class="mt-1 text-sm text-slate-600">
                                {{ $childLaborer->primaryGuardian->relationship }}
                            </p>
                        </div>

                        <span
                            class="rounded-full bg-emerald-100
                               px-3 py-1 text-xs font-bold
                               text-emerald-700">
                            Primary
                        </span>
                    </div>

                    <p class="mt-4 text-sm text-slate-600">
                        Contact:
                        {{ $childLaborer->primaryGuardian->contact_number ?: 'Not provided' }}
                    </p>
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50 px-4
                       py-5 text-sm text-slate-500">
                    No parent or guardian has been added.
                </p>
            @endif

            <p class="mt-4 text-sm text-slate-500">
                Total recorded:
                <span class="font-bold text-slate-700">
                    {{ $childLaborer->parentGuardians->count() }}
                </span>
            </p>
        </article>

        {{-- Household members --}}
        <article class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Household Members
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        People currently belonging to the child’s
                        household.
                    </p>
                </div>

                <a href="{{ route('child-laborers.household-members.index', $childLaborer) }}"
                    class="rounded-xl bg-sky-50 px-4 py-2
                       text-xs font-bold text-sky-700">
                    @can('update', $childLaborer)
                        Manage
                    @else
                        View
                    @endcan
                </a>
            </div>

            @if ($childLaborer->householdMembers->isNotEmpty())
                <div class="mt-5 space-y-3">
                    @foreach ($childLaborer->householdMembers->take(3) as $householdMember)
                        <div
                            class="flex items-center justify-between
                               gap-4 rounded-xl bg-slate-50
                               px-4 py-3">
                            <div>
                                <p class="font-bold text-slate-800">
                                    {{ $householdMember->full_name }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $householdMember->relationship }}
                                </p>
                            </div>

                            <p class="text-xs font-semibold text-slate-500">
                                {{ $householdMember->age !== null ? $householdMember->age . ' years old' : $householdMember->sex }}
                            </p>
                        </div>
                    @endforeach
                </div>

                @if ($childLaborer->householdMembers->count() > 3)
                    <p class="mt-4 text-sm text-slate-500">
                        And
                        {{ $childLaborer->householdMembers->count() - 3 }}
                        more household member(s).
                    </p>
                @endif
            @else
                <p class="mt-5 rounded-xl bg-slate-50 px-4
                       py-5 text-sm text-slate-500">
                    No household members have been added.
                </p>
            @endif
        </article>
    </section>

    {{-- Education information --}}
    <section class="rounded-3xl border border-slate-200
           bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Education Records
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Current enrollment information and education
                    history.
                </p>
            </div>

            <a href="{{ route('child-laborers.education-records.index', $childLaborer) }}"
                class="rounded-xl bg-sky-50 px-4 py-2
                   text-center text-xs font-bold text-sky-700">
                @can('update', $childLaborer)
                    Manage Education
                @else
                    View Education
                @endcan
            </a>
        </div>

        @if ($childLaborer->currentEducation)
            @php
                $educationStatusClasses = match ($childLaborer->currentEducation->enrollment_status) {
                    'Enrolled' => 'bg-emerald-100 text-emerald-700',

                    'Completed', 'Graduated' => 'bg-sky-100 text-sky-700',

                    'Dropped Out' => 'bg-red-100 text-red-700',

                    default => 'bg-amber-100 text-amber-700',
                };
            @endphp

            <div class="mt-5 rounded-2xl border border-slate-200
                   bg-slate-50 p-5">
                <div class="flex flex-col gap-4 sm:flex-row
                       sm:items-start sm:justify-between">
                    <div>
                        <p
                            class="text-xs font-bold uppercase
                               tracking-wide text-slate-400">
                            Current Education Situation
                        </p>

                        <p class="mt-2 text-lg font-bold text-slate-800">
                            {{ $childLaborer->currentEducation->school_name ?: 'No school recorded' }}
                        </p>

                        <p class="mt-1 text-sm text-slate-600">
                            {{ $childLaborer->currentEducation->grade_year_level ?: 'Grade or year level not provided' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            School Year:
                            {{ $childLaborer->currentEducation->school_year ?: 'Not provided' }}
                        </p>
                    </div>

                    <span
                        class="inline-flex self-start rounded-full
                           px-3 py-1 text-xs font-bold
                           {{ $educationStatusClasses }}">
                        {{ $childLaborer->currentEducation->enrollment_status }}
                    </span>
                </div>

                @if ($childLaborer->currentEducation->reason_not_attending)
                    <div
                        class="mt-5 rounded-xl border border-amber-200
                           bg-amber-50 px-4 py-3">
                        <p class="text-xs font-bold uppercase
                               text-amber-700">
                            Reason for Not Attending
                        </p>

                        <p class="mt-1 text-sm text-amber-800">
                            {{ $childLaborer->currentEducation->reason_not_attending }}
                        </p>
                    </div>
                @endif
            </div>
        @else
            <p class="mt-5 rounded-xl bg-slate-50 px-4
                   py-5 text-sm text-slate-500">
                No education information has been added.
            </p>
        @endif

        <p class="mt-4 text-sm text-slate-500">
            Total education records:

            <span class="font-bold text-slate-700">
                {{ $childLaborer->educationRecords->count() }}
            </span>
        </p>
    </section>

    {{-- Employment information --}}
    <section class="rounded-3xl border border-slate-200
           bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Employment Records
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Current work, employer, schedule, and employment
                    history.
                </p>
            </div>

            <a href="{{ route('child-laborers.employment-records.index', $childLaborer) }}"
                class="rounded-xl bg-sky-50 px-4 py-2
                   text-center text-xs font-bold text-sky-700">
                @can('update', $childLaborer)
                    Manage Employment
                @else
                    View Employment
                @endcan
            </a>
        </div>

        @if ($childLaborer->currentEmployment)
            <div class="mt-5 rounded-2xl border
                   border-slate-200 bg-slate-50 p-5">
                <div class="flex flex-col gap-4 sm:flex-row
                       sm:items-start sm:justify-between">
                    <div>
                        <p
                            class="text-xs font-bold uppercase
                               tracking-wide text-slate-400">
                            Current Work
                        </p>

                        <p class="mt-2 text-lg font-bold text-slate-800">
                            {{ $childLaborer->currentEmployment->occupation }}
                        </p>

                        <p class="mt-1 text-sm text-slate-600">
                            {{ $childLaborer->currentEmployment->employer_name ?: 'Employer not provided' }}
                        </p>

                        <p class="mt-1 text-xs text-slate-500">
                            {{ $childLaborer->currentEmployment->work_type }}
                            ·
                            {{ $childLaborer->currentEmployment->employment_arrangement }}
                        </p>
                    </div>

                    <span
                        class="inline-flex self-start rounded-full
                           bg-emerald-100 px-3 py-1 text-xs
                           font-bold text-emerald-700">
                        Current
                    </span>
                </div>

                <div class="mt-5 grid gap-4 sm:grid-cols-3">
                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Schedule
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ $childLaborer->currentEmployment->days_per_week }}
                            day(s) weekly
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Working Hours
                        </p>

                        <p class="mt-1 text-sm font-semibold text-slate-700">
                            {{ number_format((float) $childLaborer->currentEmployment->hours_per_day, 2) }}
                            hour(s) daily
                        </p>
                    </div>

                    <div>
                        <p class="text-xs font-bold uppercase text-slate-400">
                            Income
                        </p>

                        @if ($childLaborer->currentEmployment->income_frequency === 'Unpaid')
                            <p class="mt-1 text-sm font-semibold text-slate-700">
                                Unpaid
                            </p>
                        @else
                            <p class="mt-1 text-sm font-semibold text-slate-700">
                                ₱{{ number_format((float) $childLaborer->currentEmployment->income_amount, 2) }}
                                {{ $childLaborer->currentEmployment->income_frequency }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        @else
            <p class="mt-5 rounded-xl bg-slate-50
                   px-4 py-5 text-sm text-slate-500">
                No employment information has been added.
            </p>
        @endif

        <p class="mt-4 text-sm text-slate-500">
            Total employment records:

            <span class="font-bold text-slate-700">
                {{ $childLaborer->employmentRecords->count() }}
            </span>
        </p>
    </section>

    {{-- Work hazard summary --}}
    <section class="rounded-3xl border border-slate-200
           bg-white p-6 shadow-sm">
        @php
            $allWorkHazards = $childLaborer->employmentRecords->flatMap(fn($employment) => $employment->workHazards);

            $currentWorkHazards = $childLaborer->currentEmployment?->workHazards ?? collect();

            $unsafeHazardCount = $allWorkHazards
                ->filter(
                    fn($hazard) => $hazard->heavy_work ||
                        $hazard->long_hours ||
                        $hazard->night_work ||
                        $hazard->unsafe_conditions,
                )
                ->count();

            $hazardsWithoutPpe = $allWorkHazards->where('ppe_provided', false)->count();
        @endphp

        <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Work Hazards
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Hazard exposure, unsafe working conditions,
                    equipment, chemicals, PPE, and incidents.
                </p>
            </div>

            @if ($childLaborer->currentEmployment)
                <a href="{{ route('child-laborers.work-hazards.index', [$childLaborer, $childLaborer->currentEmployment]) }}"
                    class="rounded-xl bg-amber-50 px-4 py-2
                       text-center text-xs font-bold
                       text-amber-700">
                    @can('update', $childLaborer)
                        Manage Current Hazards
                    @else
                        View Current Hazards
                    @endcan
                </a>
            @else
                <a href="{{ route('child-laborers.employment-records.index', $childLaborer) }}"
                    class="rounded-xl bg-sky-50 px-4 py-2
                       text-center text-xs font-bold
                       text-sky-700">
                    View Employment
                </a>
            @endif
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-3">
            <article class="rounded-2xl border border-slate-200
                   bg-slate-50 p-5">
                <p class="text-xs font-bold uppercase
                       tracking-wide text-slate-400">
                    Total Hazards
                </p>

                <p class="mt-2 text-2xl font-bold text-slate-800">
                    {{ $allWorkHazards->count() }}
                </p>
            </article>

            <article class="rounded-2xl border border-red-200
                   bg-red-50 p-5">
                <p class="text-xs font-bold uppercase
                       tracking-wide text-red-500">
                    Unsafe Indicators
                </p>

                <p class="mt-2 text-2xl font-bold text-red-700">
                    {{ $unsafeHazardCount }}
                </p>
            </article>

            <article class="rounded-2xl border border-amber-200
                   bg-amber-50 p-5">
                <p class="text-xs font-bold uppercase
                       tracking-wide text-amber-600">
                    Without PPE
                </p>

                <p class="mt-2 text-2xl font-bold text-amber-700">
                    {{ $hazardsWithoutPpe }}
                </p>
            </article>
        </div>

        @if ($currentWorkHazards->isNotEmpty())
            <div class="mt-5 space-y-3">
                <p class="text-xs font-bold uppercase
                       tracking-wide text-slate-400">
                    Current Employment Hazards
                </p>

                @foreach ($currentWorkHazards->take(3) as $workHazard)
                    <div
                        class="flex flex-col gap-3 rounded-2xl
                           border border-slate-200 p-4
                           sm:flex-row sm:items-center
                           sm:justify-between">
                        <div>
                            <div class="flex flex-wrap gap-2">
                                <span
                                    class="rounded-full bg-red-100
                                       px-3 py-1 text-xs
                                       font-bold text-red-700">
                                    {{ $workHazard->hazard_type }}
                                </span>

                                <span
                                    class="rounded-full bg-slate-100
                                       px-3 py-1 text-xs
                                       font-bold text-slate-600">
                                    {{ $workHazard->exposure_frequency }}
                                </span>
                            </div>

                            <p class="mt-3 text-sm leading-6
                                   text-slate-700">
                                {{ $workHazard->hazard_description }}
                            </p>
                        </div>

                        @if ($workHazard->ppe_provided)
                            <span
                                class="shrink-0 rounded-full
                                   bg-emerald-100 px-3 py-1
                                   text-xs font-bold
                                   text-emerald-700">
                                PPE Provided
                            </span>
                        @else
                            <span
                                class="shrink-0 rounded-full
                                   bg-amber-100 px-3 py-1
                                   text-xs font-bold
                                   text-amber-700">
                                No PPE
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif ($childLaborer->currentEmployment)
            <p class="mt-5 rounded-xl bg-slate-50
                   px-4 py-5 text-sm text-slate-500">
                No hazard has been recorded for the current
                employment.
            </p>
        @else
            <p class="mt-5 rounded-xl bg-slate-50
                   px-4 py-5 text-sm text-slate-500">
                Add an employment record before documenting
                work hazards.
            </p>
        @endif
    </section>

    @can('viewHealth', $childLaborer)
        {{-- Health information --}}
        <section class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm">
            @php
                $currentHealth = $childLaborer->currentHealthInformation;

                $healthConcernCount = $currentHealth?->concern_count ?? 0;
            @endphp

            <div class="flex flex-col gap-4 sm:flex-row
                   sm:items-start sm:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-2">
                        <h2 class="text-lg font-bold text-slate-800">
                            Health Information
                        </h2>

                        <span
                            class="rounded-full bg-amber-100
                               px-3 py-1 text-[10px]
                               font-bold uppercase
                               text-amber-700">
                            Restricted
                        </span>
                    </div>

                    <p class="mt-1 text-sm text-slate-500">
                        Reported health conditions, injuries,
                        disability information, treatments, and
                        psychosocial concerns.
                    </p>
                </div>

                <a href="{{ route('child-laborers.health-information.index', $childLaborer) }}"
                    class="rounded-xl bg-sky-50 px-4 py-2
                       text-center text-xs font-bold
                       text-sky-700">
                    @can('updateHealth', $childLaborer)
                        Manage Health Information
                    @else
                        View Health Information
                    @endcan
                </a>
            </div>

            @if ($currentHealth)
                <div class="mt-5 rounded-2xl border
                       border-slate-200 bg-slate-50 p-5">
                    <div
                        class="flex flex-col gap-4 sm:flex-row
                           sm:items-start sm:justify-between">
                        <div>
                            <p
                                class="text-xs font-bold uppercase
                                   tracking-wide text-slate-400">
                                Current Health Assessment
                            </p>

                            <p class="mt-2 text-lg font-bold
                                   text-slate-800">
                                {{ $currentHealth->health_condition ?: 'No specific health condition recorded' }}
                            </p>

                            <p class="mt-1 text-xs text-slate-500">
                                Assessed
                                {{ $currentHealth->assessment_date->format('F d, Y') }}
                            </p>
                        </div>

                        @if ($healthConcernCount > 0)
                            <span
                                class="inline-flex self-start
                                   rounded-full bg-amber-100
                                   px-3 py-1 text-xs font-bold
                                   text-amber-700">
                                {{ $healthConcernCount }}
                                concern indicator(s)
                            </span>
                        @else
                            <span
                                class="inline-flex self-start
                                   rounded-full bg-emerald-100
                                   px-3 py-1 text-xs font-bold
                                   text-emerald-700">
                                No concerns recorded
                            </span>
                        @endif
                    </div>

                    <div class="mt-5 grid gap-4
                           sm:grid-cols-3">
                        <div>
                            <p class="text-xs font-bold uppercase
                                   text-slate-400">
                                Disability
                            </p>

                            <p class="mt-1 text-sm font-semibold
                                   text-slate-700">
                                {{ $currentHealth->has_disability ? 'Reported' : 'Not reported' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase
                                   text-slate-400">
                                Current Complaints
                            </p>

                            <p class="mt-1 text-sm font-semibold
                                   text-slate-700">
                                {{ $currentHealth->current_complaints ?: 'None recorded' }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs font-bold uppercase
                                   text-slate-400">
                                Health Facility
                            </p>

                            <p class="mt-1 text-sm font-semibold
                                   text-slate-700">
                                {{ $currentHealth->health_facility ?: 'Not recorded' }}
                            </p>
                        </div>
                    </div>

                    @if ($currentHealth->mental_health_concerns)
                        <div
                            class="mt-5 rounded-xl border
                               border-violet-200
                               bg-violet-50 px-4 py-3">
                            <p class="text-xs font-bold uppercase
                                   text-violet-700">
                                Mental-Health or Psychosocial Concerns
                            </p>

                            <p class="mt-1 text-sm leading-6
                                   text-violet-800">
                                {{ $currentHealth->mental_health_concerns }}
                            </p>
                        </div>
                    @endif
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50
                       px-4 py-5 text-sm text-slate-500">
                    No health information has been added.
                </p>
            @endif

            <p class="mt-4 text-sm text-slate-500">
                Total health assessments:

                <span class="font-bold text-slate-700">
                    {{ $childLaborer->healthInformationRecords->count() }}
                </span>
            </p>
        </section>
    @endcan

    {{-- Interventions and assistance --}}
    <section class="rounded-3xl border border-slate-200
           bg-white p-6 shadow-sm">
        @php
            $profileInterventions = $childLaborer->interventions;

            $activeInterventions = $profileInterventions->whereIn('status', ['Pending', 'Ongoing']);

            $completedInterventions = $profileInterventions->where('status', 'Completed');

            $latestIntervention = $profileInterventions
                ->sortByDesc(fn($item) => $item->date_provided?->timestamp ?? $item->created_at->timestamp)
                ->first();

            $totalInterventionValue = $profileInterventions->sum(fn($item) => (float) ($item->amount ?? 0));
        @endphp

        <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Interventions and Assistance
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Assistance, referrals, services, benefits, and
                    support provided to the child laborer.
                </p>
            </div>

            <a href="{{ route('child-laborers.interventions.index', $childLaborer) }}"
                class="rounded-xl bg-sky-50 px-4 py-2
                   text-center text-xs font-bold
                   text-sky-700">
                @can('manageInterventions', $childLaborer)
                    Manage Interventions
                @else
                    View Interventions
                @endcan
            </a>
        </div>

        <div class="mt-5 grid gap-4 sm:grid-cols-2
               lg:grid-cols-4">
            <article class="rounded-2xl border border-slate-200
                   bg-slate-50 p-5">
                <p class="text-xs font-bold uppercase
                       text-slate-400">
                    Total Records
                </p>

                <p class="mt-2 text-2xl font-bold text-slate-800">
                    {{ $profileInterventions->count() }}
                </p>
            </article>

            <article class="rounded-2xl border border-sky-200
                   bg-sky-50 p-5">
                <p class="text-xs font-bold uppercase
                       text-sky-500">
                    Active
                </p>

                <p class="mt-2 text-2xl font-bold text-sky-700">
                    {{ $activeInterventions->count() }}
                </p>
            </article>

            <article class="rounded-2xl border border-emerald-200
                   bg-emerald-50 p-5">
                <p class="text-xs font-bold uppercase
                       text-emerald-500">
                    Completed
                </p>

                <p class="mt-2 text-2xl font-bold
                       text-emerald-700">
                    {{ $completedInterventions->count() }}
                </p>
            </article>

            <article class="rounded-2xl border border-violet-200
                   bg-violet-50 p-5">
                <p class="text-xs font-bold uppercase
                       text-violet-500">
                    Recorded Value
                </p>

                <p class="mt-2 text-xl font-bold
                       text-violet-700">
                    ₱{{ number_format($totalInterventionValue, 2) }}
                </p>
            </article>
        </div>

        @if ($latestIntervention)
            @php
                $latestStatusClasses = match ($latestIntervention->status) {
                    'Pending' => 'bg-amber-100 text-amber-700',

                    'Ongoing' => 'bg-sky-100 text-sky-700',

                    'Completed' => 'bg-emerald-100 text-emerald-700',

                    'Cancelled' => 'bg-slate-200 text-slate-700',

                    'Discontinued' => 'bg-red-100 text-red-700',

                    default => 'bg-slate-100 text-slate-700',
                };
            @endphp

            <div class="mt-5 rounded-2xl border
                   border-slate-200 bg-slate-50 p-5">
                <div class="flex flex-col gap-4 sm:flex-row
                       sm:items-start sm:justify-between">
                    <div>
                        <p
                            class="text-xs font-bold uppercase
                               tracking-wide text-slate-400">
                            Latest Intervention
                        </p>

                        <p class="mt-2 text-lg font-bold
                               text-slate-800">
                            {{ $latestIntervention->intervention_type }}
                        </p>

                        <p class="mt-1 text-sm text-slate-600">
                            {{ $latestIntervention->provider }}
                        </p>
                    </div>

                    <span
                        class="inline-flex self-start
                           rounded-full px-3 py-1
                           text-xs font-bold
                           {{ $latestStatusClasses }}">
                        {{ $latestIntervention->status }}
                    </span>
                </div>

                <p class="mt-4 text-sm leading-7
                       text-slate-700">
                    {{ $latestIntervention->description }}
                </p>

                <div class="mt-4 flex flex-wrap gap-x-6
                       gap-y-2 text-xs text-slate-500">
                    <span>
                        Date provided:
                        {{ $latestIntervention->date_provided?->format('F d, Y') ?? 'Not yet provided' }}
                    </span>

                    <span>
                        Recorded value:
                        @if ($latestIntervention->amount !== null)
                            ₱{{ number_format((float) $latestIntervention->amount, 2) }}
                        @else
                            Not recorded
                        @endif
                    </span>
                </div>
            </div>
        @else
            <p class="mt-5 rounded-xl bg-slate-50
                   px-4 py-5 text-sm text-slate-500">
                No intervention or assistance has been recorded.
            </p>
        @endif
    </section>

    {{-- Document management --}}
    <section class="rounded-3xl border border-slate-200
           bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row
               sm:items-start sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-800">
                    Profile Documents
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Supporting documents, records, evidence,
                    referrals, and official files.
                </p>
            </div>

            <a href="{{ route('child-laborers.documents.index', $childLaborer) }}"
                class="rounded-xl bg-sky-50 px-4 py-2
                   text-center text-xs font-bold
                   text-sky-700">
                @can('uploadDocuments', $childLaborer)
                    Manage Documents
                @else
                    View Documents
                @endcan
            </a>
        </div>

        <div class="mt-5 rounded-2xl border
               border-slate-200 bg-slate-50 p-5">
            <p class="text-xs font-bold uppercase
                   tracking-wide text-slate-400">
                Available Documents
            </p>

            <p class="mt-2 text-3xl font-bold text-slate-800">
                {{ number_format($visibleDocumentCount) }}
            </p>
        </div>

        @if ($latestDocuments->isNotEmpty())
            <div class="mt-5 space-y-3">
                <p class="text-xs font-bold uppercase
                       tracking-wide text-slate-400">
                    Latest Documents
                </p>

                @foreach ($latestDocuments as $document)
                    <div
                        class="flex flex-col gap-3
                           rounded-2xl border
                           border-slate-200 p-4
                           sm:flex-row sm:items-center
                           sm:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap
                                   items-center gap-2">
                                <p class="break-all font-bold
                                       text-slate-800">
                                    {{ $document->original_name }}
                                </p>

                                @if ($document->is_confidential)
                                    <span
                                        class="rounded-full
                                           bg-amber-100
                                           px-2.5 py-1
                                           text-[10px]
                                           font-bold uppercase
                                           text-amber-700">
                                        Confidential
                                    </span>
                                @endif
                            </div>

                            <p class="mt-1 text-xs text-slate-500">
                                {{ $document->document_type }}
                                ·
                                {{ $document->formatted_file_size }}
                                ·
                                {{ $document->uploaded_at->format('M d, Y') }}
                            </p>
                        </div>

                        <a href="{{ route('child-laborers.documents.download', [$childLaborer, $document]) }}"
                            class="shrink-0 rounded-xl
                               bg-sky-600 px-4 py-2
                               text-center text-xs
                               font-bold text-white">
                            Download
                        </a>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-5 rounded-xl bg-slate-50
                   px-4 py-5 text-sm text-slate-500">
                No document is available for this profile.
            </p>
        @endif
    </section>

    @can('viewActivity', $childLaborer)
        <section class="rounded-3xl border border-slate-200
               bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 sm:flex-row
                   sm:items-start sm:justify-between">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        Profile Activity
                    </h2>

                    <p class="mt-1 text-sm text-slate-500">
                        Recent changes, workflow actions, documents,
                        interventions, and profile updates.
                    </p>
                </div>

                <a href="{{ route('child-laborers.activity-logs.index', $childLaborer) }}"
                    class="rounded-xl bg-sky-50 px-4 py-2
                       text-center text-xs font-bold
                       text-sky-700">
                    View Complete History
                </a>
            </div>

            @if ($recentActivityLogs->isNotEmpty())
                <div class="mt-5 divide-y divide-slate-200">
                    @foreach ($recentActivityLogs as $activityLog)
                        <article class="py-4 first:pt-0 last:pb-0">
                            <div
                                class="flex flex-col gap-3
                                   sm:flex-row sm:items-start
                                   sm:justify-between">
                                <div>
                                    <div
                                        class="flex flex-wrap
                                           items-center gap-2">
                                        <span
                                            class="rounded-full
                                               bg-sky-100
                                               px-3 py-1
                                               text-xs font-bold
                                               text-sky-700">
                                            {{ $activityLog->action_label }}
                                        </span>

                                        <span
                                            class="text-xs
                                               text-slate-400">
                                            {{ $activityLog->entity_label }}
                                        </span>
                                    </div>

                                    <p
                                        class="mt-2 text-sm
                                           font-semibold
                                           text-slate-700">
                                        {{ $activityLog->description }}
                                    </p>

                                    <p class="mt-1 text-xs
                                           text-slate-500">
                                        {{ $activityLog->actor_display }}

                                        @if ($activityLog->role_name)
                                            ·
                                            {{ $activityLog->role_name }}
                                        @endif
                                    </p>
                                </div>

                                <p class="shrink-0 text-xs
                                       text-slate-400">
                                    {{ $activityLog->created_at->format('M d, Y h:i A') }}
                                </p>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <p class="mt-5 rounded-xl bg-slate-50
                       px-4 py-5 text-sm text-slate-500">
                    No activity has been recorded for this profile.
                </p>
            @endif
        </section>
    @endcan

    {{-- Actions --}}
    <section class="rounded-3xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-lg font-bold text-slate-800">
            Profile Actions
        </h2>

        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ route('child-laborers.index') }}"
                class="rounded-xl border border-slate-300 px-4 py-2 text-sm font-bold text-slate-600">
                Back
            </a>

            @can('update', $childLaborer)
                <a href="{{ route('child-laborers.edit', $childLaborer) }}"
                    class="rounded-xl bg-sky-600 px-4 py-2 text-sm font-bold text-white">
                    Edit Personal Information
                </a>

                <a href="{{ route('child-laborers.birth-information.edit', $childLaborer) }}"
                    class="rounded-xl bg-cyan-600 px-4 py-2 text-sm font-bold text-white">
                    Birth Information
                </a>

                <a href="{{ route('child-laborers.residential-address.edit', $childLaborer) }}"
                    class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-bold text-white">
                    Residential Address
                </a>
            @endcan

            @can('submit', $childLaborer)
                <form method="POST" action="{{ route('child-laborers.submit', $childLaborer) }}">
                    @csrf
                    @method('PATCH')

                    <button class="rounded-xl bg-violet-600 px-4 py-2 text-sm font-bold text-white">
                        Submit for Review
                    </button>
                </form>
            @endcan

            @can('approve', $childLaborer)
                <form method="POST" action="{{ route('child-laborers.approve', $childLaborer) }}">
                    @csrf
                    @method('PATCH')

                    <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white">
                        Approve Profile
                    </button>
                </form>
            @endcan

            @can('restore', $childLaborer)
                <form method="POST" action="{{ route('child-laborers.restore', $childLaborer) }}">
                    @csrf
                    @method('PATCH')

                    <button class="rounded-xl bg-emerald-600 px-4 py-2 text-sm font-bold text-white">
                        Restore Profile
                    </button>
                </form>
            @endcan
        </div>

        @can('return', $childLaborer)
            <form method="POST" action="{{ route('child-laborers.return', $childLaborer) }}"
                class="mt-6 rounded-2xl border border-amber-200 bg-amber-50 p-5">
                @csrf
                @method('PATCH')

                <label class="block text-sm font-bold text-amber-900">
                    Reason for returning the profile
                </label>

                <textarea name="return_reason" rows="3" required class="mt-2 block w-full rounded-xl border-amber-300">{{ old('return_reason') }}</textarea>

                <button class="mt-3 rounded-xl bg-amber-600 px-4 py-2 text-sm font-bold text-white">
                    Return for Correction
                </button>
            </form>
        @endcan

        @can('archive', $childLaborer)
            <form method="POST" action="{{ route('child-laborers.archive', $childLaborer) }}"
                class="mt-6 rounded-2xl border border-red-200 bg-red-50 p-5">
                @csrf
                @method('PATCH')

                <label class="block text-sm font-bold text-red-900">
                    Archive reason
                </label>

                <textarea name="archive_reason" rows="2" class="mt-2 block w-full rounded-xl border-red-300">{{ old('archive_reason') }}</textarea>

                <button class="mt-3 rounded-xl bg-red-600 px-4 py-2 text-sm font-bold text-white">
                    Archive Profile
                </button>
            </form>
        @endcan
    </section>
</x-dashboard-shell>
