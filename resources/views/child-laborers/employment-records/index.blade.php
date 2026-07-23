<x-dashboard-shell title="Employment Records"
    subtitle="{{ $childLaborer->profile_number }} — {{ $childLaborer->full_name }}" badge="{{ $childLaborer->status }}">
    @if (session('success'))
        <div
            class="rounded-xl border border-emerald-200
                   bg-emerald-50 p-4 text-sm font-semibold
                   text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="rounded-xl border border-red-200
                   bg-red-50 p-5">
            <h2 class="font-bold text-red-800">
                Please correct the following:
            </h2>

            <ul class="mt-3 list-inside list-disc
                       space-y-1 text-sm text-red-700">
                @foreach ($errors->all() as $error)
                    <li>
                        {{ $error }}
                    </li>
                @endforeach
            </ul>
        </div>
    @endif

    @can('update', $childLaborer)
        <section class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm sm:p-8">
            <div class="mb-6 border-b border-slate-200 pb-5">
                <h2 class="text-xl font-bold text-slate-800">
                    Add Employment Record
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record the child’s employer, work activity,
                    schedule, arrangement, and earnings.
                </p>
            </div>

            <form method="POST"
                action="{{ route('child-laborers.employment-records.store', $childLaborer) }}">
                @csrf

                @include('child-laborers.employment-records.partials.form', [
                    'employmentRecord' => null,
                ])

                <div class="mt-7 flex justify-end
                           border-t border-slate-200 pt-6">
                    <button type="submit"
                        class="rounded-xl bg-sky-600
                               px-6 py-3 text-sm font-bold
                               text-white hover:bg-sky-700">
                        Add Employment Record
                    </button>
                </div>
            </form>
        </section>
    @endcan

    <section class="overflow-hidden rounded-3xl border
               border-slate-200 bg-white shadow-sm">
        <div
            class="flex flex-col gap-4 border-b
                   border-slate-200 p-6 sm:flex-row
                   sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Employment History
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $employmentRecords->count() }}
                    employment record(s)
                </p>
            </div>

            <a href="{{ route('child-laborers.show', $childLaborer) }}"
                class="rounded-xl border border-slate-300
                       px-4 py-2 text-center text-sm
                       font-bold text-slate-600">
                Back to Profile
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-sky-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Employment
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Work Schedule
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Income
                        </th>

                        <th class="px-6 py-4 text-left text-xs font-bold uppercase text-sky-800">
                            Period
                        </th>

                        @can('update', $childLaborer)
                            <th class="px-6 py-4 text-right text-xs font-bold uppercase text-sky-800">
                                Action
                            </th>
                        @endcan
                    </tr>
                </thead>

                <tbody class="divide-y divide-slate-100">
                    @forelse (
                        $employmentRecords as $employmentRecord
                    )
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <p class="font-bold text-slate-800">
                                        {{ $employmentRecord->occupation }}
                                    </p>

                                    @if ($employmentRecord->is_current)
                                        <span
                                            class="rounded-full
                                                   bg-emerald-100
                                                   px-2.5 py-1 text-[10px]
                                                   font-bold uppercase
                                                   text-emerald-700">
                                            Current
                                        </span>
                                    @endif
                                </div>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ $employmentRecord->employer_name ?: 'Employer not provided' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $employmentRecord->work_type }}
                                    ·
                                    {{ $employmentRecord->employment_arrangement }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $employmentRecord->industry ?: 'Industry not provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="font-semibold text-slate-700">
                                    {{ $employmentRecord->days_per_week }}
                                    day(s) per week
                                </p>

                                <p class="mt-1 text-sm text-slate-600">
                                    {{ number_format((float) $employmentRecord->hours_per_day, 2) }}
                                    hour(s) per day
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    About
                                    {{ number_format($employmentRecord->weekly_hours, 2) }}
                                    hour(s) weekly
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                @if ($employmentRecord->income_frequency === 'Unpaid')
                                    <span
                                        class="rounded-full bg-slate-100
                                               px-3 py-1 text-xs
                                               font-bold text-slate-600">
                                        Unpaid
                                    </span>
                                @else
                                    <p class="font-bold text-slate-800">
                                        ₱{{ number_format((float) $employmentRecord->income_amount, 2) }}
                                    </p>

                                    <p class="mt-1 text-xs text-slate-500">
                                        {{ $employmentRecord->income_frequency }}
                                    </p>
                                @endif
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ $employmentRecord->start_date->format('M d, Y') }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($employmentRecord->is_current)
                                        Present
                                    @elseif ($employmentRecord->end_date)
                                        Ended
                                        {{ $employmentRecord->end_date->format('M d, Y') }}
                                    @else
                                        End date not provided
                                    @endif
                                </p>
                            </td>

                            @can('update', $childLaborer)
                                <td class="px-6 py-4 text-right">
                                    <div class="flex flex-wrap justify-end gap-2">
                                        <a href="{{ route('child-laborers.work-hazards.index', [$childLaborer, $employmentRecord]) }}"
                                            class="rounded-lg bg-amber-50
               px-3 py-2 text-xs font-bold
               text-amber-700">
                                            Hazards
                                            ({{ $employmentRecord->work_hazards_count }})
                                        </a>

                                        @can('update', $childLaborer)
                                            <a href="{{ route('child-laborers.employment-records.edit', [$childLaborer, $employmentRecord]) }}"
                                                class="rounded-lg bg-sky-50
                   px-3 py-2 text-xs font-bold
                   text-sky-700">
                                                Edit
                                            </a>

                                            <form method="POST"
                                                action="{{ route('child-laborers.employment-records.destroy', [$childLaborer, $employmentRecord]) }}"
                                                onsubmit="return confirm('Remove this employment record?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit"
                                                    class="rounded-lg bg-red-50
                       px-3 py-2 text-xs font-bold
                       text-red-700">
                                                    Remove
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            @endcan
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5"
                                class="px-6 py-12 text-center
                                       text-sm text-slate-500">
                                No employment record has been added.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-shell>
