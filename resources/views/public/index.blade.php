@extends('layouts.public')

@section('title', config('app.name').' — Book your game')

@section('content')
    {{-- ============ HERO ============ --}}
    <section class="relative mx-auto max-w-6xl overflow-hidden">
        <div class="relative z-10 grid items-center gap-10 lg:grid-cols-2">
        <div class="mx-auto max-w-2xl lg:mx-0">
            <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300 tracking-wide">
                <span class="h-1.5 w-1.5 rounded-full bg-neon-500 animate-pulse-glow"></span>
                Live availability near you
            </span>

            <h1 class="mt-6 font-display text-4xl leading-tight font-black text-white sm:text-6xl">
                Book your <span class="text-neon-500">next match</span>
                <span class="block text-2xl font-normal not-italic text-zinc-400 tracking-wide mt-1" style="letter-spacing:-0.01em">in seconds</span>
            </h1>
            <p class="mt-5 text-base leading-relaxed text-zinc-400 sm:text-lg max-w-xl">
                {{ $business?->name ?? config('app.name') }} — snooker, PS5, PC gaming and table tennis.
                Pick a slot, see the exact price before you confirm, and manage everything from your dashboard.
            </p>

            <div class="mt-8 flex flex-wrap gap-3">
                @guest
                    <a href="{{ route('register') }}" class="btn-neon">Create an account</a>
                    <a href="{{ route('login') }}" class="btn-ghost">I already have one</a>
                @else
                    <a href="{{ route('public.my-bookings') }}" class="btn-neon">My bookings</a>
                    <a href="{{ route('customer.dashboard') }}" class="btn-ghost">Dashboard</a>
                @endguest
            </div>

            <div class="mt-6 flex flex-wrap gap-3 text-xs text-zinc-500">
                <span class="chip"><x-icon name="clock" class="h-3.5 w-3.5" /> {{ $business?->hoursLabel() ?? 'Open 24/7' }}</span>
                <span class="chip">{{ $business?->currency ?? 'PKR' }} pricing</span>
                <span class="chip">Instant price breakdown</span>
            </div>
        </div>

        {{-- Hero visual: neon gaming-zone artwork that loads instantly (inline SVG). --}}
        <div class="relative order-first lg:order-last">
            <div class="neon-card overflow-hidden !p-0">
                <img src="{{ asset('img/hero/gaming-zone.svg') }}"
                     alt="Neon gaming zone with consoles, screens and RGB lighting"
                     width="720" height="760" loading="eager"
                     class="h-64 w-full object-cover sm:h-96 lg:h-[30rem]">
            </div>
            <span class="absolute left-4 top-4 inline-flex items-center gap-1.5 rounded-full border border-white/10 bg-ink-950/70 px-3 py-1 text-[11px] font-semibold text-zinc-200 backdrop-blur">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7 · walk-ins welcome
            </span>
        </div>
        </div>

        {{-- Decorative neon glow --}}
        <div class="pointer-events-none absolute inset-0 -z-10">
            <div class="absolute -top-32 left-1/2 -translate-x-1/2 h-[50rem] w-[70rem] rounded-full bg-neon-500/5 blur-3xl"></div>
            <div class="absolute bottom-0 right-0 h-64 w-64 rounded-full bg-ink-700/60 blur-3xl"></div>
        </div>
    </section>

    {{-- ============ TICKER / HORIZON ============ --}}
    <section class="relative z-10 mx-auto max-w-6xl overflow-hidden border-y border-white/[0.05] pb-10">
        <div class="flex w-max animate-marquee flex-nowrap text-xs font-medium text-zinc-500">
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                <span>Snooker &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-neon-400"></span>
                <span>PS5 &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-neon-400"></span>
                <span>PC Gaming &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-neon-400"></span>
                <span>Table Tennis &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-rose-400"></span>
                <span>Walk-ins welcome &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-neon-400"></span>
                <span>Book online &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                <span>Instant confirmation &nbsp;·&nbsp;</span>
            </div>
            <div class="mx-4 flex h-8 items-center gap-4">
                <span class="h-2 w-2 rounded-full bg-neon-400"></span>
                <span>For everyone &nbsp;·&nbsp;</span>
            </div>
        </div>
    </section>

    {{---- Sections below animate in one by one. They are present in the DOM on load. ----}}

    {{-- ============ FEATURED ============ --}}
    <section class="relative z-10 mx-auto max-w-6xl mt-14">
        <div class="flex items-end justify-between">
            <div>
                <h2 class="font-display text-2xl font-bold text-white">Featured</h2>
                <p class="mt-1 text-sm text-zinc-500">The most popular activities right now</p>
            </div>
            <a href="{{ route('public.index') }}" class="text-sm font-medium text-neon-400 transition hover:text-neon-300">See all →</a>
        </div>

        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($featured as $activity)
                @include('public._card', ['activity' => $activity, 'settings' => $settings, 'featured' => true])
            @endforeach
        </div>
    </section>

    {{---- All category sections (with inline stats). ----}}
    @foreach($categories as $category)
        <section class="relative z-10 mx-auto max-w-6xl mt-16" id="category-{{ $category->slug }}">
            <div class="flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-display text-2xl font-bold text-white">{{ $category->name }}</h2>
                    <p class="mt-1 text-sm text-zinc-500">
                        {{ $category->resources->count() }} {{ Str::plural('activity', $category->resources->count()) }}
                        <span class="hidden sm:inline">&nbsp;·&nbsp;Booked in seconds</span>
                    </p>
                </div>
                @if($category->description)
                    <p class="max-w-2xl text-sm leading-relaxed text-zinc-400">{{ $category->description }}</p>
                @endif
            </div>

            <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($category->resources as $activity)
                    @include('public._card', ['activity' => $activity, 'settings' => $settings, 'featured' => false])
                @endforeach
            </div>
        </section>
    @endforeach

    @if($categories->isEmpty())
        <div class="relative z-10 neon-card mx-auto max-w-6xl mt-14 p-10 text-center text-zinc-400">
            No activities are available for online booking right now.
        </div>
    @endif

    {{---- Footer note brand row (anti-rollback placement) — no structural DOM change. ----}}
    <div class="relative z-10 mx-auto max-w-6xl -mt-2 pb-2 text-center text-[10px] uppercase tracking-widest text-zinc-600">
        <span>{{ config('app.name') }} · Gaming &amp; Snooker Booking</span>
    </div>
@endsection
