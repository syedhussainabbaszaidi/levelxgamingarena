<section class="neon-card p-6 sm:p-8">
    <header class="flex items-start gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-neon-500/15 text-neon-400">
            <x-icon name="lock" class="h-6 w-6" />
        </span>
        <div>
            <h2 class="font-display text-lg font-bold uppercase tracking-wider text-white">{{ __('Update Password') }}</h2>
            <p class="mt-1 text-sm text-zinc-400">{{ __('Ensure your account is using a long, random password to stay secure.') }}</p>
        </div>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label class="neon-label" for="update_password_current_password">{{ __('Current Password') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" autocomplete="current-password"
                   class="input-neon @error('current_password') input-invalid @enderror">
            @error('current_password')<p class="mt-1.5 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label class="neon-label" for="update_password_password">{{ __('New Password') }}</label>
                <input id="update_password_password" name="password" type="password" autocomplete="new-password"
                       class="input-neon @error('password') input-invalid @enderror">
                @error('password')<p class="mt-1.5 text-xs text-rose-300">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="neon-label" for="update_password_password_confirmation">{{ __('Confirm Password') }}</label>
                <input id="update_password_password_confirmation" name="password_confirmation" type="password" autocomplete="new-password"
                       class="input-neon @error('password') input-invalid @enderror">
            </div>
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-neon">{{ __('Save') }}</button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-300">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
