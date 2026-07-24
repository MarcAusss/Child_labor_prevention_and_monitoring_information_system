<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'CLPMIS') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="clpmis-auth-shell min-h-screen">
    <main class="mx-auto flex min-h-screen max-w-7xl items-center px-5 py-12 sm:px-8">
        <section class="grid w-full overflow-hidden rounded-4xl border border-white/70 bg-white/85 shadow-lift backdrop-blur-xl lg:grid-cols-[1.15fr_.85fr]">
            <div class="relative overflow-hidden bg-slate-950 p-8 text-white sm:p-12 lg:p-16">
                <div class="absolute inset-0 opacity-15" style="background-image: linear-gradient(rgba(255,255,255,.16) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.16) 1px, transparent 1px); background-size: 34px 34px;"></div>
                <div class="relative">
                    <span class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/10 text-sky-200"><x-application-logo class="h-11 w-11" /></span>
                    <p class="mt-10 text-xs font-extrabold uppercase tracking-[0.25em] text-sky-300">Child protection information system</p>
                    <h1 class="mt-4 max-w-2xl text-balance text-4xl font-extrabold tracking-tight sm:text-5xl">Structured monitoring for timely, accountable action.</h1>
                    <p class="mt-5 max-w-xl text-base leading-8 text-slate-300">CLPMIS supports authorized profiling, interventions, audits, reporting, and secure coordination for child labor prevention and monitoring.</p>
                </div>
            </div>

            <div class="flex flex-col justify-center p-8 sm:p-12">
                <p class="clpmis-eyebrow">Secure official access</p>
                <h2 class="mt-3 text-3xl font-extrabold tracking-tight text-slate-950">Continue to the workspace</h2>
                <p class="mt-3 text-sm leading-7 text-slate-500">Use an authorized account. System activity may be logged for security and accountability.</p>

                <div class="mt-8 space-y-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-sky-700 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-sky-800">Open dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-xl bg-sky-700 px-5 py-3 text-sm font-extrabold text-white shadow-soft hover:bg-sky-800">Sign in securely</a>
                    @endauth
                </div>

                <div class="mt-8 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-2xl bg-slate-50 p-4"><p class="text-sm font-extrabold text-slate-900">Role-based access</p><p class="mt-1 text-xs leading-5 text-slate-500">Users only see actions and information permitted for their role.</p></div>
                    <div class="rounded-2xl bg-sky-50 p-4"><p class="text-sm font-extrabold text-sky-900">Private records</p><p class="mt-1 text-xs leading-5 text-sky-700">Sensitive documents are served through authorized workflows.</p></div>
                </div>
            </div>
        </section>
    </main>
</body>
</html>
