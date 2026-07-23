<x-dashboard-shell
    title="Education Records"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}"
    badge="{{ $childLaborer->status }}"
>
    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 p-4 text-sm font-semibold
                   text-emerald-700"
        >
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div
            class="rounded-xl border border-red-200 bg-red-50 p-5"
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

    @can('update', $childLaborer)
        <section
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8"
        >
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Add Education Record
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record the child’s current enrollment situation
                    or previous education history.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.education-records.store',
                    $childLaborer
                ) }}"
            >
                @csrf

                @include(
                    'child-laborers.education-records.partials.form',
                    [
                        'educationRecord' => null,
                    ]
                )

                <div
                    class="mt-7 flex justify-end
                           border-t border-slate-200 pt-6"
                >
                    <button
                        type="submit"
                        class="rounded-xl bg-sky-600 px-6 py-3
                               text-sm font-bold text-white
                               hover:bg-sky-700"
                    >
                        Add Education Record
                    </button>
                </div>
            </form>
        </section>
    @endcan

    <section
        class="overflow-hidden rounded-3xl border
               border-slate-200 bg-white shadow-sm"
    >
        <div
            class="flex flex-col gap-4 border-b border-slate-200
                   p-6 sm:flex-row sm:items-center
                   sm:justify-between"
        >
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Education History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $educationRecords->count() }}
                    education record(s)
                </p>
            </div>

            <a
                href="{{ route(
                    'child-laborers.show',
                    $childLaborer
                ) }}"
                class="rounded-xl border border-slate-300
                       px-4 py-2 text-center text-sm font-bold
                       text-slate-600"
            >
                Back to Profile
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            School
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Education Level
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Status
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Details
                        </th>

                        @can('update', $childLaborer)
                            <th
                                class="px-6 py-4 text-right text-xs
                                       font-bold uppercase text-sky-800"
                            >
                                Action
                            </th>
                        @endcan
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse (
                        $educationRecords as $educationRecord
                    )
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-slate-800">
                                        {{ $educationRecord->school_name
                                            ?: 'No school recorded' }}
                                    </p>

                                    @if ($educationRecord->is_current)
                                        <span
                                            class="rounded-full
                                                   bg-emerald-100
                                                   px-2.5 py-1 text-[10px]
                                                   font-bold uppercase
                                                   text-emerald-700"
                                        >
                                            Current
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $educationRecord->school_address
                                        ?: 'Address not provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700">
                                    {{ $educationRecord->grade_year_level
                                        ?: 'Not provided' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    School Year:
                                    {{ $educationRecord->school_year
                                        ?: 'Not provided' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    Last completed:
                                    {{ $educationRecord
                                        ->last_grade_completed
                                        ?: 'Not provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                @php
                                    $statusClasses = match (
                                        $educationRecord
                                            ->enrollment_status
                                    ) {
                                        'Enrolled' =>
                                            'bg-emerald-100 text-emerald-700',

                                        'Completed',
                                        'Graduated' =>
                                            'bg-sky-100 text-sky-700',

                                        'Dropped Out' =>
                                            'bg-red-100 text-red-700',

                                        default =>
                                            'bg-amber-100 text-amber-700',
                                    };
                                @endphp

                                <span
                                    class="inline-flex rounded-full
                                           px-3 py-1 text-xs font-bold
                                           {{ $statusClasses }}"
                                >
                                    {{ $educationRecord
                                        ->enrollment_status }}
                                </span>
                            </td>

                            <td class="px-6 py-4">
                                @if (
                                    $educationRecord
                                        ->reason_not_attending
                                )
                                    <p class="text-sm text-slate-700">
                                        {{ $educationRecord
                                            ->reason_not_attending }}
                                    </p>
                                @elseif ($educationRecord->remarks)
                                    <p class="text-sm text-slate-700">
                                        {{ $educationRecord->remarks }}
                                    </p>
                                @else
                                    <p class="text-sm text-slate-500">
                                        No additional details
                                    </p>
                                @endif

                                @if ($educationRecord->date_enrolled)
                                    <p class="mt-2 text-xs text-slate-500">
                                        Enrolled:
                                        {{ $educationRecord
                                            ->date_enrolled
                                            ->format('M d, Y') }}
                                    </p>
                                @endif

                                @if ($educationRecord->date_ended)
                                    <p class="mt-1 text-xs text-slate-500">
                                        Ended:
                                        {{ $educationRecord
                                            ->date_ended
                                            ->format('M d, Y') }}
                                    </p>
                                @endif
                            </td>

                            @can('update', $childLaborer)
                                <td class="px-6 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <a
                                            href="{{ route(
                                                'child-laborers.education-records.edit',
                                                [
                                                    $childLaborer,
                                                    $educationRecord,
                                                ]
                                            ) }}"
                                            class="rounded-lg bg-sky-50
                                                   px-3 py-2 text-xs
                                                   font-bold text-sky-700"
                                        >
                                            Edit
                                        </a>

                                        <form
                                            method="POST"
                                            action="{{ route(
                                                'child-laborers.education-records.destroy',
                                                [
                                                    $childLaborer,
                                                    $educationRecord,
                                                ]
                                            ) }}"
                                            onsubmit="return confirm('Remove this education record?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button
                                                type="submit"
                                                class="rounded-lg bg-red-50
                                                       px-3 py-2 text-xs
                                                       font-bold text-red-700"
                                            >
                                                Remove
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td
                                colspan="5"
                                class="px-6 py-12 text-center
                                       text-sm text-slate-500"
                            >
                                No education record has been added.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-shell>