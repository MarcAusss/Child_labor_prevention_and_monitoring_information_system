<x-guest-layout>
    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input
                id="email"
                class="mt-2 block w-full"
                type="email"
                name="email"
                :value="old('email')"
                required
                autofocus
                autocomplete="username"
                placeholder="name@example.com"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <div class="flex items-center justify-between gap-3">
                <x-input-label for="password" :value="__('Password')" />

                @if (Route::has('password.request'))
                    <a class="text-xs font-bold text-sky-700 hover:text-sky-900" href="{{ route('password.request') }}">
                        Forgot password?
                    </a>
                @endif
            </div>

            <x-text-input
                id="password"
                class="mt-2 block w-full"
                type="password"
                name="password"
                required
                autocomplete="current-password"
                placeholder="Enter your password"
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <label for="remember_me" class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-sky-700 focus:ring-sky-300" name="remember">
            <span class="text-sm font-semibold text-slate-600">Keep me signed in on this device</span>
        </label>

        <x-primary-button class="w-full">
            <span>Sign in to CLPMIS</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m9 18 6-6-6-6" />
            </svg>
        </x-primary-button>
    </form>
</x-guest-layout>
