<section class="neon-card p-6 sm:p-8">
    <header class="flex items-start gap-4">
        <span class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-neon-600 font-display text-lg font-bold text-white shadow-neon-sm">
            {{ substr($user->name, 0, 1) }}
        </span>
        <div>
            <h2 class="font-display text-lg font-bold uppercase tracking-wider text-white">{{ __('Profile Information') }}</h2>
            <p class="mt-1 text-sm text-zinc-400">{{ __("Update your account's profile information and email address.") }}</p>
        </div>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="hidden">
        @csrf
    </form>

    {{-- $profileUpdateRoute lets each panel point the form at its own endpoint
         (admin: profile.update, customer: customer.profile.update). --}}
    <form method="post" action="{{ route($profileUpdateRoute ?? 'profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label class="neon-label" for="name">{{ __('Name') }}</label>
            <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name"
                   class="input-neon @error('name') input-invalid @enderror">
            @error('name')<p class="mt-1.5 text-xs text-rose-300">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="neon-label" for="email">{{ __('Email') }}</label>
            <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required autocomplete="username"
                   class="input-neon @error('email') input-invalid @enderror">
            @error('email')<p class="mt-1.5 text-xs text-rose-300">{{ $message }}</p>@enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div class="mt-3 rounded-lg border border-amber-500/30 bg-amber-500/10 px-3 py-2 text-xs text-amber-200">
                    <p>{{ __('Your email address is unverified.') }}</p>
                    <button form="send-verification" class="mt-1 font-semibold underline hover:text-white">
                        {{ __('Click here to re-send the verification email.') }}
                    </button>
                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-1 font-medium text-emerald-300">{{ __('A new verification link has been sent to your email address.') }}</p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="submit" class="btn-neon">{{ __('Save') }}</button>

            @if (session('status') === 'profile-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-emerald-300">{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
