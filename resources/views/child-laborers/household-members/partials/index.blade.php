<x-dashboard-shell
    title="Household Members"
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
            <ul
                class="list-inside list-disc space-y-1
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
                    Add Household Member
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    Record people currently belonging to the child’s
                    household.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.household-members.store',
                    $childLaborer
                ) }}"
            >
                @csrf

                @include(
                    'child-laborers.household-members.partials.form',
                    [
                        'householdMember' => null,
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
                        Add Household Member
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
            class="flex items-center justify-between gap-4
                   border-b border-slate-200 p-6"
        >
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Recorded Household Members
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $householdMembers->count() }}
                    household member(s)
                </p>
            </div>

            <a
                href="{{ route(
                    'child-laborers.show',
                    $childLaborer
                ) }}"
                class="rounded-xl border border-slate-300
                       px-4 py-2 text-sm font-bold text-slate-600"
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
                            Household Member
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Personal Details
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Education and Work
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Income
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
                        $householdMembers as $householdMember
                    )
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $householdMember->full_name }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $householdMember->relationship }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm text-slate-700">
                                    {{ $householdMember->sex }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    @if ($householdMember->birth_date)
                                        {{ $householdMember
                                            ->birth_date
                                            ->format('M d, Y') }}

                                        — {{ $householdMember->age }}
                                        years old
                                    @else
                                        Birth date not provided
                                    @endif
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $householdMember->civil_status
                                        ?: 'Civil status not provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ $householdMember->occupation
                                        ?: 'Occupation not provided' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    {{ $householdMember
                                        ->educational_attainment
                                        ?: 'Education not provided' }}
                                </p>
                            </td>

                            <td class="px-6 py-4 text-sm text-slate-700">
                                @if (
                                    $householdMember->monthly_income
                                    !== null
                                )
                                    ₱{{ number_format(
                                        (float) $householdMember
                                            ->monthly_income,
                                        2
                                    ) }}
                                @else
                                    Not provided
                                @endif
                            </td>

                            @can('update', $childLaborer)
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <a
                                            href="{{ route(
                                                'child-laborers.household-members.edit',
                                                [
                                                    $childLaborer,
                                                    $householdMember,
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
                                                'child-laborers.household-members.destroy',
                                                [
                                                    $childLaborer,
                                                    $householdMember,
                                                ]
                                            ) }}"
                                            onsubmit="return confirm('Remove this household member?')"
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
                                No household member has been recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-shell>