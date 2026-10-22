@extends('layouts.admin')

@section('title', 'Booking '.$booking->code)
@section('subtitle', $booking->resource?->name)

@section('header')
    <a href="{{ route('admin.bookings.index') }}" class="btn-ghost">← All bookings</a>
@endsection

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="neon-card p-6">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-xs uppercase tracking-widest text-zinc-500">Reference</p>
                        <p class="font-display text-2xl font-bold text-white">{{ $booking->code }}</p>
                        <p class="mt-1 text-xs text-zinc-500">Created {{ $booking->created_at->format('d M Y, g:i A') }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-2">
                        <span class="badge {{ $booking->statusBadge() }} !px-3 !py-1 !text-sm">{{ str_replace('_', ' ', $booking->status) }}</span>
                        <span class="badge {{ $booking->paymentBadge() }} !px-3 !py-1 !text-sm">{{ ucfirst($booking->payment_status) }}</span>
                    </div>
                </div>

                <div class="mt-6 grid gap-6 sm:grid-cols-2">
                    <div>
                        <h2 class="neon-label">Activity</h2>
                        <p class="font-semibold text-white">{{ $booking->resource?->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $booking->resource?->category?->name }}</p>
                    </div>
                    <div>
                        <h2 class="neon-label">Schedule</h2>
                        <p class="text-sm text-zinc-300">{{ $booking->starts_at->format('D, d M Y') }}</p>
                        <p class="text-sm text-zinc-400">{{ $booking->starts_at->format('g:i A') }} – {{ $booking->ends_at->format('g:i A') }}</p>
                        <p class="text-xs text-zinc-500">{{ $booking->duration_minutes }} minutes</p>
                    </div>
                    <div>
                        <h2 class="neon-label">Customer</h2>
                        @if($booking->customer)
                            <a href="{{ route('admin.customers.show', $booking->customer) }}" class="text-sm text-neon-400 hover:text-neon-300">
                                {{ $booking->customer->name }}
                            </a>
                            <p class="text-xs text-zinc-500">{{ $booking->customer->phone }}</p>
                            @if($booking->customer->email)<p class="text-xs text-zinc-500">{{ $booking->customer->email }}</p>@endif
                        @else
                            <p class="text-sm text-zinc-500">No customer record</p>
                        @endif
                    </div>
                    <div>
                        <h2 class="neon-label">Logged by</h2>
                        <p class="text-sm text-zinc-300">{{ $booking->user?->name ?? 'Customer (online)' }}</p>
                        @if($booking->ended_at)
                            <p class="text-xs text-zinc-500">Ended {{ $booking->ended_at->format('d M, g:i A') }}</p>
                        @endif
                    </div>
                </div>

                @if($booking->fieldValues->isNotEmpty())
                    <div class="mt-6">
                        <h2 class="neon-label">Booking form answers</h2>
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

            {{-- Payments --}}
            <div class="neon-card overflow-hidden">
                <div class="flex items-center justify-between border-b border-white/[0.06] px-6 py-4">
                    <h2 class="text-sm font-semibold text-white">Payments</h2>
                    <div class="flex gap-2 text-xs">
                        <span class="chip">Paid {{ number_format($booking->paidAmount(), 2) }}</span>
                        <span class="chip {{ $booking->dueAmount() > 0 ? '!border-amber-500/30 !text-amber-300' : '!border-emerald-500/30 !text-emerald-300' }}">
                            Due {{ number_format($booking->dueAmount(), 2) }}
                        </span>
                    </div>
                </div>
                <table class="table-neon">
                    <thead><tr><th>Code</th><th>Amount</th><th>Method</th><th>Kind</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($booking->payments as $payment)
                            <tr>
                                <td class="font-mono text-xs text-zinc-400">{{ $payment->code }}</td>
                                <td class="font-semibold text-white">{{ number_format($payment->amount, 2) }}</td>
                                <td class="text-xs uppercase text-zinc-400">{{ $payment->method }}</td>
                                <td class="text-xs text-zinc-400">{{ $payment->kind }}</td>
                                <td class="text-xs text-zinc-500">{{ $payment->created_at->format('d M Y, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-6 text-center text-sm text-zinc-500">No payments recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if(auth()->user()->hasPermissionTo('bookings.edit'))
                    <form method="POST" action="{{ route('admin.payments.store') }}" class="grid gap-3 border-t border-white/[0.06] px-6 py-4 sm:grid-cols-[1fr_140px_140px_auto]">
                        @csrf
                        <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                        <input type="number" name="amount" step="0.01" min="0.01" value="{{ max(0, $booking->dueAmount()) }}" placeholder="Amount" required class="input-neon">
                        <select name="method" class="select-neon">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                        <select name="kind" class="select-neon">
                            @foreach(['partial', 'full', 'addon'] as $kind)
                                <option value="{{ $kind }}">{{ ucfirst($kind) }}</option>
                            @endforeach
                        </select>
                        <button class="btn-neon">Record payment</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="neon-card p-5">
                <h2 class="neon-label">Price</h2>
                <p class="font-display text-3xl font-bold text-neon-400">{{ number_format($booking->final_cost, 0) }}</p>
                <p class="text-xs text-zinc-500">{{ $booking->resource?->business?->currency }} final · estimated {{ number_format($booking->estimated_cost, 0) }}</p>
                @if($booking->extended_minutes)
                    <p class="mt-1 text-xs text-amber-300">+{{ $booking->extended_minutes }} min extended</p>
                @endif
            </div>

            @if(auth()->user()->hasPermissionTo('bookings.status'))
                <div class="neon-card p-5">
                    <h2 class="mb-3 text-sm font-semibold text-white">Change status</h2>
                    <form method="POST" action="{{ route('admin.bookings.status', $booking) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <select name="status" class="select-neon">
                            @foreach($statuses as $status)
                                <option value="{{ $status }}" @selected($booking->status === $status)>{{ ucfirst(str_replace('_', ' ', $status)) }}</option>
                            @endforeach
                        </select>
                        <input name="note" placeholder="Optional note for the log" class="input-neon text-sm">
                        <button class="btn-neon w-full">Update status</button>
                    </form>
                </div>
            @endif

            @if(auth()->user()->hasPermissionTo('bookings.edit'))
                <div class="neon-card p-5">
                    <h2 class="mb-3 text-sm font-semibold text-white">Payment status</h2>
                    <div class="mb-3 grid grid-cols-2 gap-2">
                        <button type="button"
                                onclick="var f=this.closest('.neon-card'); f.querySelector('[name=payment_status]').value='paid'; f.querySelector('form').submit();"
                                class="{{ $booking->isPaid() ? 'btn-neon' : 'btn-ghost' }}">Paid</button>
                        <button type="button"
                                onclick="var f=this.closest('.neon-card'); f.querySelector('[name=payment_status]').value='unpaid'; f.querySelector('form').submit();"
                                class="{{ $booking->isPaid() ? 'btn-ghost' : 'btn-neon' }}">Unpaid</button>
                    </div>
                    <form method="POST" action="{{ route('admin.bookings.payment-status', $booking) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="payment_status" value="{{ $booking->payment_status }}">
                        <select name="method" class="select-neon" title="Payment method">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                        <p class="mt-2 text-xs text-zinc-500">
                            Marking paid adds the amount to Today's revenue & total payments; unpaid moves it back to Due.
                        </p>
                    </form>
                </div>
            @endif

            @if($booking->checked_in_at !== null && $booking->ended_at === null && auth()->user()->hasPermissionTo('bookings.status'))
                <div class="neon-card p-5">
                    <h2 class="mb-3 text-sm font-semibold text-white">Live session</h2>
                    <form method="POST" action="{{ route('admin.sessions.extend', $booking) }}" class="mb-3 flex gap-2">
                        @csrf
                        <input type="number" name="minutes" value="30" min="15" step="15" class="input-neon">
                        <button class="btn-ghost whitespace-nowrap">Extend</button>
                    </form>
                    <form method="POST" action="{{ route('admin.sessions.end', $booking) }}">
                        @csrf
                        <select name="method" class="select-neon mb-3">
                            @foreach($paymentMethods as $method)
                                <option value="{{ $method }}">{{ ucfirst($method) }}</option>
                            @endforeach
                        </select>
                        <button class="btn-neon w-full">End session & settle</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
