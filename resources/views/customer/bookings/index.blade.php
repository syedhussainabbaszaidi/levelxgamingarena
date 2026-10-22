@extends('layouts.public')

@section('title', 'My Bookings')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
        <div>
            <h1 class="neon-title text-2xl">My bookings</h1>
            <p class="text-sm text-zinc-500">Every session you have booked, with status and pricing.</p>
        </div>
        <a href="{{ route('public.index') }}" class="btn-neon"><x-icon name="plus" class="h-4 w-4" /> New booking</a>
    </div>

    <div class="mb-5 flex flex-wrap gap-2">
        <a href="{{ route('public.my-bookings') }}"
           class="chip {{ !$filter ? '!border-neon-500/50 !text-white' : '' }}">All</a>
        @foreach(['pending', 'confirmed', 'completed', 'cancelled'] as $status)
            <a href="{{ route('public.my-bookings', ['status' => $status]) }}"
               class="chip {{ $filter === $status ? '!border-neon-500/50 !text-white' : '' }}">
                {{ ucfirst(str_replace('_', ' ', $status)) }}
            </a>
        @endforeach
    </div>

    <div class="space-y-4">
        @forelse($bookings as $booking)
            <a href="{{ route('public.booking', $booking) }}"
               class="neon-card neon-card-hover flex flex-col gap-4 p-5 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-start gap-4">
                    <div class="grid h-12 w-12 shrink-0 place-items-center rounded-xl bg-neon-500/10 font-display text-sm font-bold text-neon-400">
                        {{ $booking->starts_at->format('d') }}<br><span class="text-[10px] font-normal">{{ $booking->starts_at->format('M') }}</span>
                    </div>
                    <div>
                        <p class="font-semibold text-white">{{ $booking->resource?->name }}</p>
                        <p class="text-xs text-zinc-500">
                            {{ $booking->resource?->category?->name }} · {{ $booking->starts_at->format('g:i A') }}–{{ $booking->ends_at->format('g:i A') }}
                            · {{ $booking->duration_minutes }} min
                        </p>
                        <p class="mt-1 font-mono text-[11px] text-zinc-600">{{ $booking->code }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-4 sm:flex-col sm:items-end sm:gap-1">
                    <div class="flex items-center gap-1.5">
                        <span class="badge {{ $booking->statusBadge() }}">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                        <span class="badge {{ $booking->paymentBadge() }}">{{ ucfirst($booking->payment_status) }}</span>
                    </div>
                    <span class="font-display text-lg font-bold text-white">{{ number_format($booking->final_cost, 0) }}</span>
                </div>
            </a>
        @empty
            <div class="neon-card p-12 text-center">
                <p class="text-zinc-400">No bookings found.</p>
                <a href="{{ route('public.index') }}" class="btn-neon mt-4">Browse activities</a>
            </div>
        @endforelse
    </div>

    <div class="mt-6">{{ $bookings->links() }}</div>
@endsection
