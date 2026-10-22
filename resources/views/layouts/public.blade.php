<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/favicon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/public.js'])
</head>
@php($business = \App\Models\Business::primary())
@php($timezone = $business?->timezone ?: config('app.timezone', 'UTC'))
@php($now = now()->timezone($timezone))
<body class="flex min-h-screen flex-col font-sans antialiased">
<nav x-data="{ open: false }" @keydown.escape.window="open = false"
     class="sticky top-0 z-50 border-b border-white/[0.06] bg-ink-950/90 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-6xl items-center gap-2 px-4">
        <x-brand-logo href="{{ route('public.index') }}" height="h-8 sm:h-9" class="shrink-0" />

        {{-- Desktop navigation --}}
        <div class="ml-4 hidden items-center gap-1 text-sm lg:flex">
            <a href="{{ route('public.index') }}#activities" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-zinc-300 transition hover:text-white">
                <x-icon name="gamepad" class="h-4 w-4" /> Activities
            </a>
            <a href="{{ route('public.about') }}" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-zinc-300 transition hover:text-white">
                <x-icon name="info" class="h-4 w-4" /> About
            </a>
            <a href="{{ route('public.contact') }}" class="flex items-center gap-1.5 rounded-lg px-3 py-2 text-zinc-300 transition hover:text-white">
                <x-icon name="phone" class="h-4 w-4" /> Contact
            </a>
            <span class="ml-2 hidden items-center gap-1.5 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-2.5 py-1 text-[11px] font-semibold text-emerald-300 lg:flex">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7
            </span>
            <span class="ml-1 hidden items-center gap-1.5 rounded-full border border-white/10 bg-white/[0.03] px-3 py-1 text-xs text-zinc-400 xl:flex"
                  data-live-clock data-timezone="{{ $timezone }}">
                <x-icon name="clock" class="h-3.5 w-3.5 text-zinc-500" />
                <span data-clock-time>{{ $now->format('g:i A') }}</span>
            </span>
        </div>

        <div class="ml-auto flex items-center gap-2">
            {{-- Desktop account actions --}}
            <div class="hidden items-center gap-2 text-sm lg:flex">
                @auth
                    @if(auth()->user()->isStaffOrAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="btn-ghost !px-3 !py-1.5">
                            <x-icon name="cog" class="h-4 w-4" /> Admin Panel
                        </a>
                    @else
                        <a href="{{ route('customer.dashboard') }}" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-zinc-300 transition hover:text-white">
                            <x-icon name="dashboard" class="h-4 w-4" /> Dashboard
                        </a>
                        <a href="{{ route('public.my-bookings') }}" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-zinc-300 transition hover:text-white">
                            <x-icon name="calendar-days" class="h-4 w-4" /> My Bookings
                        </a>
                    @endif
                    <div class="relative" x-data="{ menu: false }">
                        <button type="button" @click="menu = !menu" class="grid h-9 w-9 place-items-center rounded-full bg-neon-600 text-xs font-bold text-white"
                                aria-label="Account menu" :aria-expanded="menu">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </button>
                        <div x-show="menu" x-cloak @click.outside="menu = false"
                             class="absolute right-0 mt-2 w-48 overflow-hidden rounded-xl border border-white/10 bg-ink-800 py-1 shadow-panel">
                            @php($profileUrl = auth()->user()->isStaffOrAdmin() ? route('profile.edit') : route('customer.profile'))
                            <a href="{{ $profileUrl }}" class="flex items-center gap-2 px-4 py-2.5 text-sm text-zinc-300 hover:bg-white/[0.04]">
                                <x-icon name="user-circle" class="h-4 w-4" /> Profile
                            </a>
                            <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                               class="flex items-center gap-2 px-4 py-2.5 text-sm text-rose-400 hover:bg-white/[0.04]">
                                <x-icon name="logout" class="h-4 w-4" /> Logout
                            </a>
                        </div>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-zinc-300 transition hover:text-white">
                        <x-icon name="lock" class="h-4 w-4" /> Login
                    </a>
                    <a href="{{ route('register') }}" class="btn-neon !px-3 !py-1.5">Sign up</a>
                @endauth
            </div>

            {{-- Compact mobile shortcuts --}}
            @auth
                <a href="{{ auth()->user()->isStaffOrAdmin() ? route('admin.dashboard') : route('public.my-bookings') }}"
                   class="btn-ghost !px-2.5 !py-2 lg:hidden" aria-label="{{ auth()->user()->isStaffOrAdmin() ? 'Admin panel' : 'My bookings' }}">
                    <x-icon name="{{ auth()->user()->isStaffOrAdmin() ? 'cog' : 'calendar-days' }}" class="h-4 w-4" />
                </a>
            @else
                <a href="{{ route('login') }}" class="btn-ghost !px-3 !py-2 text-xs lg:hidden">Login</a>
            @endauth

            <button type="button" @click="open = !open"
                    class="btn-ghost !px-2.5 !py-2 lg:hidden" aria-controls="mobile-nav" aria-label="Toggle navigation"
                    :aria-expanded="open">
                <x-icon name="menu" class="h-5 w-5" x-show="!open" />
                <x-icon name="close" class="h-5 w-5" x-show="open" x-cloak />
            </button>
        </div>
    </div>

    {{-- Mobile / tablet navigation panel --}}
    <div id="mobile-nav" x-show="open" x-cloak
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="lg:hidden">
        <div class="mx-auto max-w-6xl space-y-1 border-t border-white/[0.06] px-4 pb-5 pt-3">
            <a href="{{ route('public.index') }}#activities" @click="open = false"
               class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-zinc-200 transition hover:bg-white/[0.04]">
                <x-icon name="gamepad" class="h-4 w-4 text-neon-400" /> Activities
            </a>
            <a href="{{ route('public.about') }}" @click="open = false"
               class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-zinc-200 transition hover:bg-white/[0.04]">
                <x-icon name="info" class="h-4 w-4 text-neon-400" /> About
            </a>
            <a href="{{ route('public.contact') }}" @click="open = false"
               class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-zinc-200 transition hover:bg-white/[0.04]">
                <x-icon name="phone" class="h-4 w-4 text-neon-400" /> Contact
            </a>
            <span class="mx-3 inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-[11px] font-semibold text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7 · every day
            </span>

            @auth
                <a href="{{ auth()->user()->isStaffOrAdmin() ? route('admin.dashboard') : route('customer.dashboard') }}" @click="open = false"
                   class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-zinc-200 transition hover:bg-white/[0.04]">
                    <x-icon name="dashboard" class="h-4 w-4 text-neon-400" /> Dashboard
                </a>
                <a href="{{ route('public.my-bookings') }}" @click="open = false"
                   class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-zinc-200 transition hover:bg-white/[0.04]">
                    <x-icon name="calendar-days" class="h-4 w-4 text-neon-400" /> My Bookings
                </a>
                <a href="{{ auth()->user()->isStaffOrAdmin() ? route('profile.edit') : route('customer.profile') }}" @click="open = false"
                   class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-zinc-200 transition hover:bg-white/[0.04]">
                    <x-icon name="user-circle" class="h-4 w-4 text-neon-400" /> Profile
                </a>
                <a href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                   class="flex items-center gap-3 rounded-lg px-3 py-3 text-sm font-medium text-rose-300 transition hover:bg-white/[0.04]">
                    <x-icon name="logout" class="h-4 w-4" /> Logout
                </a>
            @else
                <div class="grid grid-cols-2 gap-2 pt-2">
                    <a href="{{ route('login') }}" class="btn-ghost !py-2.5">Login</a>
                    <a href="{{ route('register') }}" class="btn-neon !py-2.5">Sign up</a>
                </div>
            @endauth

            <p class="flex items-center gap-2 px-3 pt-3 text-xs text-zinc-500" data-live-clock data-timezone="{{ $timezone }}">
                <x-icon name="clock" class="h-3.5 w-3.5" />
                <span data-clock-time>{{ $now->format('g:i A') }}</span>
                <span class="text-zinc-600" data-clock-zone></span>
            </p>
        </div>
    </div>
</nav>

<main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
    @include('partials.flash')
    @yield('content')
</main>

@if (empty($hideFooter))
<footer class="mt-12 border-t border-white/[0.06] bg-ink-950/60">
    <div class="mx-auto max-w-6xl px-4 py-12">
        <div class="grid gap-10 sm:grid-cols-2 lg:grid-cols-4">
            {{-- Brand --}}
            <div class="lg:col-span-1">
                <x-brand-logo href="{{ route('public.index') }}" height="h-10" />
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-zinc-500">
                    Snooker, PS5, PC gaming and table tennis — book your game online in seconds and pay at the counter.
                </p>
            </div>

            {{-- Explore --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-zinc-400">Explore</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('public.index') }}#activities" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="gamepad" class="h-4 w-4" /> Activities</a></li>
                    <li><a href="{{ route('public.my-bookings') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="calendar-days" class="h-4 w-4" /> My Bookings</a></li>
                    <li><a href="{{ route('public.contact') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="mail" class="h-4 w-4" /> Get in touch</a></li>
                </ul>
            </div>

            {{-- Company / legal --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-zinc-400">Company</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li><a href="{{ route('public.about') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="info" class="h-4 w-4" /> About Us</a></li>
                    <li><a href="{{ route('public.contact') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="phone" class="h-4 w-4" /> Contact Us</a></li>
                    <li><a href="{{ route('public.terms') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="document" class="h-4 w-4" /> Terms &amp; Conditions</a></li>
                    <li><a href="{{ route('public.privacy') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="shield" class="h-4 w-4" /> Privacy Policy</a></li>
                    <li><a href="{{ route('public.disclaimer') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="warning" class="h-4 w-4" /> Disclaimer</a></li>
                    <li><a href="{{ route('public.cookies') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="lock" class="h-4 w-4" /> Cookie Policy</a></li>
                    <li><a href="{{ route('public.credits') }}" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400"><x-icon name="eye" class="h-4 w-4" /> Photo Credits</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h3 class="text-xs font-bold uppercase tracking-widest text-zinc-400">Contact</h3>
                <ul class="mt-4 space-y-2.5 text-sm">
                    <li>
                        <a href="tel:+923422631057" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400">
                            <x-icon name="phone" class="h-4 w-4" /> +92 342 2631057
                        </a>
                    </li>
                    <li>
                        <a href="mailto:info@levelxgamingarena.com" class="flex items-center gap-2 text-zinc-500 transition hover:text-neon-400">
                            <x-icon name="mail" class="h-4 w-4" /> info@levelxgamingarena.com
                        </a>
                    </li>
                    @if($business?->address)
                        <li class="flex items-start gap-2 text-zinc-500">
                            <x-icon name="map-pin" class="mt-0.5 h-4 w-4 shrink-0" /> {{ $business->address }}
                        </li>
                    @endif
                    <li class="flex items-center gap-2 text-zinc-500">
                        <x-icon name="clock" class="h-4 w-4" />
                        {{ $business ? $business->hoursLabel() : 'Open 24/7' }}
                        <span class="text-zinc-600">· every day</span>
                    </li>
                    <li>
                        <a href="{{ $business?->whatsAppUrl() ?? 'https://wa.me/'.config('site.whatsapp_number') }}"
                           target="_blank" rel="noopener noreferrer"
                           class="flex items-center gap-2 text-zinc-500 transition hover:text-emerald-300">
                            <x-icon name="whatsapp" class="h-4 w-4" /> WhatsApp
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="mt-10 flex flex-col items-center justify-between gap-3 border-t border-white/[0.06] pt-6 text-xs text-zinc-600 sm:flex-row">
            <span>© {{ $now->year }} {{ config('app.name') }} — Gaming &amp; Snooker Booking. All rights reserved.</span>
            <span class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1">
                <a href="{{ route('public.about') }}" class="transition hover:text-zinc-400">About</a>
                <a href="{{ route('public.contact') }}" class="transition hover:text-zinc-400">Contact</a>
                <a href="{{ route('public.terms') }}" class="transition hover:text-zinc-400">Terms</a>
                <a href="{{ route('public.privacy') }}" class="transition hover:text-zinc-400">Privacy</a>
                <a href="{{ route('public.disclaimer') }}" class="transition hover:text-zinc-400">Disclaimer</a>
                <a href="{{ route('public.cookies') }}" class="transition hover:text-zinc-400">Cookies</a>
            </span>
        </div>
    </div>
</footer>
@endif

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

{{-- Scripts load after the DOM so inline page scripts can query elements. --}}
@stack('scripts')
@stack('modals')
</body>
</html>
