<x-workspace-shell
    title="Operations Dashboard"
    subtitle="Child labor prevention, profiling, intervention, audit, and reporting overview."
>
    @php
        $user = auth()->user();

        $statusColors = [
            'Draft' =>
                'bg-slate-500',

            'Submitted' =>
                'bg-sky-500',

            'Returned' =>
                'bg-amber-500',

            'Approved' =>
                'bg-emerald-500',

            'Archived' =>
                'bg-red-500',
        ];
    @endphp

    <section
        class="relative overflow-hidden rounded-[28px]
               bg-gradient-to-br from-slate-950
               via-slate-900 to-sky-950 p-6
               text-white shadow-2xl
               shadow-slate-300/40 sm:p-8"
    >
        <div
            class="absolute -right-20 -top-24
                   h-64 w-64 rounded-full
                   bg-sky-400/10 blur-3xl"
        ></div>

        <div
            class="absolute -bottom-28 left-1/3
                   h-56 w-56 rounded-full
                   bg-blue-500/10 blur-3xl"
        ></div>

        <div
            class="relative flex flex-col gap-6
                   xl:flex-row xl:items-end
                   xl:justify-between"
        >
            <div class="max-w-3xl">
                <p
                    class="text-xs font-black uppercase
                           tracking-[0.24em]
                           text-sky-300"
                >
                    {{ $roleLabel }} Workspace
                </p>

                <h2
                    class="mt-3 text-3xl font-black
                           tracking-tight sm:text-4xl"
                >
                    Welcome back,
                    {{ str($user->name)
                        ->before(' ') }}.
                </h2>

                <p
                    class="mt-3 max-w-2xl text-sm
                           leading-7 text-slate-300"
                >
                    Review current profile workloads,
                    intervention coverage, audit schedules,
                    notifications, and recent system activity
                    from one centralized workspace.
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                @if (
                    \Illuminate\Support\Facades\Route::has(
                        'child-laborers.index'
                    )
                )
                    <a
                        href="{{ route(
                            'child-laborers.index'
                        ) }}"
                        class="rounded-xl bg-white px-5
                               py-3 text-sm font-black
                               text-slate-900 shadow-lg
                               transition hover:bg-sky-50"
                    >
                        Open Profiles
                    </a>
                @endif

                @if (
                    ($user->isAdmin()
                        || $user->isSuperAdmin()
                        || $user->isProfilingOfficer())
                    && \Illuminate\Support\Facades\Route::has(
                        'child-laborers.create'
                    )
                )
                    <a
                        href="{{ route(
                            'child-laborers.create'
                        ) }}"
                        class="rounded-xl border
                               border-white/20 bg-white/10
                               px-5 py-3 text-sm font-black
                               text-white backdrop-blur
                               transition hover:bg-white/20"
                    >
                        New Profile
                    </a>
                @endif
            </div>
        </div>
    </section>

    <section
        class="grid gap-4 sm:grid-cols-2
               xl:grid-cols-4"
    >
        @foreach ([
            [
                'label' => 'Accessible Profiles',
                'value' => $summary['total_profiles'],
                'note' => 'Profiles available to your role',
                'classes' => 'from-sky-500 to-blue-600',
            ],
            [
                'label' => 'Submitted for Review',
                'value' => $summary['submitted_profiles'],
                'note' => 'Awaiting administrative action',
                'classes' => 'from-violet-500 to-purple-600',
            ],
            [
                'label' => 'Currently Working',
                'value' => $summary['currently_working'],
                'note' => 'Profiles with current employment',
                'classes' => 'from-amber-500 to-orange-600',
            ],
            [
                'label' => 'With Interventions',
                'value' => $summary['with_interventions'],
                'note' => 'Profiles receiving assistance',
                'classes' => 'from-emerald-500 to-teal-600',
            ],
        ] as $card)
            <article
                class="relative overflow-hidden
                       rounded-3xl border border-white
                       bg-white p-5 shadow-sm
                       ring-1 ring-slate-200/70"
            >
                <div
                    class="absolute right-0 top-0
                           h-24 w-24 translate-x-8
                           -translate-y-8 rounded-full
                           bg-gradient-to-br
                           {{ $card['classes'] }}
                           opacity-10 blur-xl"
                ></div>

                <div
                    class="flex items-start
                           justify-between gap-4"
                >
                    <div>
                        <p
                            class="text-xs font-black uppercase
                                   tracking-wide text-slate-400"
                        >
                            {{ $card['label'] }}
                        </p>

                        <p
                            class="mt-3 text-4xl font-black
                                   tracking-tight text-slate-900"
                        >
                            {{ number_format(
                                $card['value']
                            ) }}
                        </p>

                        <p
                            class="mt-2 text-xs leading-5
                                   text-slate-500"
                        >
                            {{ $card['note'] }}
                        </p>
                    </div>

                    <span
                        class="flex h-12 w-12 shrink-0
                               items-center justify-center
                               rounded-2xl bg-gradient-to-br
                               {{ $card['classes'] }}
                               text-white shadow-lg"
                    >
                        <svg
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            class="h-6 w-6"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="1.8"
                                d="M8 7V3m8 4V3M5 11h14M5 5h14a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V7a2 2 0 0 1 2-2Z"
                            />
                        </svg>
                    </span>
                </div>
            </article>
        @endforeach
    </section>

    <section class="grid gap-6 xl:grid-cols-3">
        <article
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm xl:col-span-2"
        >
            <div
                class="flex flex-col gap-3 sm:flex-row
                       sm:items-center
                       sm:justify-between"
            >
                <div>
                    <h2
                        class="text-lg font-black
                               text-slate-900"
                    >
                        Profile Creation Trend
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500"
                    >
                        New accessible profiles during the
                        last six months.
                    </p>
                </div>

                <span
                    class="rounded-full bg-sky-50
                           px-3 py-1 text-xs
                           font-bold text-sky-700"
                >
                    6-month view
                </span>
            </div>

            <div
                class="mt-8 grid h-64 grid-cols-6
                       items-end gap-3"
            >
                @foreach ($monthlyTrend as $month)
                    <div
                        class="flex h-full flex-col
                               items-center justify-end"
                    >
                        <span
                            class="mb-2 text-xs font-black
                                   text-slate-700"
                        >
                            {{ number_format(
                                $month['total']
                            ) }}
                        </span>

                        <div
                            class="flex h-44 w-full
                                   items-end rounded-2xl
                                   bg-slate-100 p-1"
                        >
                            <div
                                class="w-full rounded-xl
                                       bg-gradient-to-t
                                       from-sky-600
                                       to-cyan-400
                                       shadow-sm"
                                style="height: {{ max(
                                    4,
                                    $month['percentage']
                                ) }}%"
                            ></div>
                        </div>

                        <span
                            class="mt-3 text-center
                                   text-[10px] font-bold
                                   uppercase text-slate-400"
                        >
                            {{ $month['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </article>

        <article
            class="rounded-3xl border border-slate-200
                   bg-white p-6 shadow-sm"
        >
            <h2
                class="text-lg font-black
                       text-slate-900"
            >
                Profile Status
            </h2>

            <p
                class="mt-1 text-sm
                       text-slate-500"
            >
                Distribution of accessible profiles.
            </p>

            <div class="mt-6 space-y-5">
                @foreach (
                    $statusDistribution
                    as $status
                )
                    <div>
                        <div
                            class="mb-2 flex items-center
                                   justify-between gap-3"
                        >
                            <div
                                class="flex items-center
                                       gap-2"
                            >
                                <span
                                    class="h-2.5 w-2.5
                                           rounded-full
                                           {{ $statusColors[
                                                $status['label']
                                            ] ?? 'bg-slate-500' }}"
                                ></span>

                                <span
                                    class="text-sm font-bold
                                           text-slate-700"
                                >
                                    {{ $status['label'] }}
                                </span>
                            </div>

                            <span
                                class="text-sm font-black
                                       text-slate-900"
                            >
                                {{ number_format(
                                    $status['total']
                                ) }}
                            </span>
                        </div>

                        <div
                            class="h-2 overflow-hidden
                                   rounded-full bg-slate-100"
                        >
                            <div
                                class="h-full rounded-full
                                       {{ $statusColors[
                                            $status['label']
                                        ] ?? 'bg-slate-500' }}"
                                style="width: {{ $status[
                                    'percentage'
                                ] }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </article>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <article
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm"
        >
            <div
                class="flex items-center
                       justify-between gap-4
                       border-b border-slate-200
                       px-6 py-5"
            >
                <div>
                    <h2
                        class="text-lg font-black
                               text-slate-900"
                    >
                        Recently Updated Profiles
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500"
                    >
                        Latest profile activity within
                        your access scope.
                    </p>
                </div>

                @if (
                    \Illuminate\Support\Facades\Route::has(
                        'child-laborers.index'
                    )
                )
                    <a
                        href="{{ route(
                            'child-laborers.index'
                        ) }}"
                        class="text-xs font-black
                               text-sky-700 hover:text-sky-800"
                    >
                        View all
                    </a>
                @endif
            </div>

            <div class="divide-y divide-slate-100">
                @forelse (
                    $recentProfiles as $profile
                )
                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has(
                            'child-laborers.show'
                        )
                            ? route(
                                'child-laborers.show',
                                $profile
                            )
                            : '#' }}"
                        class="flex items-center gap-4
                               px-6 py-4 transition
                               hover:bg-slate-50"
                    >
                        <div
                            class="flex h-11 w-11 shrink-0
                                   items-center justify-center
                                   rounded-2xl bg-sky-100
                                   text-sm font-black
                                   text-sky-700"
                        >
                            {{ str($profile->first_name)
                                ->substr(0, 1)
                                ->upper() }}
                            {{ str($profile->last_name)
                                ->substr(0, 1)
                                ->upper() }}
                        </div>

                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm
                                       font-black text-slate-800"
                            >
                                {{ $profile->full_name }}
                            </p>

                            <p
                                class="mt-1 truncate text-xs
                                       text-slate-500"
                            >
                                {{ $profile->profile_number }}

                                ·

                                {{ $profile
                                    ->residentialAddress
                                    ?->locality?->name
                                    ?? $profile
                                        ->residentialAddress
                                        ?->province?->name
                                    ?? 'Location not recorded' }}
                            </p>
                        </div>

                        <span
                            class="rounded-full px-3 py-1
                                   text-[10px] font-black
                                   {{ match (
                                        $profile->status
                                    ) {
                                        'Approved' =>
                                            'bg-emerald-100 text-emerald-700',

                                        'Submitted' =>
                                            'bg-sky-100 text-sky-700',

                                        'Returned' =>
                                            'bg-amber-100 text-amber-700',

                                        'Archived' =>
                                            'bg-red-100 text-red-700',

                                        default =>
                                            'bg-slate-100 text-slate-700',
                                    } }}"
                        >
                            {{ $profile->status }}
                        </span>
                    </a>
                @empty
                    <p
                        class="px-6 py-14 text-center
                               text-sm text-slate-500"
                    >
                        No accessible profile is available.
                    </p>
                @endforelse
            </div>
        </article>

        <article
            class="overflow-hidden rounded-3xl
                   border border-slate-200
                   bg-white shadow-sm"
        >
            <div
                class="flex items-center
                       justify-between gap-4
                       border-b border-slate-200
                       px-6 py-5"
            >
                <div>
                    <h2
                        class="text-lg font-black
                               text-slate-900"
                    >
                        Notifications
                    </h2>

                    <p
                        class="mt-1 text-sm
                               text-slate-500"
                    >
                        {{ number_format(
                            $summary[
                                'unread_notifications'
                            ]
                        ) }}
                        unread notification(s).
                    </p>
                </div>

                @if (
                    \Illuminate\Support\Facades\Route::has(
                        'notifications.index'
                    )
                )
                    <a
                        href="{{ route(
                            'notifications.index'
                        ) }}"
                        class="text-xs font-black
                               text-sky-700 hover:text-sky-800"
                    >
                        Open inbox
                    </a>
                @endif
            </div>

            <div class="divide-y divide-slate-100">
                @forelse (
                    $recentNotifications
                    as $notification
                )
                    @php
                        $data =
                            $notification->data;
                    @endphp

                    <a
                        href="{{ \Illuminate\Support\Facades\Route::has(
                            'notifications.open'
                        )
                            ? route(
                                'notifications.open',
                                $notification->id
                            )
                            : '#' }}"
                        class="flex gap-4 px-6 py-4
                               transition hover:bg-slate-50"
                    >
                        <span
                            class="mt-1 h-2.5 w-2.5
                                   shrink-0 rounded-full
                                   {{ $notification->read_at
                                        ? 'bg-slate-300'
                                        : 'bg-sky-500' }}"
                        ></span>

                        <div class="min-w-0 flex-1">
                            <p
                                class="truncate text-sm
                                       font-black text-slate-800"
                            >
                                {{ data_get(
                                    $data,
                                    'title',
                                    'System Notification'
                                ) }}
                            </p>

                            <p
                                class="mt-1 line-clamp-2
                                       text-xs leading-5
                                       text-slate-500"
                            >
                                {{ data_get(
                                    $data,
                                    'message',
                                    'No message provided.'
                                ) }}
                            </p>
                        </div>

                        <span
                            class="shrink-0 text-[10px]
                                   font-medium text-slate-400"
                        >
                            {{ $notification
                                ->created_at
                                ->diffForHumans() }}
                        </span>
                    </a>
                @empty
                    <p
                        class="px-6 py-14 text-center
                               text-sm text-slate-500"
                    >
                        No notification is available.
                    </p>
                @endforelse
            </div>
        </article>
    </section>

    @if (
        $upcomingAudits->isNotEmpty()
        || $recentActivity->isNotEmpty()
    )
        <section class="grid gap-6 xl:grid-cols-2">
            @if ($upcomingAudits->isNotEmpty())
                <article
                    class="overflow-hidden rounded-3xl
                           border border-slate-200
                           bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200
                               px-6 py-5"
                    >
                        <h2
                            class="text-lg font-black
                                   text-slate-900"
                        >
                            Upcoming Audits
                        </h2>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500"
                        >
                            Scheduled administrative reviews.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach (
                            $upcomingAudits
                            as $audit
                        )
                            <a
                                href="{{ \Illuminate\Support\Facades\Route::has(
                                    'audit-schedules.show'
                                )
                                    ? route(
                                        'audit-schedules.show',
                                        $audit
                                    )
                                    : '#' }}"
                                class="flex items-center gap-4
                                       px-6 py-4 transition
                                       hover:bg-slate-50"
                            >
                                <div
                                    class="flex h-12 w-12
                                           shrink-0 flex-col
                                           items-center
                                           justify-center
                                           rounded-2xl
                                           bg-violet-100
                                           text-violet-700"
                                >
                                    <span
                                        class="text-[10px]
                                               font-black uppercase"
                                    >
                                        {{ $audit
                                            ->scheduled_at
                                            ->format('M') }}
                                    </span>

                                    <span
                                        class="text-lg font-black
                                               leading-none"
                                    >
                                        {{ $audit
                                            ->scheduled_at
                                            ->format('d') }}
                                    </span>
                                </div>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate text-sm
                                               font-black
                                               text-slate-800"
                                    >
                                        {{ $audit
                                            ->childLaborer
                                            ?->profile_number }}
                                        ·
                                        {{ $audit
                                            ->childLaborer
                                            ?->full_name }}
                                    </p>

                                    <p
                                        class="mt-1 truncate
                                               text-xs text-slate-500"
                                    >
                                        {{ $audit
                                            ->scheduled_at
                                            ->format(
                                                'h:i A'
                                            ) }}
                                        ·
                                        {{ $audit
                                            ->assignedAdministrator
                                            ?->name }}
                                    </p>
                                </div>

                                <span
                                    class="rounded-full
                                           bg-violet-100
                                           px-3 py-1
                                           text-[10px]
                                           font-black
                                           text-violet-700"
                                >
                                    {{ $audit->status }}
                                </span>
                            </a>
                        @endforeach
                    </div>
                </article>
            @endif

            @if ($recentActivity->isNotEmpty())
                <article
                    class="overflow-hidden rounded-3xl
                           border border-slate-200
                           bg-white shadow-sm"
                >
                    <div
                        class="border-b border-slate-200
                               px-6 py-5"
                    >
                        <h2
                            class="text-lg font-black
                                   text-slate-900"
                        >
                            Recent Activity
                        </h2>

                        <p
                            class="mt-1 text-sm
                                   text-slate-500"
                        >
                            Latest accountable system actions.
                        </p>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @foreach (
                            $recentActivity
                            as $activity
                        )
                            <div
                                class="flex gap-4 px-6 py-4"
                            >
                                <span
                                    class="mt-1 flex h-9 w-9
                                           shrink-0 items-center
                                           justify-center
                                           rounded-xl
                                           bg-slate-100
                                           text-slate-500"
                                >
                                    <svg
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        class="h-4 w-4"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="1.8"
                                            d="M12 8v4l3 2m6-2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"
                                        />
                                    </svg>
                                </span>

                                <div class="min-w-0 flex-1">
                                    <p
                                        class="text-sm font-bold
                                               leading-6
                                               text-slate-700"
                                    >
                                        {{ $activity->description }}
                                    </p>

                                    <p
                                        class="mt-1 text-xs
                                               text-slate-400"
                                    >
                                        {{ $activity
                                            ->actor_display }}

                                        ·

                                        {{ $activity
                                            ->created_at
                                            ->diffForHumans() }}
                                    </p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </article>
            @endif
        </section>
    @endif
</x-workspace-shell>
