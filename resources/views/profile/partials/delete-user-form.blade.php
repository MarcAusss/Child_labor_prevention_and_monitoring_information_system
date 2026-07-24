<section class="space-y-6">
    <header>
        <p class="text-[10px] font-extrabold uppercase tracking-[0.2em] text-red-600">Danger zone</p>
        <h2 class="mt-1 text-xl font-extrabold text-slate-950">Delete account</h2>
        <p class="mt-2 text-sm leading-6 text-slate-500">Account deletion is permanent. Confirm that all required information has been retained before proceeding.</p>
    </header>

    <x-danger-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')">Delete account</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6 sm:p-8">
            @csrf
            @method('delete')

            <h2 class="text-xl font-extrabold text-slate-950">Confirm account deletion</h2>
            <p class="mt-2 text-sm leading-6 text-slate-500">Enter your password to permanently delete this account and its associated personal account data.</p>

            <div class="mt-6">
                <x-input-label for="password" value="{{ __('Password') }}" class="sr-only" />
                <x-text-input id="password" name="password" type="password" class="block w-full" placeholder="Password" />
                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex flex-wrap justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancel</x-secondary-button>
                <x-danger-button>Delete account</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
