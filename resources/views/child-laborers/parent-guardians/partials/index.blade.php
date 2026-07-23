<x-dashboard-shell
    title="Parents and Guardians"
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
                    Add Parent or Guardian
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    The first entry automatically becomes the primary
                    family contact.
                </p>
            </div>

            <form
                method="POST"
                action="{{ route(
                    'child-laborers.parent-guardians.store',
                    $childLaborer
                ) }}"
            >
                @csrf

                @include(
                    'child-laborers.parent-guardians.partials.form',
                    [
                        'parentGuardian' => null,
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
                        Add Parent or Guardian
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
                    Recorded Parents and Guardians
                </h2>

                <p class="mt-1 text-sm text-slate-500">
                    {{ $parentGuardians->count() }}
                    recorded contact(s)
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
                            Parent or Guardian
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Contact
                        </th>

                        <th
                            class="px-6 py-4 text-left text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Livelihood
                        </th>

                        <th
                            class="px-6 py-4 text-center text-xs
                                   font-bold uppercase text-sky-800"
                        >
                            Primary
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
                        $parentGuardians as $parentGuardian
                    )
                        <tr>
                            <td class="px-6 py-4">
                                <p class="font-bold text-slate-800">
                                    {{ $parentGuardian->full_name }}
                                </p>

                                <p class="mt-1 text-sm text-slate-500">
                                    {{ $parentGuardian->relationship }}
                                </p>

                                <p class="mt-1 text-xs text-slate-400">
                                    {{ $parentGuardian->educational_attainment
                                        ?: 'Education not provided' }}
                                </p>
                            </td>

                            <td
                                class="px-6 py-4 text-sm text-slate-600"
                            >
                                {{ $parentGuardian->contact_number
                                    ?: 'Not provided' }}
                            </td>

                            <td class="px-6 py-4">
                                <p class="text-sm font-semibold text-slate-700">
                                    {{ $parentGuardian->occupation
                                        ?: 'Not provided' }}
                                </p>

                                <p class="mt-1 text-xs text-slate-500">
                                    @if (
                                        $parentGuardian->monthly_income
                                        !== null
                                    )
                                        ₱{{ number_format(
                                            (float) $parentGuardian
                                                ->monthly_income,
                                            2
                                        ) }}
                                        monthly
                                    @else
                                        Income not provided
                                    @endif
                                </p>
                            </td>

                            <td class="px-6 py-4 text-center">
                                @if ($parentGuardian->is_primary)
                                    <span
                                        class="inline-flex rounded-full
                                               bg-emerald-100 px-3 py-1
                                               text-xs font-bold
                                               text-emerald-700"
                                    >
                                        Primary
                                    </span>
                                @else
                                    <span
                                        class="inline-flex rounded-full
                                               bg-slate-100 px-3 py-1
                                               text-xs font-bold
                                               text-slate-500"
                                    >
                                        Additional
                                    </span>
                                @endif
                            </td>

                            @can('update', $childLaborer)
                                <td class="px-6 py-4 text-right">
                                    <div
                                        class="flex justify-end gap-2"
                                    >
                                        <a
                                            href="{{ route(
                                                'child-laborers.parent-guardians.edit',
                                                [
                                                    $childLaborer,
                                                    $parentGuardian,
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
                                                'child-laborers.parent-guardians.destroy',
                                                [
                                                    $childLaborer,
                                                    $parentGuardian,
                                                ]
                                            ) }}"
                                            onsubmit="return confirm('Remove this parent or guardian?')"
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
                                No parent or guardian has been recorded.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-dashboard-shell>