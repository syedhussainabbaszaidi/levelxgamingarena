@extends('layouts.public')

@section('title', 'About Us — '.config('app.name'))

@section('content')
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
                <x-icon name="info" class="h-3.5 w-3.5" /> About Us
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7
            </span>
        </div>

        <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">
            About <span class="text-neon-500">{{ config('app.name') }}</span>
        </h1>

        <p class="mt-5 text-base leading-relaxed text-zinc-400">
            {{ $business?->name ?? config('app.name') }} is your home for snooker, pool, PlayStation 5,
            high-end gaming PCs and private VIP rooms. We built this platform so players can check live
            availability, see an exact price breakdown and lock in a slot in seconds — then simply show up
            and play.
        </p>

        <div class="mt-8 grid gap-4 sm:grid-cols-3">
            @foreach([
                ['sparkles', 'Premium setups', 'Well-maintained tables, consoles and PCs across every zone.'],
                ['calendar-days', 'Live availability', 'Real-time slots that update as bookings come in.'],
                ['banknotes', 'Transparent pricing', 'See the full cost before you confirm — no surprises.'],
            ] as [$icon, $title, $copy])
                <div class="neon-card p-5">
                    <span class="grid h-10 w-10 place-items-center rounded-lg bg-neon-500/10 text-neon-300">
                        <x-icon :name="$icon" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-3 text-sm font-semibold text-white">{{ $title }}</h3>
                    <p class="mt-1 text-sm text-zinc-500">{{ $copy }}</p>
                </div>
            @endforeach
        </div>

        <div class="mt-8 neon-card p-6">
            <h2 class="font-display text-lg font-bold text-white">Visit us</h2>
            <dl class="mt-4 space-y-3 text-sm">
                @if($business?->address)
                    <div class="flex items-center gap-3">
                        <x-icon name="map-pin" class="h-4 w-4 text-zinc-500" />
                        <span class="text-zinc-300">{{ $business->address }}</span>
                    </div>
                @endif
                <div class="flex items-center gap-3">
                    <x-icon name="phone" class="h-4 w-4 text-zinc-500" />
                    <a href="tel:+923422631057" class="text-zinc-300 transition hover:text-neon-400">+92 342 2631057</a>
                </div>
                <div class="flex items-center gap-3">
                    <x-icon name="mail" class="h-4 w-4 text-zinc-500" />
                    <a href="mailto:info@levelxgamingarena.com" class="text-zinc-300 transition hover:text-neon-400">info@levelxgamingarena.com</a>
                </div>
                <div class="flex items-center gap-3">
                    <x-icon name="clock" class="h-4 w-4 text-zinc-500" />
                    <span class="text-zinc-300">{{ $business?->hoursLabel() ?? 'Open 24/7' }} · every day</span>
                </div>
                <div class="flex items-center gap-3">
                    <x-icon name="whatsapp" class="h-4 w-4 text-zinc-500" />
                    <a href="{{ $business?->whatsAppUrl() ?? 'https://wa.me/'.config('site.whatsapp_number') }}"
                       target="_blank" rel="noopener noreferrer" class="text-zinc-300 transition hover:text-emerald-300">
                        Chat with us on WhatsApp
                    </a>
                </div>
            </dl>
            <a href="{{ route('public.index') }}" class="btn-neon mt-5">
                <x-icon name="gamepad" class="h-4 w-4" /> Browse activities
            </a>
        </div>
@endsection
