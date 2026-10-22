@extends('layouts.public')

@section('title', 'Cookie Policy — '.config('app.name'))

@section('content')
        <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
            <x-icon name="lock" class="h-3.5 w-3.5" /> Cookie Policy
        </span>

        <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">Cookie Policy</h1>
        <p class="mt-2 text-xs text-zinc-500">Last updated {{ $updatedAt }}</p>

        <div class="mt-6 grid gap-6 text-sm leading-relaxed text-zinc-400 lg:grid-cols-2">
            <p>
                Cookies are small text files stored on your device. {{ config('app.name') }} uses cookies and
                similar technologies to keep the site working and to remember your preferences.
            </p>

            <div>
                <h2 class="font-display text-lg font-bold text-white">How we use cookies</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5">
                    <li><span class="text-zinc-300">Essential cookies</span> — required to sign in, keep you signed in and protect forms against cross-site request forgery.</li>
                    <li><span class="text-zinc-300">Preference cookies</span> — remember choices such as the date you last filtered your availability.</li>
                    <li><span class="text-zinc-300">Performance cookies</span> — help us understand how the booking pages are used so we can improve them.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Managing cookies</h2>
                <p class="mt-3">
                    You can block or delete cookies through your browser settings. Blocking essential cookies may
                    prevent you from signing in or completing a booking.
                </p>
            </div>

            <div class="neon-card p-5">
                <h2 class="text-sm font-semibold text-white">Questions</h2>
                <p class="mt-2">
                    Contact us at
                    <a href="mailto:info@levelxgamingarena.com" class="text-neon-400 hover:text-neon-300">info@levelxgamingarena.com</a>
                    if you have any questions about our use of cookies.
                </p>
            </div>
        </div>
@endsection
