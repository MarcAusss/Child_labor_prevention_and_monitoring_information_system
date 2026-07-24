<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'CLPMIS') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="clpmis-auth-shell min-h-screen text-slate-800 antialiased">
    @php
        [$authTitle, $authDescription] = match (true) {
            request()->routeIs('login') => [
                'Welcome back',
                'Sign in with your authorized CLPMIS account to continue.',
            ],
            request()->routeIs('register') => [
                'Create your account',
                'Register an authorized account for the monitoring workspace.',
            ],
            request()->routeIs('password.request') => [
                'Recover your account',
                'Enter your email address and we will send a secure reset link.',
            ],
            request()->routeIs('password.reset') => [
                'Set a new password',
                'Choose a strong password to protect your account.',
            ],
            request()->routeIs('password.confirm') => [
                'Confirm your identity',
                'Re-enter your password before continuing to this protected area.',
            ],
            request()->routeIs('verification.notice') => [
                'Verify your email',
                'Confirm your email address before accessing the system.',
            ],
            default => [
                'Secure access',
                'Continue to the Child Labor Prevention and Monitoring Information System.',
            ],
        };
    @endphp

    <main class="grid min-h-screen lg:grid-cols-[1.05fr_.95fr]">
        <section class="relative hidden overflow-hidden bg-slate-950 px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between xl:px-16">
            <div class="absolute inset-0 opacity-20" style="background-image: linear-gradient(rgba(255,255,255,.14) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,.14) 1px, transparent 1px); background-size: 34px 34px;"></div>
            <div class="absolute -left-24 top-12 h-72 w-72 rounded-full bg-sky-500/20 blur-3xl"></div>
            <div class="absolute -bottom-20 right-0 h-80 w-80 rounded-full bg-amber-500/15 blur-3xl"></div>

            <div class="relative">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-4">
                    <span class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 text-sky-200 ring-1 ring-white/15">
                        <x-application-logo class="h-10 w-10" />
                    </span>
                    <span>
                        <span class="block text-xl font-extrabold tracking-tight">CLPMIS</span>
                        <span class="mt-0.5 block text-xs font-semibold uppercase tracking-[0.16em] text-slate-400">Protected public service workspace</span>
                    </span>
                </a>
            </div>

            <div class="relative max-w-2xl">
                <p class="text-xs font-extrabold uppercase tracking-[0.28em] text-sky-300">Child-centered case monitoring</p>
                <h1 class="mt-5 text-balance font-display text-4xl font-extrabold leading-tight tracking-tight xl:text-5xl">
                    Clear records. Accountable action. Safer futures.
                </h1>
                <p class="mt-6 max-w-xl text-base leading-8 text-slate-300">
                    A secure information system for profiling, interventions, monitoring, audits, reporting, and coordinated action against child labor.
                </p>

                <div class="mt-8 grid max-w-xl grid-cols-3 gap-3">
                    @foreach ([
                        ['Profiles', 'Structured case records'],
                        ['Monitoring', 'Interventions and audits'],
                        ['Protection', 'Role-based data access'],
                    ] as [$label, $description])
                        <div class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <p class="text-sm font-extrabold text-white">{{ $label }}</p>
                            <p class="mt-1 text-xs leading-5 text-slate-400">{{ $description }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <p class="relative text-xs text-slate-500">
                Authorized personnel only · All access and actions may be recorded.
            </p>
        </section>

        <section class="flex min-h-screen items-center justify-center px-4 py-10 sm:px-8 lg:px-12">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center gap-3 lg:hidden">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-sky-100 text-sky-800">
                        <x-application-logo class="h-8 w-8" />
                    </span>
                    <div>
                        <p class="text-lg font-extrabold text-slate-900">CLPMIS</p>
                        <p class="text-xs font-semibold text-slate-500">Secure monitoring workspace</p>
                    </div>
                </div>

                <div class="auth-panel">
                    <div class="mb-7">
                        <p class="clpmis-eyebrow">Authorized Access</p>
                        <h1 class="mt-2 text-3xl font-extrabold tracking-tight text-slate-900">{{ $authTitle }}</h1>
                        <p class="mt-2 text-sm leading-6 text-slate-500">{{ $authDescription }}</p>
                    </div>

                    {{ $slot }}
                </div>

                <p class="mt-6 text-center text-xs leading-5 text-slate-500">
                    By continuing, you acknowledge the system's privacy, security, and accountability requirements.
                </p>
            </div>
        </section>
    </main>
</body>
</html>
