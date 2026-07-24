<x-guest-layout>
    <div class="rounded-2xl border border-sky-200 bg-sky-50 p-5 text-sm leading-7 text-sky-900">
        We sent a verification link to your registered email address. Open that message and confirm the address before accessing protected CLPMIS records.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
            A new verification link has been sent.
        </div>
    @endif

    <div class="mt-6 grid gap-3 sm:grid-cols-2">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button class="w-full">Resend email</x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <x-secondary-button type="submit" class="w-full">Sign out</x-secondary-button>
        </form>
    </div>
</x-guest-layout>
