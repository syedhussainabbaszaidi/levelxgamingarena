@extends('layouts.guest')

@section('title', 'Sign in — '.config('app.name'))

@section('content')
    <div class="mb-5">
        <h1 class="font-display text-xl font-bold text-white">Welcome back</h1>
        <p class="text-sm text-zinc-500">Sign in to book and manage your sessions.</p>
    </div>

    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <div>
            <label class="neon-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="input-neon">
            @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="neon-label" for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="input-neon">
            @error('password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>

        <label class="flex items-center gap-2 text-sm text-zinc-400">
            <input type="checkbox" name="remember" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500">
            Remember me
        </label>

        <div class="flex items-center justify-between pt-1">
            @if(Route::has('password.request'))
                <a href="{{ route('password.request') }}" class="text-xs text-zinc-500 hover:text-neon-400">Forgot password?</a>
            @endif
            <button class="btn-neon">Sign in</button>
        </div>
    </form>

    <p class="mt-6 border-t border-white/[0.06] pt-5 text-center text-sm text-zinc-500">
        New here?
        <a href="{{ route('register') }}" class="font-semibold text-neon-400 hover:text-neon-300">Create an account</a>
    </p>
@endsection
