<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <meta
        name="csrf-token"
        content="{{ csrf_token() }}"
    >

    <title>
        {{ $title }} · {{ config('app.name', 'CLPMIS') }}
    </title>

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    <style>
        [x-cloak] {
            display: none !important;
        }
    </style>
</head>

<body
    class="min-h-screen bg-slate-100
           font-sans text-slate-700 antialiased"
>
    <div
        x-data="{ sidebarOpen: false }"
        class="min-h-screen"
    >
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/50
                   backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50
                   flex w-[290px] -translate-x-full
                   flex-col border-r border-slate-800
                   bg-slate-950 text-white shadow-2xl
                   transition-transform duration-300
                   lg:translate-x-0"
            :class="sidebarOpen
                ? 'translate-x-0'
                : '-translate-x-full'"
        >
            <div
                class="border-b border-white/10
                       px-6 py-6"
            >
                <a
                    href="{{ route(
                        'workspace.dashboard'
                    ) }}"
                    class="flex items-center gap-4"
                >
                    <div
                        class="flex h-12 w-12 items-center
                               justify-center rounded-2xl
                               bg-gradient-to-br from-sky-400
                               to-blue-600 shadow-lg
                               shadow-sky-950/40"
                    >
                        <span
                            class="text-lg font-black
                                   tracking-tight text-white"
                        >
                            CL
                        </span>
                    </div>

                    <div class="min-w-0">
                        <p
                            class="truncate text-lg font-black
                                   tracking-tight text-white"
                        >
                            CLPMIS
                        </p>

                        <p
                            class="truncate text-xs
                                   font-medium text-slate-400"
                        >
                            Child Labor Monitoring
                        </p>
                    </div>
                </a>
            </div>

            <nav
                class="flex-1 space-y-7 overflow-y-auto
                       px-4 py-6"
            >
                @foreach ($navigation as $section)
                    <section>
                        <p
                            class="px-3 text-[10px] font-black
                                   uppercase tracking-[0.22em]
                                   text-slate-500"
                        >
                            {{ $section['label'] }}
                        </p>

                        <div class="mt-3 space-y-1.5">
                            @foreach (
                                $section['links']
                                as $link
                            )
                                @php
                                    $active = request()
                                        ->routeIs(
                                            $link['pattern']
                                        );
                                @endphp

                                <a
                                    href="{{ route(
                                        $link['route']
                                    ) }}"
                                    class="group flex items-center
                                           gap-3 rounded-xl px-3.5
                                           py-3 text-sm font-bold
                                           transition
                                           {{ $active
                                                ? 'bg-sky-500 text-white shadow-lg shadow-sky-950/30'
                                                : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                                >
                                    <span
                                        class="flex h-9 w-9
                                               items-center
                                               justify-center
                                               rounded-lg
                                               {{ $active
                                                    ? 'bg-white/15'
                                                    : 'bg-white/5 group-hover:bg-white/10' }}"
                                    >
                                        @switch($link['icon'])
                                            @case('dashboard')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M4 13h6V4H4v9Zm10 7h6v-9h-6v9ZM4 20h6v-3H4v3Zm10-13h6V4h-6v3Z"
                                                    />
                                                </svg>
                                                @break

                                            @case('profiles')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m-2-12a4 4 0 0 1 0 7.75"
                                                    />
                                                </svg>
                                                @break

                                            @case('bell')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 13h4"
                                                    />
                                                </svg>
                                                @break

                                            @case('audit')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"
                                                    />
                                                </svg>
                                                @break

                                            @case('chart')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M4 19V9m6 10V5m6 14v-7m4 7H2"
                                                    />
                                                </svg>
                                                @break

                                            @case('users')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2m8-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm14 10v-2a4 4 0 0 0-3-3.87m-2-12a4 4 0 0 1 0 7.75"
                                                    />
                                                </svg>
                                                @break

                                            @case('history')
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5m4-1v5l3 2"
                                                    />
                                                </svg>
                                                @break

                                            @default
                                                <svg
                                                    viewBox="0 0 24 24"
                                                    fill="none"
                                                    stroke="currentColor"
                                                    class="h-5 w-5"
                                                >
                                                    <path
                                                        stroke-linecap="round"
                                                        stroke-linejoin="round"
                                                        stroke-width="1.8"
                                                        d="M4 4h16v16H4z"
                                                    />
                                                </svg>
                                        @endswitch
                                    </span>

                                    <span class="truncate">
                                        {{ $link['label'] }}
                                    </span>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach
            </nav>

            <div
                class="border-t border-white/10
                       p-4"
            >
                <div
                    class="rounded-2xl bg-white/5
                           p-4"
                >
                    <p
                        class="truncate text-sm font-bold
                               text-white"
                    >
                        {{ $user?->name }}
                    </p>

                    <p
                        class="mt-1 truncate text-xs
                               text-slate-400"
                    >
                        {{ $user?->email }}
                    </p>
                </div>
            </div>
        </aside>

        <div class="lg:pl-[290px]">
            <header
                class="sticky top-0 z-30
                       border-b border-slate-200/80
                       bg-white/90 backdrop-blur-xl"
            >
                <div
                    class="flex min-h-[76px] items-center
                           justify-between gap-4 px-4
                           sm:px-6 lg:px-8"
                >
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11
                                   items-center justify-center
                                   rounded-xl border
                                   border-slate-200 bg-white
                                   text-slate-600 shadow-sm
                                   lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Open navigation"
                        >
                            <svg
                                viewBox="0 0 24 24"
                                fill="none"
                                stroke="currentColor"
                                class="h-5 w-5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16"
                                />
                            </svg>
                        </button>

                        <div class="min-w-0">
                            <h1
                                class="truncate text-lg
                                       font-black tracking-tight
                                       text-slate-900 sm:text-xl"
                            >
                                {{ $title }}
                            </h1>

                            @if ($subtitle)
                                <p
                                    class="mt-0.5 hidden truncate
                                           text-xs text-slate-500
                                           sm:block"
                                >
                                    {{ $subtitle }}
                                </p>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (
                            \Illuminate\Support\Facades\Route::has(
                                'notifications.index'
                            )
                        )
                            <x-notification-bell />
                        @endif

                        <div
                            class="hidden items-center gap-3
                                   rounded-xl border
                                   border-slate-200 bg-white
                                   px-3 py-2 shadow-sm
                                   sm:flex"
                        >
                            <div
                                class="flex h-9 w-9 items-center
                                       justify-center rounded-lg
                                       bg-sky-100 text-sm
                                       font-black text-sky-700"
                            >
                                {{ str($user?->name)
                                    ->substr(0, 1)
                                    ->upper() }}
                            </div>

                            <div class="max-w-[170px]">
                                <p
                                    class="truncate text-xs
                                           font-bold text-slate-800"
                                >
                                    {{ $user?->name }}
                                </p>

                                <p
                                    class="truncate text-[10px]
                                           text-slate-400"
                                >
                                    {{ $user?->role?->name
                                        ?: 'Authorized User' }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <main
                class="mx-auto max-w-[1800px]
                       space-y-6 p-4 sm:p-6 lg:p-8"
            >
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
