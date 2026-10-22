@extends('layouts.public')

@section('title', 'Contact Us — '.config('app.name'))

@section('content')
        <div class="flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
                <x-icon name="phone" class="h-3.5 w-3.5" /> Contact Us
            </span>
            <span class="inline-flex items-center gap-2 rounded-full border border-emerald-500/30 bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-300">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7
            </span>
        </div>

        <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">Get in touch</h1>
        <p class="mt-3 max-w-2xl text-base leading-relaxed text-zinc-400">
            Questions about a booking, a tournament or a private event? Send us a message or reach us directly —
            we're happy to help.
        </p>

        <div class="mt-8 grid gap-6 lg:grid-cols-3">
            {{-- Contact details --}}
            <div class="space-y-4 lg:col-span-1">
                <a href="{{ $business?->whatsAppUrl() ?? 'https://wa.me/'.config('site.whatsapp_number') }}"
                   target="_blank" rel="noopener noreferrer"
                   class="neon-card neon-card-hover flex items-start gap-3 border-emerald-500/30 p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-emerald-500/10 text-emerald-300">
                        <x-icon name="whatsapp" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-xs uppercase tracking-wider text-zinc-500">WhatsApp · fastest reply</span>
                        <span class="mt-0.5 block font-medium text-white">Chat with our team</span>
                    </span>
                </a>

                <a href="tel:+923422631057" class="neon-card neon-card-hover flex items-start gap-3 p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-neon-500/10 text-neon-300">
                        <x-icon name="phone" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-xs uppercase tracking-wider text-zinc-500">Phone</span>
                        <span class="mt-0.5 block font-medium text-white">+92 342 2631057</span>
                    </span>
                </a>

                <a href="mailto:info@levelxgamingarena.com" class="neon-card neon-card-hover flex items-start gap-3 p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-neon-500/10 text-neon-300">
                        <x-icon name="mail" class="h-5 w-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block text-xs uppercase tracking-wider text-zinc-500">Email</span>
                        <span class="mt-0.5 block break-all font-medium text-white">info@levelxgamingarena.com</span>
                    </span>
                </a>

                @if($business?->address)
                    <div class="neon-card flex items-start gap-3 p-5">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-neon-500/10 text-neon-300">
                            <x-icon name="map-pin" class="h-5 w-5" />
                        </span>
                        <span>
                            <span class="block text-xs uppercase tracking-wider text-zinc-500">Address</span>
                            <span class="mt-0.5 block font-medium text-white">{{ $business->address }}</span>
                        </span>
                    </div>
                @endif

                <div class="neon-card flex items-start gap-3 p-5">
                    <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-emerald-500/10 text-emerald-300">
                        <x-icon name="clock" class="h-5 w-5" />
                    </span>
                    <span>
                        <span class="block text-xs uppercase tracking-wider text-zinc-500">Opening hours</span>
                        <span class="mt-0.5 block font-medium text-white">{{ $business?->hoursLabel() ?? 'Open 24/7' }}</span>
                        <span class="block text-xs text-zinc-500">Every day — walk in any time.</span>
                    </span>
                </div>
            </div>

            {{-- Message form --}}
            <div class="neon-card p-5 sm:p-6 lg:col-span-2">
                <h2 class="text-sm font-semibold text-white">Send us a message</h2>
                <p class="mt-1 text-xs text-zinc-500">Our team is on the floor 24/7 — WhatsApp gets you the fastest reply.</p>

                <form method="POST" action="{{ route('public.contact.send') }}" class="mt-5 space-y-4">
                    @csrf

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="neon-label" for="name">Name</label>
                            <input id="name" name="name" value="{{ old('name', auth()->user()->name ?? '') }}"
                                   class="input-neon @error('name') input-invalid @enderror" placeholder="Your name" required>
                            @error('name')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="neon-label" for="email">Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email', auth()->user()->email ?? '') }}"
                                   class="input-neon @error('email') input-invalid @enderror" placeholder="you@example.com" required>
                            @error('email')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="neon-label" for="phone">Phone (optional)</label>
                            <input id="phone" name="phone" value="{{ old('phone') }}" class="input-neon @error('phone') input-invalid @enderror" placeholder="+92 300 0000000">
                            @error('phone')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="neon-label" for="subject">Subject (optional)</label>
                            <input id="subject" name="subject" value="{{ old('subject') }}" class="input-neon @error('subject') input-invalid @enderror" placeholder="Booking enquiry">
                            @error('subject')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label class="neon-label" for="message">Message</label>
                        <textarea id="message" name="message" rows="5" class="input-neon @error('message') input-invalid @enderror"
                                  placeholder="How can we help?" required>{{ old('message') }}</textarea>
                        @error('message')<p class="mt-1 text-xs text-rose-300">{{ $message }}</p>@enderror
                    </div>

                    <button class="btn-neon w-full !py-2.5">
                        <x-icon name="mail" class="h-4 w-4" /> Send message
                    </button>
                </form>
            </div>
        </div>
@endsection
