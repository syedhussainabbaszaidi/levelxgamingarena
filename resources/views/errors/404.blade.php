<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased">
    <div class="grid min-h-screen place-items-center p-6">
        <div class="neon-card w-full max-w-lg p-8 text-center">
            <p class="font-display text-6xl font-black text-neon-500 glow-text">404</p>
            <h1 class="neon-title mt-4 text-lg">Page not found</h1>
            <p class="mt-2 text-sm text-zinc-400">
                The page you were looking for has moved or no longer exists.
            </p>
            <div class="mt-6 flex flex-wrap justify-center gap-2">
                @auth
                    <a href="{{ auth()->user()->isStaffOrAdmin() ? route('admin.dashboard') : route('customer.dashboard') }}" class="btn-neon">Back to dashboard</a>
                @else
                    <a href="{{ route('public.index') }}" class="btn-neon">Back to booking site</a>
                @endauth
                <a href="{{ url()->previous() }}" class="btn-ghost">Go back</a>
            </div>
        </div>
    </div>
</body>
</html>
