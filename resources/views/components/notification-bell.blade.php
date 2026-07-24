@auth
    <a
        href="{{ route('notifications.index') }}"
        class="relative inline-flex h-11 w-11
               items-center justify-center rounded-xl
               border border-slate-200 bg-white
               text-slate-600 shadow-sm transition
               hover:border-sky-300 hover:bg-sky-50
               hover:text-sky-700"
        aria-label="Open notifications"
        title="Notifications"
    >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.8"
            stroke="currentColor"
            class="h-5 w-5"
            aria-hidden="true"
        >
            <path
                stroke-linecap="round"
                stroke-linejoin="round"
                d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0"
            />
        </svg>

        @if ($unreadCount > 0)
            <span
                class="absolute -right-1.5 -top-1.5
                       inline-flex min-w-5 items-center
                       justify-center rounded-full
                       bg-red-600 px-1.5 py-0.5
                       text-[10px] font-black text-white
                       ring-2 ring-white"
            >
                {{ $unreadCount > 99
                    ? '99+'
                    : $unreadCount }}
            </span>
        @endif
    </a>
@endauth
