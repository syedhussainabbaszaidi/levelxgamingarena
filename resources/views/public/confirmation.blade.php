@extends('layouts.public')

@section('title', 'Booking '.$booking->code)

@section('content')
        <div class="neon-card animate-fade-up overflow-hidden">
            <div class="border-b border-white/[0.06] bg-neon-500/[0.07] px-6 py-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-neon-300">Booking reference</p>
                        <p class="font-display text-2xl font-bold text-white">{{ $booking->code }}</p>
                    </div>
                    <span class="badge {{ $booking->statusBadge() }} !px-3 !py-1 !text-sm">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                </div>
            </div>

            <div class="grid gap-6 p-6 sm:grid-cols-2">
                <div>
                    <h2 class="neon-label">Activity</h2>
                    <p class="text-lg font-semibold text-white">{{ $booking->resource?->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $booking->resource?->category?->name }}</p>

                    <h2 class="neon-label mt-5">Schedule</h2>
                    <p class="text-sm text-zinc-300">{{ $booking->starts_at->format('D, d M Y') }}</p>
                    <p class="text-sm text-zinc-400">{{ $booking->starts_at->format('g:i A') }} – {{ $booking->ends_at->format('g:i A') }}</p>
                    <p class="text-xs text-zinc-500">{{ $booking->duration_minutes }} minutes</p>
                </div>

                <div>
                    <h2 class="neon-label">Customer</h2>
                    <p class="text-sm text-zinc-300">{{ $booking->customer?->name ?? auth()->user()->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $booking->customer?->phone }}</p>
                    @if($booking->customer?->email)
                        <p class="text-xs text-zinc-500">{{ $booking->customer->email }}</p>
                    @endif

                    <h2 class="neon-label mt-5">Payment</h2>
                    <p class="text-sm text-zinc-300">Paid {{ number_format($booking->paidAmount(), 2) }}</p>
                    <p class="text-sm {{ $booking->dueAmount() > 0 ? 'text-amber-300' : 'text-emerald-300' }}">
                        Due {{ number_format($booking->dueAmount(), 2) }} {{ $booking->resource?->business?->currency }}
                    </p>
                </div>
            </div>

            @if($booking->fieldValues->isNotEmpty())
                <div class="border-t border-white/[0.06] px-6 py-5">
                    <h2 class="neon-label">Booking details</h2>
                    <dl class="grid gap-3 sm:grid-cols-2">
                        @foreach($booking->fieldValues as $value)
                            <div class="neon-panel">
                                <dt class="text-xs text-zinc-500">{{ $value->field?->label }}</dt>
                                <dd class="text-sm text-zinc-200">{{ $value->value }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif

            <div class="border-t border-white/[0.06] px-6 py-5">
                <h2 class="neon-label">Price breakdown</h2>
                @include('partials.price-breakdown', ['breakdown' => $breakdown, 'currency' => $booking->resource?->business?->currency])
            </div>

            @if($booking->notes)
                <div class="border-t border-white/[0.06] px-6 py-5">
                    <h2 class="neon-label">Notes</h2>
                    <p class="text-sm text-zinc-400">{{ $booking->notes }}</p>
                </div>
            @endif

            <div class="flex flex-wrap gap-3 border-t border-white/[0.06] px-6 py-5">
                <a href="{{ route('public.my-bookings') }}" class="btn-neon">My bookings</a>
                <a href="{{ route('public.index') }}" class="btn-ghost">Book another activity</a>
            </div>
        </div>

        @if($booking->status === 'pending')
            <p class="mt-4 text-center text-xs text-zinc-500">
                This booking is awaiting confirmation from our team. You will see the status update in your dashboard.
            </p>
        @endif
    </div>
@endsection
