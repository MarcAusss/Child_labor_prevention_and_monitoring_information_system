<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} · {{ config('app.name', 'CLPMIS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="clpmis-app min-h-screen">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen">
        <div
            x-show="sidebarOpen"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-40 bg-slate-950/55 backdrop-blur-sm lg:hidden"
            @click="sidebarOpen = false"
        ></div>

        <aside
            class="fixed inset-y-0 left-0 z-50 flex w-[304px] -translate-x-full flex-col border-r border-slate-200/80 bg-[#fbfaf6]/95 shadow-2xl backdrop-blur-xl transition-transform duration-300 lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
        >
            <div class="relative overflow-hidden border-b border-slate-200 px-5 py-5">
                <div class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-sky-800 via-cyan-400 to-amber-500"></div>

                <a href="{{ route('dashboard') }}" class="flex items-center gap-3.5 rounded-2xl p-2 hover:bg-white">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-slate-950 text-sky-200 shadow-panel">
                        <x-application-logo class="h-9 w-9" />
                    </span>

                    <span class="min-w-0">
                        <span class="block truncate text-lg font-extrabold tracking-tight text-slate-950">CLPMIS</span>
                        <span class="mt-0.5 block truncate text-[10px] font-bold uppercase tracking-[0.14em] text-slate-500">
                            Protection and Monitoring
                        </span>
                    </span>
                </a>
            </div>

            <nav class="clpmis-sidebar-scroll flex-1 space-y-6 overflow-y-auto px-4 py-6">
                @foreach ($navigation as $section)
                    <section>
                        <p class="px-3 text-[9px] font-extrabold uppercase tracking-[0.22em] text-slate-400">
                            {{ $section['label'] }}
                        </p>

                        <div class="mt-2.5 space-y-1">
                            @foreach ($section['links'] as $link)
                                @php
                                    $active = request()->routeIs(...$link['patterns']);
                                @endphp

                                <a
                                    href="{{ route($link['route']) }}"
                                    class="group relative flex items-center gap-3 rounded-2xl px-3 py-2.5 text-sm transition
                                        {{ $active
                                            ? 'bg-slate-950 font-extrabold text-white shadow-panel'
                                            : 'font-semibold text-slate-600 hover:bg-white hover:text-slate-950 hover:shadow-soft' }}"
                                >
                                    @if ($active)
                                        <span class="absolute inset-y-3 left-0 w-1 rounded-r-full bg-amber-400"></span>
                                    @endif

                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl
                                        {{ $active ? 'bg-white/10 text-sky-200' : 'bg-slate-100 text-slate-500 group-hover:bg-sky-50 group-hover:text-sky-800' }}">
                                        @switch($link['icon'])
                                            @case('home')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m3 11 9-8 9 8v9a1 1 0 0 1-1 1h-5v-6H9v6H4a1 1 0 0 1-1-1v-9Z" /></svg>
                                                @break
                                            @case('pulse')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12h4l2.5-7 5 14 2.5-7h4" /></svg>
                                                @break
                                            @case('profiles')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm13 10v-2a4 4 0 0 0-3-3.87m-2-12a4 4 0 0 1 0 7.75" /></svg>
                                                @break
                                            @case('bell')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 8a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9Zm-8 13h4" /></svg>
                                                @break
                                            @case('audit')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 11l3 3L22 4M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11" /></svg>
                                                @break
                                            @case('report')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 3h7l4 4v14H7a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Zm7 0v5h5M9 13h6M9 17h6" /></svg>
                                                @break
                                            @case('chart')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 19V9m6 10V5m6 14v-7m4 7H2" /></svg>
                                                @break
                                            @case('users')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2m7-10a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm8 10v-2a4 4 0 0 0-3-3.87" /></svg>
                                                @break
                                            @case('history')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12a9 9 0 1 0 3-6.7L3 8m0-5v5h5m4-1v5l3 2" /></svg>
                                                @break
                                            @case('backup')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16v12H4zM8 3v3m8-3v3M8 18v3m8-3v3M8 10h8M8 14h5" /></svg>
                                                @break
                                            @case('security')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 3 20 6v6c0 5-3.2 8.5-8 10-4.8-1.5-8-5-8-10V6l8-3Zm-3 9 2 2 4-4" /></svg>
                                                @break
                                            @case('quality')
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="m12 3 2.2 4.5L19 8.2l-3.5 3.4.8 4.8L12 14.1l-4.3 2.3.8-4.8L5 8.2l4.8-.7L12 3Zm-7 16h14" /></svg>
                                                @break
                                        @endswitch
                                    </span>

                                    <span class="min-w-0 flex-1 truncate">{{ $link['label'] }}</span>

                                    @if ($active)
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4 text-sky-200"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" /></svg>
                                    @endif
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endforeach    
            </nav>

            <div class="border-t border-slate-200 p-4">
                <div class="rounded-2xl border border-slate-200 bg-white p-3.5 shadow-soft">
                    <div class="flex items-center gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-sky-100 text-sm font-extrabold text-sky-800">
                            {{ str($user?->name)->substr(0, 1)->upper() }}
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-extrabold text-slate-900">{{ $user?->name }}</p>
                            <p class="truncate text-[10px] font-bold uppercase tracking-wide text-slate-400">{{ $user?->role?->name ?? 'Authorized User' }}</p>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @if (Route::has('profile.edit'))
                            <a href="{{ route('profile.edit') }}" class="rounded-xl bg-slate-100 px-3 py-2 text-center text-xs font-bold text-slate-700 hover:bg-sky-50 hover:text-sky-800">Account</a>
                        @endif

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full rounded-xl bg-slate-950 px-3 py-2 text-xs font-bold text-white hover:bg-sky-900">Sign out</button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <div class="lg:pl-[304px]">
            <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-[#f4f1e9]/88 backdrop-blur-xl">
                <div class="mx-auto flex min-h-[78px] max-w-[1900px] items-center justify-between gap-4 px-4 sm:px-6 lg:px-8">
                    <div class="flex min-w-0 items-center gap-3">
                        <button
                            type="button"
                            class="inline-flex h-11 w-11 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 shadow-sm lg:hidden"
                            @click="sidebarOpen = true"
                            aria-label="Open navigation"
                        >
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        </button>

                        <div class="min-w-0">
                            <div class="flex items-center gap-2 text-[10px] font-extrabold uppercase tracking-[0.18em] text-slate-400">
                                <span>CLPMIS</span>
                                <span class="h-1 w-1 rounded-full bg-amber-500"></span>
                                <span>{{ now()->format('M d, Y') }}</span>
                            </div>
                            <h1 class="mt-1 truncate text-lg font-extrabold tracking-tight text-slate-950 sm:text-xl">{{ $title }}</h1>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if (Route::has('notifications.index'))
                            <x-notification-bell />
                        @endif

                        <x-dropdown align="right" width="64">
                            <x-slot name="trigger">
                                <button type="button" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white/90 px-2.5 py-2 shadow-sm hover:border-sky-300">
                                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-slate-950 text-xs font-extrabold text-sky-200">
                                        {{ str($user?->name)->substr(0, 1)->upper() }}
                                    </span>
                                    <span class="hidden max-w-[150px] text-left sm:block">
                                        <span class="block truncate text-xs font-extrabold text-slate-900">{{ $user?->name }}</span>
                                        <span class="block truncate text-[9px] font-bold uppercase tracking-wide text-slate-400">{{ $user?->role?->name ?? 'User' }}</span>
                                    </span>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="hidden h-4 w-4 text-slate-400 sm:block"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m6 9 6 6 6-6" /></svg>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <div class="border-b border-slate-100 px-4 py-3">
                                    <p class="truncate text-sm font-extrabold text-slate-900">{{ $user?->name }}</p>
                                    <p class="mt-0.5 truncate text-xs text-slate-500">{{ $user?->email }}</p>
                                </div>

                                @if (Route::has('profile.edit'))
                                    <x-dropdown-link :href="route('profile.edit')">Account settings</x-dropdown-link>
                                @endif

                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();">
                                        Sign out securely
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>
                </div>
            </header>

            <main class="mx-auto max-w-[1900px] p-4 sm:p-6 lg:p-8">
                <div class="clpmis-content">
                    {{ $slot }}
                </div>
            </main>

            <footer class="mx-auto max-w-[1900px] px-4 pb-6 text-center text-[10px] font-semibold uppercase tracking-[0.14em] text-slate-400 sm:px-6 lg:px-8">
                Child Labor Prevention and Monitoring Information System · Secure official use
            </footer>
        </div>
    </div>
</body>
</html>
