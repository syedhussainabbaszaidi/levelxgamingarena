@extends('layouts.public')

@section('title', 'My Dashboard')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="neon-title text-2xl">Welcome back, {{ $user->name }}</h1>
            <p class="text-sm text-zinc-500">{{ $user->email }}</p>
        </div>
        <a href="{{ route('public.index') }}" class="btn-neon">
            <x-icon name="plus" class="h-4 w-4" /> New booking
        </a>
    </div>

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['Total bookings', $stats['total'], 'calendar-days'],
            ['Upcoming', $stats['upcoming'], 'clock'],
            ['Completed', $stats['completed'], 'check-circle'],
            ['Total spent', number_format($stats['spent'], 0), 'banknotes'],
        ] as [$label, $value, $icon])
            <div class="neon-card neon-card-hover p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-zinc-500">{{ $label }}</span>
                    <x-icon :name="$icon" class="h-5 w-5 text-zinc-500" />
                </div>
                <p class="stat-value mt-2">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Profile --}}
        <div class="neon-card h-fit p-6">
            <h2 class="mb-4 text-sm font-semibold text-white">Profile</h2>
            <div class="flex items-center gap-3">
                <span class="grid h-12 w-12 place-items-center rounded-full bg-neon-600 text-lg font-bold text-white">{{ substr($user->name, 0, 1) }}</span>
                <div>
                    <p class="font-semibold text-white">{{ $user->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $user->email }}</p>
                </div>
            </div>

            <dl class="mt-5 space-y-3 text-sm">
                <div class="flex justify-between"><dt class="text-zinc-500">Phone</dt><dd class="text-zinc-300">{{ $customer?->phone ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Visits</dt><dd class="text-zinc-300">{{ $customer?->total_visits ?? 0 }}</dd></div>
                <div class="flex justify-between"><dt class="text-zinc-500">Member since</dt><dd class="text-zinc-300">{{ $user->created_at->format('M Y') }}</dd></div>
            </dl>

            <a href="{{ route('customer.profile') }}" class="btn-ghost mt-5 w-full !py-1.5 !text-xs">Edit profile</a>
        </div>

        {{-- Recent bookings --}}
        <div class="neon-card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Recent bookings</h2>
                <a href="{{ route('public.my-bookings') }}" class="inline-flex items-center gap-1 text-xs text-neon-400 hover:text-neon-300">View all <x-icon name="arrow-right" class="h-3.5 w-3.5" /></a>
            </div>

            @forelse($bookings as $booking)
                <a href="{{ route('public.booking', $booking) }}"
                   class="flex items-center justify-between gap-4 border-b border-white/[0.04] px-5 py-4 transition hover:bg-white/[0.02]">
                    <div class="min-w-0">
                        <p class="truncate font-medium text-white">{{ $booking->resource?->name }}</p>
                        <p class="text-xs text-zinc-500">
                            {{ $booking->code }} · {{ $booking->starts_at->format('d M Y, g:i A') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <span class="badge {{ $booking->statusBadge() }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                            <span class="badge {{ $booking->paymentBadge() }}">{{ ucfirst($booking->payment_status) }}</span>
                        </div>
                        <p class="mt-1 text-xs text-zinc-400">{{ number_format($booking->final_cost, 0) }}</p>
                    </div>
                </a>
            @empty
                <div class="p-10 text-center">
                    <p class="text-sm text-zinc-500">You have no bookings yet.</p>
                    <a href="{{ route('public.index') }}" class="btn-neon mt-4">Book your first session</a>
                </div>
            @endforelse
        </div>
    </div>
@endsection
