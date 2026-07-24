<x-dashboard-shell
    title="Notifications"
    subtitle="Your private CLPMIS alerts, workflow updates, and system messages."
    badge="{{ number_format($unreadCount) }} unread"
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

    <section
        class="grid gap-4 sm:grid-cols-3"
    >
        <article
            class="rounded-2xl border border-sky-200
                   bg-sky-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-sky-600"
            >
                All Notifications
            </p>

            <p
                class="mt-2 text-3xl font-black
                       text-sky-700"
            >
                {{ number_format($totalCount) }}
            </p>
        </article>

        <article
            class="rounded-2xl border border-amber-200
                   bg-amber-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-amber-600"
            >
                Unread
            </p>

            <p
                class="mt-2 text-3xl font-black
                       text-amber-700"
            >
                {{ number_format($unreadCount) }}
            </p>
        </article>

        <article
            class="rounded-2xl border border-emerald-200
                   bg-emerald-50 p-5"
        >
            <p
                class="text-xs font-bold uppercase
                       tracking-wide text-emerald-600"
            >
                Read
            </p>

            <p
                class="mt-2 text-3xl font-black
                       text-emerald-700"
            >
                {{ number_format($readCount) }}
            </p>
        </article>
    </section>

    <section
        class="rounded-3xl border border-slate-200
               bg-white p-5 shadow-sm"
    >
        <form
            method="GET"
            class="grid gap-4 md:grid-cols-2
                   xl:grid-cols-4"
        >
            <div class="md:col-span-2">
                <label
                    for="search"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Search Notifications
                </label>

                <input
                    id="search"
                    name="search"
                    value="{{ $search }}"
                    placeholder="Title, message, or actor..."
                    class="mt-2 block w-full rounded-xl
                           border-slate-300
                           focus:border-sky-500
                           focus:ring-sky-500"
                >
            </div>

            <div>
                <label
                    for="state"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Read Status
                </label>

                <select
                    id="state"
                    name="state"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option
                        value="all"
                        @selected($state === 'all')
                    >
                        All
                    </option>

                    <option
                        value="unread"
                        @selected($state === 'unread')
                    >
                        Unread
                    </option>

                    <option
                        value="read"
                        @selected($state === 'read')
                    >
                        Read
                    </option>
                </select>
            </div>

            <div>
                <label
                    for="type"
                    class="block text-xs font-bold uppercase
                           tracking-wide text-slate-500"
                >
                    Notification Type
                </label>

                <select
                    id="type"
                    name="type"
                    class="mt-2 block w-full rounded-xl
                           border-slate-300"
                >
                    <option value="">
                        All types
                    </option>

                    @foreach (
                        $typeOptions
                        as $value => $label
                    )
                        <option
                            value="{{ $value }}"
                            @selected(
                                $selectedType === $value
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div
                class="flex flex-wrap items-end gap-2
                       md:col-span-2 xl:col-span-4"
            >
                <button
                    type="submit"
                    class="rounded-xl bg-sky-600
                           px-5 py-3 text-sm font-bold
                           text-white hover:bg-sky-700"
                >
                    Apply Filters
                </button>

                <a
                    href="{{ route(
                        'notifications.index'
                    ) }}"
                    class="rounded-xl border border-slate-300
                           px-5 py-3 text-sm font-bold
                           text-slate-600"
                >
                    Reset
                </a>

                <form
                    method="POST"
                    action="{{ route(
                        'notifications.mark-all-read'
                    ) }}"
                >
                    @csrf
                    @method('PUT')

                    <button
                        type="submit"
                        class="rounded-xl bg-slate-800
                               px-5 py-3 text-sm font-bold
                               text-white"
                    >
                        Mark All as Read
                    </button>
                </form>
            </div>
        </form>
    </section>

    <section
        class="overflow-hidden rounded-3xl
               border border-slate-200 bg-white
               shadow-sm"
    >
        <div
            class="border-b border-slate-200
                   px-6 py-5"
        >
            <h2
                class="text-xl font-bold
                       text-slate-800"
            >
                Notification Inbox
            </h2>

            <p class="mt-1 text-sm text-slate-500">
                {{ number_format(
                    $notifications->total()
                ) }}
                matching notification(s)
            </p>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse (
                $notifications as $notification
            )
                @php
                    $data =
                        $notification->data;

                    $severity =
                        data_get(
                            $data,
                            'severity',
                            'info'
                        );

                    $type =
                        data_get(
                            $data,
                            'notification_type',
                            'system'
                        );

                    $classes = match ($severity) {
                        'success' =>
                            'border-emerald-200 bg-emerald-50 text-emerald-700',

                        'warning' =>
                            'border-amber-200 bg-amber-50 text-amber-700',

                        'danger' =>
                            'border-red-200 bg-red-50 text-red-700',

                        default =>
                            'border-sky-200 bg-sky-50 text-sky-700',
                    };
                @endphp

                <article
                    class="p-6
                           {{ $notification->read_at
                                ? 'bg-white'
                                : 'bg-sky-50/40' }}"
                >
                    <div
                        class="flex flex-col gap-5
                               lg:flex-row lg:items-start
                               lg:justify-between"
                    >
                        <div
                            class="flex min-w-0
                                   items-start gap-4"
                        >
                            <div
                                class="mt-1 h-3 w-3
                                       shrink-0 rounded-full
                                       {{ $notification->read_at
                                            ? 'bg-slate-300'
                                            : 'bg-sky-500' }}"
                            ></div>

                            <div class="min-w-0">
                                <div
                                    class="flex flex-wrap
                                           items-center gap-2"
                                >
                                    <h3
                                        class="text-base font-bold
                                               text-slate-800"
                                    >
                                        {{ data_get(
                                            $data,
                                            'title',
                                            'System Notification'
                                        ) }}
                                    </h3>

                                    <span
                                        class="rounded-full border
                                               px-3 py-1 text-[10px]
                                               font-bold uppercase
                                               {{ $classes }}"
                                    >
                                        {{ str($type)->headline() }}
                                    </span>

                                    @unless ($notification->read_at)
                                        <span
                                            class="rounded-full
                                                   bg-sky-600 px-2.5
                                                   py-1 text-[10px]
                                                   font-bold uppercase
                                                   text-white"
                                        >
                                            New
                                        </span>
                                    @endunless
                                </div>

                                <p
                                    class="mt-2 max-w-4xl
                                           text-sm leading-7
                                           text-slate-600"
                                >
                                    {{ data_get(
                                        $data,
                                        'message',
                                        'No message was provided.'
                                    ) }}
                                </p>

                                <div
                                    class="mt-3 flex flex-wrap
                                           gap-x-5 gap-y-1
                                           text-xs text-slate-400"
                                >
                                    <span>
                                        {{ $notification
                                            ->created_at
                                            ->format(
                                                'F d, Y h:i A'
                                            ) }}
                                    </span>

                                    @if (
                                        data_get(
                                            $data,
                                            'actor_name'
                                        )
                                    )
                                        <span>
                                            By
                                            {{ data_get(
                                                $data,
                                                'actor_name'
                                            ) }}
                                        </span>
                                    @endif

                                    @if ($notification->read_at)
                                        <span>
                                            Read
                                            {{ $notification
                                                ->read_at
                                                ->diffForHumans() }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div
                            class="flex shrink-0
                                   flex-wrap gap-2"
                        >
                            <a
                                href="{{ route(
                                    'notifications.open',
                                    $notification->id
                                ) }}"
                                class="rounded-xl bg-sky-600
                                       px-4 py-2.5
                                       text-xs font-bold
                                       text-white hover:bg-sky-700"
                            >
                                Open
                            </a>

                            @if ($notification->read_at)
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'notifications.unread',
                                        $notification->id
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button
                                        type="submit"
                                        class="rounded-xl
                                               border border-slate-300
                                               px-4 py-2.5
                                               text-xs font-bold
                                               text-slate-600"
                                    >
                                        Mark Unread
                                    </button>
                                </form>
                            @else
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'notifications.read',
                                        $notification->id
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button
                                        type="submit"
                                        class="rounded-xl
                                               border border-slate-300
                                               px-4 py-2.5
                                               text-xs font-bold
                                               text-slate-600"
                                    >
                                        Mark Read
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </article>
            @empty
                <div
                    class="px-6 py-16 text-center
                           text-sm text-slate-500"
                >
                    No notification matches the selected filters.
                </div>
            @endforelse
        </div>

        @if ($notifications->hasPages())
            <div class="border-t border-slate-200 p-5">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</x-dashboard-shell>
