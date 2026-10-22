<section class="neon-card border-rose-500/20 p-6 sm:p-8">
    <header class="flex items-start gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-rose-500/15 text-rose-400">
            <x-icon name="warning" class="h-6 w-6" />
        </span>
        <div>
            <h2 class="font-display text-lg font-bold uppercase tracking-wider text-white">{{ __('Delete Account') }}</h2>
            <p class="mt-1 text-sm text-zinc-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
            </p>
        </div>
    </header>

    <button type="button" class="btn-danger mt-6 !px-4 !py-2 !text-sm" x-data @click="$dispatch('open-modal', 'confirm-user-deletion')">
        {{ __('Delete Account') }}
    </button>

    {{-- $profileDeleteRoute lets each panel point the form at its own endpoint
         (admin: profile.destroy, customer: customer.profile.destroy). --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route($profileDeleteRoute ?? 'profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="font-display text-lg font-bold uppercase tracking-wider text-white">
                {{ __('Are you sure you want to delete your account?') }}
            </h2>

            <p class="mt-2 text-sm text-zinc-400">
                {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.') }}
            </p>

            <div class="mt-5">
                <label class="neon-label" for="password">{{ __('Password') }}</label>
                <input id="password" name="password" type="password" autocomplete="current-password"
                       class="input-neon @error('password') input-invalid @enderror" placeholder="{{ __('Password') }}">
                @error('password')<p class="mt-1.5 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>

            <div class="mt-6 flex justify-end gap-2">
                <button type="button" class="btn-ghost" x-on:click="$dispatch('close')">{{ __('Cancel') }}</button>
                <button type="submit" class="btn-neon !bg-rose-600 !shadow-none hover:!bg-rose-500">{{ __('Delete Account') }}</button>
            </div>
        </form>
    </x-modal>
</section>
