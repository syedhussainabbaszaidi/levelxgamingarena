@extends('layouts.guest')

@section('title', 'Create account — '.config('app.name'))

@section('content')
    <div class="mb-5">
        <h1 class="font-display text-xl font-bold text-white">Create your account</h1>
        <p class="text-sm text-zinc-500">Register once, then book any activity in seconds.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <label class="neon-label" for="name">Full name</label>
            <input id="name" name="name" value="{{ old('name') }}" required autofocus class="input-neon">
            @error('name')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="neon-label" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required class="input-neon">
            @error('email')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="neon-label" for="phone">Phone</label>
            <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+92 300 0000000" class="input-neon">
            @error('phone')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="neon-label" for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="input-neon">
            @error('password')<p class="mt-1 text-xs text-rose-400">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="neon-label" for="password_confirmation">Confirm password</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required class="input-neon">
        </div>

        <button class="btn-neon w-full !py-2.5">Create account</button>
    </form>

    <p class="mt-6 border-t border-white/[0.06] pt-5 text-center text-sm text-zinc-500">
        Already registered?
        <a href="{{ route('login') }}" class="font-semibold text-neon-400 hover:text-neon-300">Sign in</a>
    </p>
@endsection
