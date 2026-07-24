<section>
    <header>
        <p class="clpmis-eyebrow">Personal details</p>
        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Profile information</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Update the name and email address associated with your authorized account.</p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">@csrf</form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Full name')" />
            <x-text-input id="name" name="name" type="text" class="mt-2 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email address')" />
            <x-text-input id="email" name="email" type="email" class="mt-2 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800">
                    Your email address has not been verified.
                    <button form="send-verification" class="font-extrabold underline underline-offset-2 hover:text-amber-950">Send a new verification email.</button>
                </div>

                @if (session('status') === 'verification-link-sent')
                    <p class="mt-2 text-sm font-semibold text-emerald-700">A new verification link has been sent.</p>
                @endif
            @endif
        </div>

        <div class="flex flex-wrap items-center gap-4 pt-1">
            <x-primary-button>Save profile</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)" class="text-sm font-semibold text-emerald-700">Changes saved.</p>
            @endif
        </div>
    </form>
</section>
