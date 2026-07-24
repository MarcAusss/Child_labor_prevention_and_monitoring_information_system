<x-guest-layout>
    <x-auth-session-status class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 p-4 font-semibold text-emerald-700" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Account email')" />
            <x-text-input id="email" class="mt-2 block w-full" type="email" name="email" :value="old('email')" required autofocus placeholder="name@example.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full">Send secure reset link</x-primary-button>

        <a href="{{ route('login') }}" class="block text-center text-sm font-bold text-sky-700 hover:text-sky-900">
            Return to sign in
        </a>
    </form>
</x-guest-layout>
