@extends('layouts.public')

@section('title', config('app.name').' — Gaming starts here')

@section('content')
<div class="space-y-20 pb-8">
    <section class="relative isolate overflow-hidden rounded-[2rem] border border-white/10 bg-gradient-to-br from-[#191018] via-[#0c0c12] to-[#08080b] px-6 py-12 shadow-2xl sm:px-10 lg:px-14 lg:py-16">
        <div class="pointer-events-none absolute -right-24 -top-24 h-96 w-96 rounded-full bg-neon-600/20 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-32 left-1/4 h-80 w-80 rounded-full bg-fuchsia-700/10 blur-3xl"></div>
        <div class="relative grid items-center gap-12 lg:grid-cols-[1.05fr_.95fr]">
            <div class="max-w-2xl">
                <div class="inline-flex items-center gap-2 rounded-full border border-neon-400/30 bg-neon-500/10 px-4 py-2 text-xs font-bold uppercase tracking-[.18em] text-neon-200">
                    <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-400"></span>
                    The arena is ready
                </div>
                <h1 class="mt-7 font-display text-5xl font-black leading-[1.04] tracking-tight text-white sm:text-6xl xl:text-7xl">
                    Game on.<br><span class="bg-gradient-to-r from-neon-300 via-neon-500 to-fuchsia-400 bg-clip-text text-transparent">Level up.</span>
                </h1>
                <p class="mt-6 max-w-xl text-base leading-8 text-zinc-300 sm:text-lg">
                    Your next great match starts at {{ $business?->name ?? config('app.name') }}.
                    Bring your crew, choose your game and book your time in just a few steps.
                </p>
                <div class="mt-9 flex flex-wrap gap-3">
                    <a href="#activities" class="btn-neon !rounded-full !px-7 !py-3.5">Explore activities <span aria-hidden="true">↗</span></a>
                    @guest
                        <a href="{{ route('register') }}" class="btn-ghost !rounded-full !border-white/20 !px-7 !py-3.5">Create account</a>
                    @else
                        <a href="{{ route('public.my-bookings') }}" class="btn-ghost !rounded-full !border-white/20 !px-7 !py-3.5">View my bookings</a>
                    @endguest
                </div>
                <div class="mt-9 flex flex-wrap items-center gap-x-6 gap-y-3 text-sm text-zinc-400">
                    <span class="inline-flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>{{ $business?->hoursLabel() ?? 'Open 24/7' }}</span>
                    <span>{{ $business?->currency ?? 'PKR' }} pricing</span>
                    <span>Quick online booking</span>
                </div>
            </div>
            <div class="relative mx-auto w-full max-w-xl">
                <div class="absolute -inset-3 rounded-[2rem] bg-gradient-to-br from-neon-500/30 to-fuchsia-500/10 blur-2xl"></div>
                <div class="relative overflow-hidden rounded-[1.6rem] border border-white/15 bg-black/40 shadow-2xl">
                    <img src="{{ asset('img/hero/gaming-zone.svg') }}" alt="Level X gaming arena" width="720" height="760" fetchpriority="high" class="h-[19rem] w-full object-cover sm:h-[25rem] lg:h-[31rem]">
                    <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/90 via-black/30 to-transparent p-6 pt-20">
                        <p class="text-xs font-bold uppercase tracking-[.22em] text-neon-300">Your arena. Your rules.</p>
                        <p class="mt-1 text-xl font-bold text-white">Make every match count.</p>
                    </div>
                </div>
                <div class="absolute -left-4 top-8 hidden rounded-2xl border border-white/10 bg-[#111116]/95 px-4 py-3 shadow-xl sm:block">
                    <p class="text-[10px] font-bold uppercase tracking-widest text-zinc-500">Play your way</p>
                    <p class="mt-1 text-sm font-semibold text-white">Console · PC · Table games</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-3">
        <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-zinc-500">Choose your game</p>
            <p class="mt-2 text-lg font-bold text-white">Something for everyone</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-zinc-500">Simple reservations</p>
            <p class="mt-2 text-lg font-bold text-white">Book in a few clicks</p>
        </div>
        <div class="rounded-2xl border border-white/10 bg-white/[.035] p-5">
            <p class="text-xs font-bold uppercase tracking-[.18em] text-zinc-500">Clear pricing</p>
            <p class="mt-2 text-lg font-bold text-white">Know before you play</p>
        </div>
    </section>

    <section id="activities" class="scroll-mt-24">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-[.22em] text-neon-300">Find your next challenge</p>
                <h2 class="mt-2 font-display text-3xl font-black text-white sm:text-4xl">Choose your game</h2>
                <p class="mt-3 max-w-2xl text-sm leading-6 text-zinc-400 sm:text-base">From friendly competition to all-out gaming sessions, find the activity that fits your mood.</p>
            </div>
            <span class="rounded-full border border-white/10 bg-white/[.04] px-4 py-2 text-xs font-semibold text-zinc-300">Explore the arena</span>
        </div>

        @if($featured->isNotEmpty())
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($featured as $activity)
                    @include('public._card', ['activity' => $activity, 'settings' => $settings, 'featured' => true])
                @endforeach
            </div>
        @endif
    </section>

    @foreach($categories as $category)
        <section class="scroll-mt-24" id="category-{{ $category->slug }}">
            <div class="mb-7 flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[.2em] text-neon-300">Explore category</p>
                    <h2 class="mt-2 font-display text-2xl font-bold text-white sm:text-3xl">{{ $category->name }}</h2>
                    <p class="mt-2 text-sm text-zinc-500">{{ $category->resources->count() }} {{ Str::plural('activity', $category->resources->count()) }} available</p>
                </div>
                @if($category->description)
                    <p class="max-w-xl text-sm leading-6 text-zinc-400">{{ $category->description }}</p>
                @endif
            </div>
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($category->resources as $activity)
                    @include('public._card', ['activity' => $activity, 'settings' => $settings, 'featured' => false])
                @endforeach
            </div>
        </section>
    @endforeach

    @if($categories->isEmpty() && $featured->isEmpty())
        <div class="rounded-3xl border border-white/10 bg-white/[.03] px-6 py-14 text-center">
            <h2 class="text-2xl font-bold text-white">New games are coming soon</h2>
            <p class="mt-3 text-sm text-zinc-400">Please check back shortly for activities and booking availability.</p>
        </div>
    @endif

    <section class="relative overflow-hidden rounded-3xl border border-neon-500/20 bg-gradient-to-r from-neon-950/70 via-[#171019] to-[#101018] px-6 py-10 text-center sm:px-10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,45,85,.13),transparent_65%)]"></div>
        <div class="relative">
            <p class="text-xs font-bold uppercase tracking-[.22em] text-neon-300">Ready when you are</p>
            <h2 class="mt-3 font-display text-3xl font-black text-white sm:text-4xl">Your next session starts here.</h2>
            <p class="mx-auto mt-3 max-w-xl text-sm leading-6 text-zinc-300">Pick an activity, choose a time and get ready to play at Level X.</p>
            <a href="#activities" class="btn-neon mt-7 !rounded-full !px-8 !py-3">Browse activities <span aria-hidden="true">↗</span></a>
        </div>
    </section>
</div>
@endsection
