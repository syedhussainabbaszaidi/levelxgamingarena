@extends('layouts.public')

@section('title', 'Booking '.$booking->code)

@section('content')
    <a href="{{ route('public.my-bookings') }}" class="mb-4 inline-block text-xs text-zinc-500 transition hover:text-neon-400">← My bookings</a>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="neon-card p-6 lg:col-span-2">                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-zinc-500">Reference</p>
                        <p class="font-display text-2xl font-bold text-white">{{ $booking->code }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="badge {{ $booking->statusBadge() }} !px-3 !py-1 !text-sm">{{ ucfirst(str_replace('_', ' ', $booking->status)) }}</span>
                        <span class="badge {{ $booking->paymentBadge() }} !px-3 !py-1 !text-sm">{{ ucfirst($booking->payment_status) }}</span>
                    </div>
                </div>

            <div class="mt-6 grid gap-6 sm:grid-cols-2">
                <div>
                    <h2 class="neon-label">Activity</h2>
                    <p class="font-semibold text-white">{{ $booking->resource?->name }}</p>
                    <p class="text-xs text-zinc-500">{{ $booking->resource?->category?->name }}</p>

                    @if($booking->resource?->description)
                        <p class="mt-2 text-sm text-zinc-400">{{ $booking->resource->description }}</p>
                    @endif
                </div>
                <div>
                    <h2 class="neon-label">Schedule</h2>
                    <p class="text-sm text-zinc-300">{{ $booking->starts_at->format('D, d M Y') }}</p>
                    <p class="text-sm text-zinc-400">{{ $booking->starts_at->format('g:i A') }} – {{ $booking->ends_at->format('g:i A') }}</p>
                    <p class="text-xs text-zinc-500">{{ $booking->duration_minutes }} minutes booked</p>
                    @if($booking->extended_minutes)
                        <p class="text-xs text-amber-300">+{{ $booking->extended_minutes }} min extended</p>
                    @endif
                </div>
            </div>

            @if($booking->fieldValues->isNotEmpty())
                <div class="mt-6">
                    <h2 class="neon-label">Your details</h2>
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

            @if($booking->notes)
                <div class="mt-6">
                    <h2 class="neon-label">Notes</h2>
                    <p class="text-sm text-zinc-400">{{ $booking->notes }}</p>
                </div>
            @endif
        </div>

        <div class="space-y-5">
            <div class="neon-card p-5">
                <h2 class="neon-label">Price</h2>
                <p class="font-display text-3xl font-bold text-neon-400">{{ number_format($booking->final_cost, 0) }}</p>
                <p class="text-xs text-zinc-500">{{ $booking->resource?->business?->currency }} final total</p>

                <div class="mt-4 space-y-1.5 text-xs">
                    <div class="flex justify-between"><span class="text-zinc-500">Paid</span><span class="text-zinc-300">{{ number_format($booking->paidAmount(), 2) }}</span></div>
                    <div class="flex justify-between"><span class="text-zinc-500">Due</span>
                        <span class="{{ $booking->dueAmount() > 0 ? 'text-amber-300' : 'text-emerald-300' }}">{{ number_format($booking->dueAmount(), 2) }}</span>
                    </div>
                </div>

                @if($booking->payments->isNotEmpty())
                    <div class="mt-4 border-t border-white/[0.06] pt-3">
                        <h3 class="neon-label">Payments</h3>
                        @foreach($booking->payments as $payment)
                            <div class="flex justify-between text-xs text-zinc-400">
                                <span>{{ $payment->created_at->format('d M Y') }} · {{ $payment->method }}</span>
                                <span>{{ number_format($payment->amount, 2) }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="neon-card p-5">
                <h2 class="neon-label">Need to change something?</h2>
                <p class="text-xs text-zinc-500">
                    Call us at {{ $booking->resource?->business?->phone ?? 'the counter' }} — changes within
                    {{ \App\Models\Setting::get('cancellation_window_hours', 2) }} hours of the start time may incur a fee.
                </p>
                <a href="{{ route('public.index') }}" class="btn-neon mt-4 w-full !py-2 !text-xs">Book another session</a>
            </div>
        </div>
    </div>
@endsection
