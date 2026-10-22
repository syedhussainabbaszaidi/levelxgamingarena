<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center font-sans antialiased">
    <a href="{{ route('public.index') }}" class="mb-6 flex w-full max-w-md justify-center px-4">
        <x-brand-logo height="h-12" />
    </a>

    <div class="w-full max-w-md px-4">
        <div class="neon-card p-7">
            {{-- Supports both <x-guest-layout> (slot) and @extends (section). --}}
            @hasSection('content')
                @yield('content')
            @else
                {{ $slot ?? '' }}
            @endif
        </div>
        <p class="mt-5 text-center text-xs text-zinc-500">
            <a href="{{ route('public.index') }}" class="inline-flex items-center gap-1 hover:text-neon-400"><x-icon name="arrow-left" class="h-3.5 w-3.5" /> Back to booking site</a>
        </p>
    </div>
</body>
</html>
