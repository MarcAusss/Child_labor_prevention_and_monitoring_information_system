<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · {{ config('app.name', 'CLPMIS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="clpmis-auth-shell min-h-screen">
    <main class="flex min-h-screen items-center justify-center px-5 py-12">
        <section class="w-full max-w-2xl overflow-hidden rounded-4xl border border-white/80 bg-white/90 shadow-lift backdrop-blur-xl">
            <div class="h-1.5 bg-gradient-to-r from-sky-800 via-cyan-400 to-amber-500"></div>
            <div class="p-8 text-center sm:p-12">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl bg-slate-950 text-sky-200">
                    <x-application-logo class="h-11 w-11" />
                </span>
                <p class="mt-7 text-xs font-extrabold uppercase tracking-[0.28em] text-sky-700">System response {{ $code }}</p>
                <h1 class="mt-3 text-balance text-3xl font-extrabold tracking-tight text-slate-950 sm:text-4xl">{{ $title }}</h1>
                <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-slate-500">{{ $message }}</p>

                <div class="mt-8 flex flex-wrap justify-center gap-3">
                    <a href="{{ auth()->check() ? route('dashboard') : route('login') }}" class="rounded-xl bg-sky-700 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-sky-800">
                        {{ auth()->check() ? 'Return to dashboard' : 'Return to sign in' }}
                    </a>
                    <button type="button" onclick="history.back()" class="rounded-xl border border-slate-300 bg-white px-5 py-3 text-sm font-extrabold text-slate-700 hover:bg-slate-50">Go back</button>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
