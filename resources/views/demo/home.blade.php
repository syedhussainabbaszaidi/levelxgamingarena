@extends('layouts.public')

@section('title', 'Premium Gaming Zone  |  '.config('app.name'))

@php
    // Presentation toggles (admin-facing display options) — every rate/image/\
    // description block below is gated on these, so /demo stays in sync with\
    // the same admin settings the regular homepage honours.
    $showImage = ($settings['show_activity_image'] ?? '1') === '1';
    $showRate = ($settings['show_hourly_rate'] ?? '1') === '1';
    $showDescription = ($settings['show_activity_description'] ?? '1') === '1';

    $currency = $business?->currency ?? 'PKR';
    $businessName = $business?->name ?? config('app.name');

    // Spotlight card in the hero: the first featured activity.
    $spotlight = $featured->first();

    // How many activity cards are revealed before the "View all activities"\
    // control appears.
    $cardLimit = 6;

    // Live per-activity availability for today, straight from the database.
    $today = now()->copy()->setHour(0)->setMinute(0)->setSecond(0);
    $availability = collect($activities)
        ->mapWithKeys(function (App\Models\Resource $activity) use ($today) {
            return [$activity->id => $activity->isOnlineBookable()
                ? (new App\Services\AvailabilityService)->hasAvailability($activity, $today, $activity->business->slot_minutes ?? 60)
                : false];
        })
        ->all();

    // $packages (one card per zone, priced from live activities),
    // $accountPackages (fixed-price bundles), $gallery and $faqs all come from
    // PageController so the copy and imagery live in exactly one place.
    $hoursLabel = $business?->hoursLabel() ?? 'Open 24/7';
@endphp

@section('content')

<style>
    /* ---- Page-scoped motion for /demo only ---- */
    .dz-grid {
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.035) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.035) 1px, transparent 1px);
        background-size: 44px 44px;
    }
    .dz-grid-soft {
        background-image:
            linear-gradient(rgba(255, 255, 255, 0.025) 1px, transparent 1px),
            linear-gradient(90deg, rgba(255, 255, 255, 0.025) 1px, transparent 1px);
        background-size: 44px 44px;
    }
    .dz-drift {
        animation: dz-drift 12s ease-in-out infinite;
    }
    @keyframes dz-drift {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-14px); }
    }
    .dz-glow-top { animation: dz-glow-top 8s ease-in-out infinite; }
    @keyframes dz-glow-top {
        0%, 100% { opacity: 0.55; transform: translateX(-10%) scale(1); }
        50% { opacity: 0.9; transform: translateX(10%) scale(1.06); }
    }
    .dz-glow-bot { animation: dz-glow-bot 10s ease-in-out infinite; }
    @keyframes dz-glow-bot {
        0%, 100% { opacity: 0.4; transform: translateY(6px) scale(1); }
        50% { opacity: 0.85; transform: translateY(-8px) scale(1.08); }
    }
    @media (prefers-reduced-motion: reduce) {
        .animate-fade-up, .dz-drift, .dz-glow-top, .dz-glow-bot { animation: none !important; }
        html { scroll-behavior: auto !important; }
    }
</style>

<div class="flex flex-col gap-16 sm:gap-20 lg:gap-24">

    {{-- ================================================================== --}}
    {{-- 1 · HERO                                                          --}}
    {{-- ================================================================== --}}
    <section class="relative overflow-hidden rounded-none border-y border-white/10 bg-ink-950 xl:rounded-3xl xl:border">
        {{-- Background layers --}}
        <img src="{{ asset('img/gallery/esports-arena.jpg') }}" alt="" aria-hidden="true"
             onerror="this.style.display='none';"
             class="absolute inset-0 h-full w-full object-cover opacity-[0.14]">
        <div class="absolute inset-0 bg-gradient-to-b from-ink-950/55 via-ink-950/[0.88] to-ink-900"></div>
        <div class="dz-grid absolute inset-0 opacity-70"></div>

        {{-- Ambient glow orbs --}}
        <div class="dz-glow-top pointer-events-none absolute -top-24 left-1/2 h-80 w-[44rem] -translate-x-1/2 rounded-full bg-neon-500/[0.18] blur-3xl"></div>
        <div class="dz-glow-bot pointer-events-none absolute -bottom-32 -right-16 h-72 w-72 rounded-full bg-neon-700/[0.22] blur-3xl"></div>

        <div class="relative grid items-center gap-10 px-6 py-12 sm:px-10 sm:py-16 lg:grid-cols-[1.08fr_0.92fr] lg:px-14 lg:py-[5.5rem]">
            {{-- Copy column --}}
            <div>
                <span class="animate-fade-up inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-4 py-1.5 text-[11px] font-bold uppercase tracking-[0.22em] text-neon-300"
                      style="animation-delay: .05s;">
                    <span class="h-1.5 w-1.5 rounded-full bg-neon-400"></span>
                    {{ $businessName }} · Premium gaming zone
                </span>

                <h1 class="animate-fade-up mt-6 font-display text-4xl font-black uppercase leading-[1.04] tracking-tight text-white sm:text-5xl lg:text-6xl"
                    style="animation-delay: .15s;">
                    Level up your
                    <span class="block bg-gradient-to-r from-neon-400 via-neon-500 to-neon-600 bg-clip-text text-transparent glow-text">
                        game night
                    </span>
                </h1>

                <p class="animate-fade-up mt-5 max-w-xl text-base leading-relaxed text-zinc-400 sm:text-lg"
                   style="animation-delay: .25s;">
                    Snooker, PS5, gaming PCs and private table tennis rooms under one roof —
                    live slots, transparent pricing and instant booking.
                </p>

                <div class="animate-fade-up mt-8 flex flex-wrap items-center gap-4"
                     style="animation-delay: .35s;">
                    <x-whatsapp-button class="!rounded-xl !px-7 !py-3 !text-base" />
                    <a href="#activities"
                       class="btn-ghost !rounded-xl !px-7 !py-3 !text-base">
                        Explore Activities
                    </a>
                </div>

                <div class="animate-fade-up mt-7 flex flex-wrap gap-3 text-xs text-zinc-500"
                     style="animation-delay: .45s;">
                    <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 font-semibold text-emerald-300">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse-glow"></span>
                        Open 24/7
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="clock" class="h-3.5 w-3.5 text-neon-400" />
                        24-hour gaming zone
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="check-circle" class="h-3.5 w-3.5 text-neon-400" />
                        Live availability
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="banknotes" class="h-3.5 w-3.5 text-neon-400" />
                        Pay at the counter
                    </span>
                </div>
            </div>

            {{-- Spotlight visual: today's headline activity --}}
            <div class="animate-fade-up relative mx-auto w-full max-w-md lg:max-w-none"
                 style="animation-delay: .3s;">
                @if($spotlight)
                    <a href="{{ route('public.activity', $spotlight) }}"
                       class="group relative block overflow-hidden rounded-3xl border border-white/10 bg-ink-800/70 shadow-panel">
                        @if($showImage)
                            <img src="{{ $spotlight->imageUrl() }}" alt="{{ $spotlight->name }}"
                                 width="720" height="480" loading="eager"
                                 onerror="this.onerror=null;this.src='{{ $spotlight->fallbackImageUrl() }}';"
                                 class="h-64 w-full object-cover transition duration-700 group-hover:scale-105 sm:h-80">
                            <div class="absolute inset-0 bg-gradient-to-t from-ink-950 via-ink-950/25 to-transparent"></div>
                        @else
                            <div class="grid h-64 place-items-center bg-gradient-to-br from-ink-600 to-ink-900 sm:h-80">
                                <span class="font-display text-6xl font-black text-neon-500/30">{{ strtoupper(substr($spotlight->name, 0, 2)) }}</span>
                            </div>
                        @endif

                        <div class="absolute inset-x-0 bottom-0 flex items-end justify-between gap-3 p-5">
                            <div>
                                <p class="font-display text-lg font-bold text-white">{{ $spotlight->name }}</p>
                                <p class="mt-0.5 text-xs text-zinc-400">
                                    {{ $spotlight->category?->name }} · up to {{ $spotlight->capacity }} players
                                </p>
                            </div>
                            @if($showRate)
                                <x-activity-price :activity="$spotlight" align="right" class="shrink-0" />
                            @endif
                        </div>
                    </a>

                    <span class="dz-drift absolute -left-3 top-5 inline-flex items-center gap-1.5 rounded-full border border-emerald-500/40 bg-ink-950/85 px-3 py-1.5 text-[11px] font-bold text-emerald-300 shadow-panel backdrop-blur">
                        <x-icon name="check-circle" class="h-3.5 w-3.5" />
                        Bookable today
                    </span>
                @else
                    <div class="overflow-hidden rounded-3xl border border-white/10 bg-ink-800/70 shadow-panel">
                        <img src="{{ asset('img/hero/gaming-zone.svg') }}" alt="Gaming zone interior"
                             width="720" height="480" loading="eager"
                             class="h-64 w-full object-cover sm:h-80">
                    </div>
                @endif
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 2 · GAMING ZONE INTRODUCTION + LIVE STATS                         --}}
    {{-- ================================================================== --}}
    <section id="about" class="scroll-mt-24">
        <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-14">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Who we are</p>
                <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                    One floor. Every way to play.
                </h2>
                <p class="mt-5 max-w-xl text-base leading-relaxed text-zinc-400">
                    {{ $businessName }} brings snooker, PS5, gaming PCs and private table tennis rooms
                    together — with live availability, honest pricing and online booking that takes seconds.
                </p>

                <p class="mt-7 text-[11px] font-bold uppercase tracking-[0.22em] text-zinc-500">What makes us different</p>
                <div class="mt-4 grid gap-4 sm:grid-cols-2">
                    <div class="rounded-2xl border border-white/[0.07] bg-ink-800/40 p-4 transition duration-200 hover:border-neon-500/30 hover:bg-ink-800/60">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400 transition duration-200 group-hover:bg-neon-500/20">
                            <x-icon name="check-circle" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <p class="mt-3 text-sm font-semibold text-white">Real live availability</p>
                        <p class="mt-1 text-xs leading-relaxed text-zinc-500">Every open slot is calculated from actual bookings — never a wasted trip.</p>
                    </div>
                    <div class="rounded-2xl border border-white/[0.07] bg-ink-800/40 p-4 transition duration-200 hover:border-neon-500/30 hover:bg-ink-800/60">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400 transition duration-200 group-hover:bg-neon-500/20">
                            <x-icon name="banknotes" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <p class="mt-3 text-sm font-semibold text-white">Price before you commit</p>
                        <p class="mt-1 text-xs leading-relaxed text-zinc-500">A full breakdown — peak windows included — shows before you confirm.</p>
                    </div>
                    <div class="rounded-2xl border border-white/[0.07] bg-ink-800/40 p-4 transition duration-200 hover:border-neon-500/30 hover:bg-ink-800/60">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400 transition duration-200 group-hover:bg-neon-500/20">
                            <x-icon name="tag" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <p class="mt-3 text-sm font-semibold text-white">Reserve free, pay here</p>
                        <p class="mt-1 text-xs leading-relaxed text-zinc-500">No card needed online — settle up at the counter when you arrive.</p>
                    </div>
                    <div class="rounded-2xl border border-white/[0.07] bg-ink-800/40 p-4 transition duration-200 hover:border-neon-500/30 hover:bg-ink-800/60">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400 transition duration-200 group-hover:bg-neon-500/20">
                            <x-icon name="gamepad" class="h-[1.125rem] w-[1.125rem]" />
                        </span>
                        <p class="mt-3 text-sm font-semibold text-white">Four ways to play</p>
                        <p class="mt-1 text-xs leading-relaxed text-zinc-500">From a quiet snooker frame to a table-tennis rally with your squad.</p>
                    </div>
                </div>

                <a href="{{ route('public.about') }}"
                   class="group mt-7 inline-flex items-center gap-2 rounded-xl border border-neon-500/30 bg-neon-500/[0.07] px-5 py-2.5 text-sm font-bold text-neon-300 transition duration-200 hover:-translate-y-0.5 hover:border-neon-500/60 hover:bg-neon-500/15">
                    <x-icon name="info" class="h-4 w-4" /> Learn more about us
                    <x-icon name="arrow-right" class="h-4 w-4 transition duration-200 group-hover:translate-x-0.5" />
                </a>
            </div>

            {{-- Live counters, straight from the database --}}
            <div class="grid grid-cols-2 gap-4 sm:gap-5">
                @foreach($stats as $stat)
                    <div class="relative overflow-hidden rounded-2xl border border-white/[0.07] bg-ink-800/60 p-6 shadow-panel transition duration-200 hover:border-neon-500/20">
                        <span class="absolute inset-x-0 top-0 h-0.5 bg-gradient-to-r from-neon-500/0 via-neon-500/60 to-neon-500/0"></span>
                        <p class="font-display text-4xl font-black text-white sm:text-5xl">
                            <span data-count="{{ $stat['value'] }}">{{ $stat['value'] }}</span>
                        </p>
                        <p class="mt-2 text-[11px] font-bold uppercase tracking-widest text-neon-400">{{ $stat['label'] }}</p>
                        <p class="mt-1 text-xs text-zinc-500">{{ $stat['hint'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 3 · ACTIVITIES — live data, filterable, premium cards              --}}
    {{-- ================================================================== --}}
    <section id="activities" class="scroll-mt-24" x-data="{
        filter: 'all',
        showAll: false,
        activeCat: null,
        setFilter(next) {
            this.filter = next;
            this.activeCat = null;
            this.showAll = false;
        },
        setCategory(slug) {
            this.filter = 'cat-'+slug;
            this.activeCat = slug;
            this.showAll = false;
        }
    }">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Activities</p>
                <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                    Pick your arena
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-zinc-500 sm:text-base">
                    Every activity, live availability — what you see is what you can actually book right now.
                </p>
            </div>
        </div>

        {{-- Zone filter chips (Alpine-driven, no page reload) --}}
        @if($categories->isNotEmpty())
            <div class="mt-8 flex flex-wrap gap-2.5">
                <button type="button" @click="setFilter('all')"
                        class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider transition duration-200"
                        :class="filter === 'all'
                            ? 'border-neon-500/60 bg-neon-500/15 text-neon-200 shadow-neon-sm'
                            : 'border-white/10 bg-white/[0.03] text-zinc-400 hover:border-white/25 hover:text-white'">
                    All · {{ $activities->count() }}
                </button>
                @foreach($categories as $category)
                    <button type="button" @click="setCategory('{{ $category->slug }}')"
                            class="rounded-full border px-4 py-1.5 text-xs font-bold uppercase tracking-wider transition duration-200"
                            :class="activeCat === '{{ $category->slug }}'
                                ? 'border-neon-500/60 bg-neon-500/15 text-neon-200 shadow-neon-sm'
                                : 'border-white/10 bg-white/[0.03] text-zinc-400 hover:border-white/25 hover:text-white'">
                        {{ $category->name }} <span class="text-[10px] font-normal tracking-normal">· {{ $category->resources->count() }}</span>
                    </button>
                @endforeach
            </div>
        @endif

        {{-- Activity grid --}}
        @if($activities->isNotEmpty())
            <x-mobile-slider class="mt-8" desktop="sm:grid-cols-2 xl:grid-cols-3" label="Activities">
                @php($i = 0)
                @foreach($categories as $category)
                    @foreach($category->resources as $activity)
                        @php($idx = $i++)
                        @php($available = $availability[$activity->id] ?? true)
                        <a href="{{ route('public.activity', $activity) }}"
                           class="neon-card neon-card-hover group relative flex h-full flex-col overflow-hidden"
                           x-show="filter === 'cat-{{ $category->slug }}' || (filter === 'all' && (showAll || {{ $idx }} < {{ $cardLimit }}))">

                            {{-- Cover --}}
                            @if($showImage)
                                <div class="relative h-44 overflow-hidden sm:h-48">
                                    <img src="{{ $activity->imageUrl() }}" alt="{{ $activity->name }}" loading="lazy"
                                         onerror="this.onerror=null;this.src='{{ $activity->fallbackImageUrl() }}';"
                                         class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                                    <div class="absolute inset-0 bg-gradient-to-t from-ink-900/90 via-ink-900/20 to-transparent"></div>
                                    <span class="absolute bottom-3 left-4 inline-flex items-center gap-1.5 text-[11px] font-bold uppercase tracking-[0.15em] text-zinc-200">
                                        <span class="h-1.5 w-1.5 rounded-full" style="background-color: {{ $category->color ?: '#ff2d55' }};"></span>
                                        {{ $category->name }}
                                    </span>
                                </div>
                            @else
                                <div class="grid h-44 place-items-center border-b border-white/[0.06] bg-gradient-to-br from-ink-600 to-ink-800 sm:h-48">
                                    <span class="font-display text-5xl font-black text-neon-500/30">{{ strtoupper(substr($activity->name, 0, 2)) }}</span>
                                </div>
                            @endif

                            {{-- Live availability pill --}}
                            <span class="absolute right-3 top-3 z-10">
                                @if(! $activity->isOnlineBookable())
                                    <span class="badge border-white/10 bg-ink-950/80 text-zinc-300 backdrop-blur">
                                        <x-icon name="lock" class="h-3.5 w-3.5" /> Unavailable
                                    </span>
                                @elseif($available)
                                    <span class="badge border-emerald-500/40 bg-emerald-500/15 text-emerald-300 backdrop-blur">
                                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse-glow"></span> Available
                                    </span>
                                @else
                                    <span class="badge border-rose-500/40 bg-rose-500/15 text-rose-300 backdrop-blur">
                                        <x-icon name="x-circle" class="h-3.5 w-3.5" /> Fully booked
                                    </span>
                                @endif
                            </span>

                            {{-- Body --}}
                            <div class="flex flex-1 flex-col p-5">
                                <h3 class="font-display text-lg font-bold tracking-wide text-white transition duration-200 group-hover:text-neon-300">
                                    {{ $activity->name }}
                                    @if($activity->featured)
                                        <x-icon name="star" class="mb-0.5 inline h-4 w-4 text-neon-500" />
                                    @endif
                                </h3>

                                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-zinc-500">
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-icon name="users" class="h-3.5 w-3.5" />
                                        up to {{ $activity->capacity }} players
                                    </span>
                                    <span class="inline-flex items-center gap-1.5">
                                        <x-icon name="clock" class="h-3.5 w-3.5" />
                                        {{ $activity->defaultDuration() }} min sessions
                                    </span>
                                </div>

                                @if($showDescription && $activity->description)
                                    <p class="mt-3 line-clamp-2 text-sm leading-relaxed text-zinc-400">
                                        {{ Str::limit($activity->description, 110) }}
                                    </p>
                                @endif

                                @if($activity->minimumMinutes() || $activity->addonDescription())
                                    <p class="mt-2 text-[11px] text-zinc-500">
                                        @if($activity->minimumMinutes())Minimum {{ $activity->minimumMinutes() }} min @endif
                                        @if($activity->minimumMinutes() && $activity->addonDescription()) · @endif
                                        {{ $activity->addonDescription() }}
                                    </p>
                                @endif

                                <div class="mt-auto flex flex-wrap items-end justify-between gap-3 border-t border-white/[0.06] pt-5">
                                    @if($showRate)
                                        <x-activity-price :activity="$activity" class="min-w-0" />
                                    @else
                                        <p class="text-xs text-zinc-500">{{ $activity->defaultDuration() }} min sessions</p>
                                    @endif

                                    <span class="btn-neon shrink-0 !px-3.5 !py-2 !text-xs">
                                        Book Now <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                                    </span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                @endforeach
            </x-mobile-slider>

            {{-- Show-more controls --}}
            @if($activities->count() > $cardLimit)
                <div class="mt-9 flex flex-wrap items-center justify-center gap-4">
                    <button type="button"
                            x-show="filter === 'all' && !showAll"
                            @click="showAll = true"
                            class="btn-neon !rounded-xl !px-6 !py-3">
                        View all {{ $activities->count() }} activities <x-icon name="chevron-down" class="h-4 w-4" />
                    </button>
                    <button type="button"
                            x-show="filter === 'all' && showAll"
                            @click="showAll = false"
                            class="btn-ghost !rounded-xl !px-6 !py-3">
                        Show fewer
                    </button>
                </div>
            @endif
        @else
            <div class="neon-card mt-8 p-10 text-center text-zinc-400">
                No activities are available for online booking right now.
            </div>
        @endif
    </section>

    {{-- ================================================================== --}}
    {{-- 4 · GAMING EXPERIENCE                                             --}}
    {{-- ================================================================== --}}
    <section id="experience" class="scroll-mt-24 rounded-3xl border border-white/[0.05] bg-ink-800/40 p-6 sm:p-10">
        <div class="max-w-2xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">The experience</p>
            <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                Built for serious play
            </h2>
            <p class="mt-4 text-sm leading-relaxed text-zinc-400 sm:text-base">
                Every station on our floor is set up so the only thing you think about is the game.
            </p>
        </div>

        <x-mobile-slider class="mt-9" desktop="sm:grid-cols-2 lg:grid-cols-3" label="Gaming experience features">
            @foreach([
                ['icon' => 'monitor', 'title' => 'High-performance setups', 'copy' => 'Current-gen consoles and gaming PCs configured for smooth, stutter-free sessions.'],
                ['icon' => 'sliders', 'title' => 'Premium peripherals', 'copy' => 'Pro controllers, mechanical keyboards and headsets ready at every station.'],
                ['icon' => 'sofa', 'title' => 'Comfort-first lounge', 'copy' => 'Spacious seating and a relaxed atmosphere built for long matches.'],
                ['icon' => 'wifi', 'title' => 'High-speed internet', 'copy' => 'A fast, stable connection for online multiplayer, updates and streaming.'],
                ['icon' => 'gamepad', 'title' => 'Multiplayer ready', 'copy' => 'Booths and tables sized for squads — challenge friends or run a mini tournament.'],
                ['icon' => 'crown', 'title' => 'Private rooms', 'copy' => 'Book a table-tennis room or the premium snooker room and keep the session to your crew.'],
            ] as $feature)
                <div class="group rounded-2xl border border-white/[0.07] bg-ink-800/60 p-6 transition duration-200 hover:-translate-y-1 hover:border-neon-500/40 hover:shadow-neon-sm">
                    <span class="grid h-11 w-11 place-items-center rounded-xl border border-neon-500/25 bg-neon-500/10 text-neon-400 transition duration-200 group-hover:border-neon-500/60 group-hover:bg-neon-500/20 group-hover:text-neon-300">
                        <x-icon name="{{ $feature['icon'] }}" class="h-5 w-5" />
                    </span>
                    <h3 class="mt-4 font-display text-base font-bold tracking-wide text-white">{{ $feature['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-zinc-400">{{ $feature['copy'] }}</p>
                </div>
            @endforeach
        </x-mobile-slider>
    </section>

    {{-- ================================================================== --}}
    {{-- 5 · PRICING / PACKAGES — live rates + live peak rules             --}}
    {{-- ================================================================== --}}
    <section id="pricing" class="scroll-mt-24">
        <div class="max-w-2xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Pricing</p>
            <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                Simple packages, honest rates
            </h2>
            <p class="mt-4 text-sm leading-relaxed text-zinc-400 sm:text-base">
                @if($showRate)
                    Priced from what each zone actually charges — see your full breakdown before you confirm.
                @else
                    See the full price before you confirm — no surprises at the counter.
                @endif
            </p>
        </div>

        @if($packages->isNotEmpty())
            <x-mobile-slider class="mt-9" desktop="md:grid-cols-2 xl:grid-cols-3" gap="gap-6" label="Pricing packages">
                @foreach($packages as $package)
                    <div class="relative flex flex-col rounded-3xl border p-6 transition duration-200 sm:p-7"
                         :class="$package['popular']
                            ? 'border-neon-500/50 bg-gradient-to-b from-neon-900/25 via-ink-800 to-ink-800 shadow-neon'
                            : 'border-white/[0.07] bg-ink-800/60 hover:-translate-y-1 hover:border-white/20'">

                        @if($package['popular'])
                            <span class="badge absolute right-5 top-5 border-neon-500/40 bg-neon-500/15 text-neon-200">
                                <x-icon name="star" class="h-3.5 w-3.5" /> Most popular
                            </span>
                        @endif

                        <span class="h-2.5 w-2.5 rounded-full ring-4 ring-white/[0.03]"
                              style="background-color: {{ $package['category']->color ?: '#ff2d55' }};"></span>
                        <h3 class="mt-4 font-display text-xl font-black uppercase tracking-wide text-white">{{ $package['category']->name }}</h3>
                        @if($package['category']->description)
                            <p class="mt-1 text-sm text-zinc-500">{{ $package['category']->description }}</p>
                        @endif

                        <div class="mt-5">
                            @if($showRate)
                                <p class="flex flex-wrap items-end gap-2">
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-zinc-500">From</span>
                                    <span class="font-display text-4xl font-black text-white">{{ number_format($package['from'] ?? 0, 0) }}</span>
                                    <span class="pb-1 text-xs uppercase tracking-wider text-zinc-500">
                                        {{ $currency }}@if(! empty($package['unit']))/{{ $package['unit'] }}@endif
                                    </span>
                                </p>
                            @else
                                <p class="text-sm italic text-zinc-500">See the full price before you confirm.</p>
                            @endif
                        </div>

                        <ul class="mt-6 space-y-2.5 text-sm text-zinc-400">
                            <li class="flex items-start gap-2.5">
                                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                                {{ $package['count'] }} {{ Str::plural('activity', $package['count']) }} in this zone
                            </li>
                            <li class="flex items-start gap-2.5">
                                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                                Up to {{ $package['capacity'] }} players at once
                            </li>
                            <li class="flex items-start gap-2.5">
                                <x-icon name="check" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-400" />
                                {{ $package['duration'] }}-minute session blocks
                            </li>
                            @if($showRate)
                                @foreach($package['peak'] as $peak)
                                    <li class="flex items-start gap-2.5">
                                        <x-icon name="clock" class="mt-0.5 h-4 w-4 shrink-0 text-neon-400" />
                                        {{ $peak->name }} — {{ number_format($peak->hourly_rate, 0) }} {{ $currency }}/hour
                                    </li>
                                @endforeach
                            @endif
                        </ul>

                        <a href="{{ route('public.activity', $package['first']) }}"
                           class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-bold transition duration-200"
                           :class="$package['popular']
                                ? 'bg-neon-600 text-white shadow-neon-sm hover:bg-neon-500 hover:shadow-neon'
                                : 'btn-ghost'">
                            Book this zone <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                @endforeach
            </x-mobile-slider>

            {{-- Real peak windows pulled from the pricing rules --}}
            @if($showRate && $packages->pluck('peak')->flatten()->isNotEmpty())
                <div class="neon-panel mt-6 flex flex-wrap items-center gap-x-5 gap-y-3 !p-5">
                    <span class="inline-flex items-center gap-2 text-xs font-bold uppercase tracking-widest text-neon-400">
                        <x-icon name="clock" class="h-4 w-4" /> Live offers &amp; rate windows
                    </span>
                    @foreach($packages->pluck('peak')->flatten()->unique('id')->values() as $peak)
                        <span class="chip">
                            {{ $peak->name }}
                            <span class="text-zinc-600">· {{ substr((string) $peak->start_time, 0, 5) }}–{{ substr((string) $peak->end_time, 0, 5) }}</span>
                            <span class="text-white">· {{ number_format($peak->hourly_rate, 0) }} {{ $currency }}/hour</span>
                        </span>
                    @endforeach
                    <span class="text-xs text-zinc-500">Always shown in your breakdown before you confirm.</span>
                </div>
            @endif
        @else
            <div class="neon-card mt-9 p-10 text-center text-zinc-400">
                Packages appear here as soon as activities go live.
            </div>
        @endif
    </section>

    {{-- ================================================================== --}}
    {{-- 5b · PREPAID ACCOUNT PACKAGES — fixed prices from the database     --}}
    {{-- ================================================================== --}}
    @if($accountPackages->isNotEmpty())
        <section id="packages" class="scroll-mt-24">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div class="max-w-2xl">
                    <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Account packages</p>
                    <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                        Play more, pay less
                    </h2>
                    <p class="mt-4 text-sm leading-relaxed text-zinc-400 sm:text-base">
                        Prepaid play-hour bundles for regulars. Open an account once, then top up with the package that suits you.
                    </p>
                </div>
                <a href="{{ route('public.contact') }}" class="btn-ghost !rounded-xl">
                    <x-icon name="phone" class="h-4 w-4" /> Ask at the counter
                </a>
            </div>

            <x-mobile-slider class="mt-9" desktop="sm:grid-cols-2 xl:grid-cols-3" label="Account packages">
                @foreach($accountPackages as $package)
                    <div class="relative flex flex-col rounded-3xl border p-6 transition duration-200 hover:-translate-y-1 hover:border-neon-500/40 hover:shadow-neon-sm border-white/[0.07] bg-ink-800/60">
                        @if(! $package->package_minutes)
                            <span class="badge absolute right-5 top-5 border-neon-500/30 bg-neon-500/10 text-neon-300">
                                <x-icon name="tag" class="h-3.5 w-3.5" /> One-time
                            </span>
                        @elseif($package->hasDiscount())
                            <span class="badge absolute right-5 top-5 border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                                {{ $package->discountPercent() }}% off
                            </span>
                        @endif

                        <span class="chip w-fit">{{ $package->category?->name }}</span>
                        <h3 class="mt-4 font-display text-2xl font-black uppercase tracking-wide text-white">{{ $package->name }}</h3>
                        <p class="mt-2 line-clamp-3 text-sm leading-relaxed text-zinc-400">{{ $package->description }}</p>

                        <div class="mt-5 border-t border-white/[0.06] pt-5">
                            @if($showRate)
                                <x-activity-price :activity="$package" />
                            @else
                                <p class="text-sm italic text-zinc-500">Ask at the counter for the current package price.</p>
                            @endif
                        </div>

                        <a href="{{ route('public.activity', $package) }}"
                           class="btn-ghost mt-6 w-full !py-2.5 text-sm">
                            Package details <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                @endforeach
            </x-mobile-slider>

            <p class="mt-5 flex items-start gap-2 text-xs text-zinc-500">
                <x-icon name="info" class="mt-0.5 h-3.5 w-3.5 shrink-0" />
                Account packages are activated at the counter — no online payment is taken.
            </p>
        </section>
    @endif

    {{-- ================================================================== --}}
    {{-- 6 · WHY CHOOSE US                                                 --}}
    {{-- ================================================================== --}}
    <section id="why-us" class="scroll-mt-24">
        <div class="max-w-2xl">
            <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Why choose us</p>
            <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                Why players keep coming back
            </h2>
        </div>

        <x-mobile-slider class="mt-9" desktop="sm:grid-cols-2 lg:grid-cols-3" label="Why choose us">
            @foreach([
                ['icon' => 'check-circle', 'title' => 'Availability you can trust', 'copy' => 'Slots come straight from the live booking calendar — if it shows open, it is open.'],
                ['icon' => 'banknotes', 'title' => 'Transparent pricing', 'copy' => 'The total, including peak windows, is laid out before anything is locked in.'],
                ['icon' => 'calendar-days', 'title' => 'Book in seconds', 'copy' => 'Pick an activity, pick a slot, confirm — the whole flow takes under a minute.'],
                ['icon' => 'tag', 'title' => 'Pay when you play', 'copy' => 'Reserving costs nothing online; you settle at the counter when you arrive.'],
                ['icon' => 'dashboard', 'title' => 'Everything in one place', 'copy' => 'Past and upcoming sessions, statuses and details live in your dashboard.'],
                ['icon' => 'users', 'title' => 'Walk-ins welcome', 'copy' => 'No account? No problem — our counter team books you in just the same.'],
            ] as $benefit)
                <div class="group relative overflow-hidden rounded-2xl border border-white/[0.07] bg-ink-800/50 p-6 transition duration-200 hover:-translate-y-1 hover:border-neon-500/40 hover:shadow-neon-sm">
                    <span class="pointer-events-none absolute -right-6 -top-6 h-24 w-24 rounded-full bg-neon-500/10 blur-2xl transition duration-200 group-hover:bg-neon-500/20"></span>
                    <span class="relative grid h-11 w-11 place-items-center rounded-xl bg-neon-500/10 text-neon-400 transition duration-200 group-hover:text-neon-300">
                        <x-icon name="{{ $benefit['icon'] }}" class="h-5 w-5" />
                    </span>
                    <h3 class="relative mt-4 font-display text-base font-bold tracking-wide text-white">{{ $benefit['title'] }}</h3>
                    <p class="relative mt-2 text-sm leading-relaxed text-zinc-400">{{ $benefit['copy'] }}</p>
                </div>
            @endforeach
        </x-mobile-slider>
    </section>

    {{-- ================================================================== --}}
    {{-- 7 · GALLERY — responsive grid + Alpine lightbox                    --}}
    {{-- ================================================================== --}}
    <section id="gallery" class="scroll-mt-24">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Gallery</p>
                <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                    Inside the arena
                </h2>
                <p class="mt-3 max-w-2xl text-sm leading-relaxed text-zinc-500 sm:text-base">
                    A look around the floor — tap any image to enlarge it.
                </p>
            </div>
        </div>

        @if($gallery->isNotEmpty())
            <x-mobile-slider class="mt-8" desktop="sm:grid-cols-3 lg:grid-cols-4" gap="gap-3 sm:gap-4" label="Gallery" x-data>
                @foreach($gallery as $item)
                    <button type="button"
                            @click="$dispatch('dz-open', {{ $loop->index }})"
                            aria-label="View {{ $item['label'] }}"
                            class="group relative aspect-square overflow-hidden rounded-2xl border border-white/[0.07] bg-ink-800 transition duration-200 hover:border-neon-500/50 hover:shadow-neon-sm {{ $loop->first ? 'col-span-2 row-span-2' : '' }}">
                        <img src="{{ $item['src'] }}" alt="{{ $item['label'] }}" loading="lazy"
                             onerror="this.style.display='none';"
                             class="h-full w-full object-cover transition duration-500 group-hover:scale-110">
                        <span class="absolute right-3 top-3 grid h-8 w-8 place-items-center rounded-full border border-white/15 bg-ink-950/60 text-zinc-200 shadow-panel backdrop-blur transition duration-200 group-hover:border-neon-500/60 group-hover:text-neon-300">
                            <x-icon name="eye" class="h-4 w-4" />
                        </span>
                    </button>
                @endforeach
            </x-mobile-slider>
        @endif
    </section>

    {{-- ================================================================== --}}
    {{-- 8 · BOOKING CTA — three steps + strong closing band               --}}
    {{-- ================================================================== --}}
    <section id="book" class="relative overflow-hidden rounded-none border-y border-neon-500/20 bg-ink-950 xl:rounded-3xl xl:border">
        <img src="{{ asset('img/hero/gaming-zone.svg') }}" alt="" aria-hidden="true"
             onerror="this.style.display='none';"
             class="absolute inset-0 h-full w-full object-cover opacity-[0.12]">
        <div class="absolute inset-0 bg-gradient-to-br from-neon-900/70 via-ink-900/90 to-ink-950"></div>
        <div class="dz-glow-top pointer-events-none absolute -top-24 left-1/2 h-80 w-[44rem] -translate-x-1/2 rounded-full bg-neon-500/[0.18] blur-3xl"></div>
        <div class="dz-glow-bot pointer-events-none absolute -bottom-28 -right-16 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

        <div class="relative px-6 py-12 sm:px-10 sm:py-16">
            <h2 class="font-display text-xl font-black uppercase tracking-tight text-white sm:text-2xl">
                From screen to table in three steps
            </h2>

            <div class="mt-8 grid gap-6 sm:grid-cols-3 sm:gap-8">
                @foreach([
                    ['n' => '01', 'icon' => 'gamepad', 'title' => 'Pick your activity', 'copy' => 'Browse every table, booth and room with capacity, session length and rate up front.'],
                    ['n' => '02', 'icon' => 'calendar-days', 'title' => 'Choose your slot', 'copy' => 'Live availability shows exactly which times are open today, with the full price first.'],
                    ['n' => '03', 'icon' => 'banknotes', 'title' => 'Show up and play', 'copy' => 'Pay at the counter when you arrive and track the session from your dashboard.'],
                ] as $step)
                    <div class="relative">
                        <span class="pointer-events-none absolute -top-5 left-0 font-display text-6xl font-black text-white/[0.05] sm:text-8xl" aria-hidden="true">{{ $step['n'] }}</span>
                        <span class="relative grid h-11 w-11 place-items-center rounded-xl border border-neon-500/30 bg-neon-500/10 text-neon-400 transition duration-200 group-hover:border-neon-500/60 group-hover:text-neon-300">
                            <x-icon name="{{ $step['icon'] }}" class="h-5 w-5" />
                        </span>
                        <h3 class="relative mt-4 text-base font-semibold text-white">{{ $step['title'] }}</h3>
                        <p class="relative mt-1.5 text-sm leading-relaxed text-zinc-400">{{ $step['copy'] }}</p>
                    </div>
                @endforeach
            </div>

            <div class="mt-12 border-t border-white/10 pt-10 sm:mt-14">
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-300">Ready to play?</p>
                <div class="mt-4 flex flex-col items-start gap-6 lg:flex-row lg:items-center lg:justify-between">
                    <div class="max-w-2xl">
                        <h2 class="font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                            Ready when you are
                        </h2>
                        <p class="mt-3 text-sm leading-relaxed text-neon-100/80 sm:text-base">
                            Book your gaming session and experience the ultimate gaming environment —
                            live slots, instant confirmation, nothing in the way.
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-4">
                        <a href="#activities"
                           class="inline-flex items-center gap-2 rounded-xl border border-white/25 px-7 py-3 text-sm font-bold text-white transition duration-200 hover:-translate-y-0.5 hover:border-neon-400/60 hover:bg-white/10">
                            View Activities
                        </a>
                        <a href="{{ route('public.contact') }}"
                           class="inline-flex items-center gap-2 rounded-xl border border-white/25 px-7 py-3 text-sm font-bold text-white transition duration-200 hover:-translate-y-0.5 hover:border-neon-400/60 hover:bg-white/10">
                            Contact Us <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 9 · FAQ — animated accordion                                       --}}
    {{-- ================================================================== --}}
    <section id="faq" class="scroll-mt-24">
        <div class="grid gap-8 lg:grid-cols-[0.85fr_1.15fr] lg:gap-12">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">FAQ</p>
                <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                    Good to know
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-zinc-400">
                    Everything about booking, pricing and payments — answered with how the system actually works.
                </p>
                <a href="{{ route('public.contact') }}"
                   class="mt-6 inline-flex items-center gap-2 text-sm font-semibold text-neon-400 transition hover:text-neon-300">
                    Still got a question? Contact us <x-icon name="arrow-right" class="h-4 w-4" />
                </a>
            </div>

            <div class="space-y-3" x-data="{ open: 0 }">
                @foreach($faqs as $index => $faq)
                    <div class="rounded-2xl border transition duration-200"
                         :class="open === {{ $index }} ? 'border-neon-500/40 bg-ink-800/70' : 'border-white/[0.07] bg-ink-800/40'">
                        <button type="button"
                                @click="open = open === {{ $index }} ? -1 : {{ $index }}"
                                :aria-expanded="open === {{ $index }}"
                                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left">
                            <span class="text-sm font-semibold text-white sm:text-base">{{ $faq['q'] }}</span>
                            <span class="inline-flex shrink-0 transition duration-200"
                                  :class="open === {{ $index }} ? 'rotate-180 text-neon-400' : 'text-zinc-500'">
                                <x-icon name="chevron-down" class="h-4 w-4" />
                            </span>
                        </button>
                        <div class="grid transition-[grid-template-rows] duration-300 ease-out"
                             :class="open === {{ $index }} ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'">
                            <div class="overflow-hidden">
                                <p class="px-5 pb-5 text-sm leading-relaxed text-zinc-400">{{ $faq['a'] }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ================================================================== --}}
    {{-- 10 · LOCATION / CONTACT                                           --}}
    {{-- ================================================================== --}}
    <section id="visit" class="scroll-mt-24">
        <div class="grid gap-8 lg:grid-cols-2 lg:gap-12">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.22em] text-neon-500">Visit us</p>
                <h2 class="mt-3 font-display text-3xl font-black uppercase tracking-tight text-white sm:text-4xl">
                    Find the zone
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-zinc-400">
                    Drop in for a session or book your slot first — either way, we'll be ready for you.
                </p>

                <div class="mt-7 grid gap-4 sm:grid-cols-2">
                    @if($business?->address)
                        <div class="rounded-2xl border border-white/[0.07] bg-ink-800/50 p-5 transition duration-200 hover:border-neon-500/30">
                            <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400">
                                <x-icon name="map-pin" class="h-4 w-4" />
                            </span>
                            <p class="mt-3 text-[11px] font-bold uppercase tracking-widest text-zinc-500">Address</p>
                            <p class="mt-1 text-sm font-medium text-white">{{ $business->address }}</p>
                        </div>
                    @endif

                    <div class="rounded-2xl border border-white/[0.07] bg-ink-800/50 p-5 transition duration-200 hover:border-neon-500/30">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400">
                            <x-icon name="clock" class="h-4 w-4" />
                        </span>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-widest text-zinc-500">Opening hours</p>
                        <p class="mt-1 text-sm font-medium text-white">{{ $hoursLabel }}</p>
                        <p class="mt-1 text-xs text-zinc-500">Walk in any time, day or night.</p>
                    </div>

                    <a href="tel:+923422631057"
                       class="rounded-2xl border border-white/[0.07] bg-ink-800/50 p-5 transition duration-200 hover:-translate-y-1 hover:border-neon-500/40 hover:shadow-neon-sm">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400">
                            <x-icon name="phone" class="h-4 w-4" />
                        </span>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-widest text-zinc-500">Phone</p>
                        <p class="mt-1 text-sm font-medium text-white">+92 342 2631057</p>
                    </a>

                    <a href="mailto:info@levelxgamingarena.com"
                       class="rounded-2xl border border-white/[0.07] bg-ink-800/50 p-5 transition duration-200 hover:-translate-y-1 hover:border-neon-500/40 hover:shadow-neon-sm">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-neon-500/10 text-neon-400">
                            <x-icon name="mail" class="h-4 w-4" />
                        </span>
                        <p class="mt-3 text-[11px] font-bold uppercase tracking-widest text-zinc-500">Email</p>
                        <p class="mt-1 break-all text-sm font-medium text-white">info@levelxgamingarena.com</p>
                    </a>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    @if($business?->address)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($business->address) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="btn-neon !rounded-xl !px-6 !py-3">
                            <x-icon name="map-pin" class="h-4 w-4" /> Get directions
                        </a>
                    @endif
                    <a href="{{ route('public.contact') }}"
                       class="btn-ghost !rounded-xl !px-6 !py-3">
                        <x-icon name="phone" class="h-4 w-4" /> Contact us
                    </a>
                </div>
            </div>

            <div class="relative min-h-[22rem] overflow-hidden rounded-3xl border border-white/[0.07] bg-ink-800/60 shadow-panel transition duration-200">
                <div class="dz-grid-soft absolute inset-0 opacity-70"></div>
                <div class="absolute inset-0 bg-gradient-to-br from-neon-900/20 via-transparent to-transparent"></div>
                <div class="pointer-events-none absolute left-1/2 top-1/2 h-40 w-40 -translate-x-1/2 -translate-y-1/2 rounded-full bg-neon-500/15 blur-3xl"></div>

                <div class="relative flex h-full flex-col items-center justify-center gap-4 p-8 text-center">
                    <span class="grid h-16 w-16 place-items-center rounded-2xl border border-neon-500/40 bg-neon-500/15 text-neon-300 shadow-neon-sm">
                        <x-icon name="map-pin" class="h-8 w-8" />
                    </span>
                    @if($business?->address)
                        <p class="max-w-xs font-display text-lg font-bold uppercase tracking-wide text-white">
                            {{ $business->address }}
                        </p>
                    @endif
                    <p class="text-sm text-zinc-400">Open 24/7 · every day of the week</p>
                    @if($business?->address)
                        <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode($business->address) }}"
                           target="_blank" rel="noopener noreferrer"
                           class="mt-1 inline-flex items-center gap-1.5 text-sm font-semibold text-neon-400 transition hover:text-neon-300">
                            Open in Google Maps <x-icon name="arrow-right" class="h-4 w-4" />
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </section>

</div>

{{-- ================================================================== --}}
{{-- 11 · MINIMAL FOOTER NAVIGATION                                   --}}
{{-- ================================================================== --}}
<footer class="border-t border-white/[0.06] pt-10">
    <div class="flex flex-col items-center gap-5 text-center">
        <x-brand-logo href="{{ route('public.index') }}" height="h-10" />

        <p class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold text-emerald-300">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7 · every day
        </p>

        <nav aria-label="Footer" class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-xs text-zinc-500">
            <a href="{{ route('public.about') }}" class="transition duration-200 hover:text-neon-400">About Us</a>
            <span class="hidden text-zinc-700 sm:inline" aria-hidden="true">|</span>
            <a href="{{ route('public.contact') }}" class="transition duration-200 hover:text-neon-400">Contact Us</a>
            <span class="hidden text-zinc-700 sm:inline" aria-hidden="true">|</span>
            <a href="{{ route('public.privacy') }}" class="transition duration-200 hover:text-neon-400">Privacy Policy</a>
            <span class="hidden text-zinc-700 sm:inline" aria-hidden="true">|</span>
            <a href="{{ route('public.disclaimer') }}" class="transition duration-200 hover:text-neon-400">Disclaimer</a>
            <span class="hidden text-zinc-700 sm:inline" aria-hidden="true">|</span>
            <a href="{{ route('public.terms') }}" class="transition duration-200 hover:text-neon-400">Terms &amp; Conditions</a>
            <span class="hidden text-zinc-700 sm:inline" aria-hidden="true">|</span>
            <a href="{{ route('public.cookies') }}" class="transition duration-200 hover:text-neon-400">Cookie Policy</a>
            <span class="hidden text-zinc-700 sm:inline" aria-hidden="true">|</span>
            <a href="{{ route('public.credits') }}" class="transition duration-200 hover:text-neon-400">Photo Credits</a>
        </nav>

        <p class="text-[11px] text-zinc-600">
            © {{ now()->year }} {{ config('app.name') }} — Gaming &amp; Snooker Booking. All rights reserved.
        </p>
    </div>
</footer>

{{-- ================================================================== --}}
{{-- LIGHTBOX (Alpine, zero JS)                                         --}}
{{-- ================================================================== --}}
@push('modals')
    <div x-data="{
            open: false,
            i: 0,
            items: @js($gallery),
            next() { this.items.length && (this.i = (this.i + 1) % this.items.length); },
            prev() { this.items.length && (this.i = (this.i - 1 + this.items.length) % this.items.length); },
            onKey(e) {
                if (!this.open) return;
                if (e.key === 'Escape') this.open = false;
                else if (e.key === 'ArrowRight') this.next();
                else if (e.key === 'ArrowLeft') this.prev();
            }
         }"
         @dz-open.window="i = $event.detail; open = true"
         @keydown.window="onKey($event)"
         x-effect="document.body.style.overflow = open ? 'hidden' : ''"
         x-cloak>

        <div x-show="open" x-transition.opacity.duration.200ms
             class="fixed inset-0 z-50 flex items-center justify-center bg-ink-950/[0.92] p-4 backdrop-blur-sm sm:p-6"
             role="dialog" aria-modal="true" aria-label="Gallery image"
             @click.self="open = false">

            <button type="button" @click="open = false" aria-label="Close gallery"
                    class="absolute right-4 top-4 grid h-10 w-10 place-items-center rounded-full border border-white/15 bg-ink-900/80 text-zinc-300 transition hover:border-neon-500/60 hover:text-white">
                <x-icon name="close" class="h-5 w-5" />
            </button>

            @if($gallery->count() > 1)
                <button type="button" @click="prev()" aria-label="Previous image"
                        class="absolute left-3 z-10 grid h-11 w-11 place-items-center rounded-full border border-white/15 bg-ink-900/80 text-zinc-300 transition hover:border-neon-500/60 hover:text-white sm:left-6">
                    <x-icon name="arrow-left" class="h-5 w-5" />
                </button>
                <button type="button" @click="next()" aria-label="Next image"
                        class="absolute right-3 z-10 grid h-11 w-11 place-items-center rounded-full border border-white/15 bg-ink-900/80 text-zinc-300 transition hover:border-neon-500/60 hover:text-white sm:right-6">
                    <x-icon name="arrow-right" class="h-5 w-5" />
                </button>
            @endif

            <figure class="max-w-4xl" @click.stop>
                <img :src="items[i].src" :alt="items[i].label"
                     class="max-h-[70vh] w-auto max-w-full rounded-2xl border border-white/10 object-contain shadow-panel">
                <figcaption class="mt-4 flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-center">
                    <span class="font-display text-sm font-bold text-white" x-text="items[i].label"></span>
                    <span class="text-xs text-zinc-500" x-text="items[i].sub"></span>
                    <span class="text-xs text-zinc-600" x-text="(i + 1) + ' / ' + items.length"></span>
                </figcaption>
            </figure>
        </div>
    </div>
@endpush

{{-- ================================================================== --}}
{{-- STAT COUNT-UP (plain JS + IntersectionObserver, no dependencies)   --}}
{{-- ================================================================== --}}
@push('scripts')
<script>
    (function () {
        var nodes = document.querySelectorAll('[data-count]');
        if (!nodes.length) return;
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        if (!('IntersectionObserver' in window)) return;

        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                observer.unobserve(el);

                var target = parseInt(el.getAttribute('data-count'), 10) || 0;
                var start = null;
                var duration = 1200;

                function tick(now) {
                    if (start === null) start = now;
                    var p = Math.min(1, (now - start) / duration);
                    var eased = 1 - Math.pow(1 - p, 3);
                    el.textContent = Math.round(target * eased);
                    if (p < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });
        }, { threshold: 0.4 });

        nodes.forEach(function (node) { observer.observe(node); });
    })();
</script>
@endpush

@endsection
