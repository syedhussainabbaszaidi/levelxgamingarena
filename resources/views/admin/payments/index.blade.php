@extends('layouts.admin')

@section('title', 'Payments')
@section('subtitle', 'Every amount collected across the venue')

@section('content')
    <div class="mb-6 grid gap-4 sm:grid-cols-3">
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Collected today</p>
            <p class="stat-value mt-2 text-emerald-400">{{ number_format($totals['today'], 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">This month</p>
            <p class="stat-value mt-2">{{ number_format($totals['month'], 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Outstanding due</p>
            <p class="stat-value mt-2 text-amber-400">{{ number_format($totals['due'], 0) }}</p>
        </div>
    </div>

    <div class="neon-card mb-6 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="neon-label" for="method">Method</label>
                <select id="method" name="method" class="select-neon !w-auto">
                    <option value="">All methods</option>
                    @foreach(['cash', 'online'] as $m)
                        <option value="{{ $m }}" @selected(request('method') === $m)>{{ ucfirst($m) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="neon-label" for="date">Date</label>
                <input type="date" id="date" name="date" value="{{ request('date') }}" class="input-neon !w-auto">
            </div>
            <button class="btn-neon">Filter</button>
            <a href="{{ route('admin.payments.index') }}" class="btn-ghost">Reset</a>
            <span class="ml-auto self-center text-xs text-zinc-500">{{ $payments->total() }} {{ Str::plural('payment', $payments->total()) }}</span>
        </form>
    </div>

    <div class="neon-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="table-neon">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Kind</th>
                        <th>Received by</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $p)
                        <tr>
                            <td class="font-mono text-xs text-zinc-300">{{ $p->code }}</td>
                            <td>
                                @if($p->booking)
                                    <a href="{{ route('admin.bookings.show', $p->booking) }}" class="font-mono text-xs text-neon-400 hover:text-neon-300">{{ $p->booking->code }}</a>
                                @else
                                    <span class="text-zinc-600">—</span>
                                @endif
                            </td>
                            <td class="text-sm">{{ $p->customer?->name ?? '—' }}</td>
                            <td class="font-semibold text-white">{{ number_format($p->amount, 2) }}</td>
                            <td class="text-xs uppercase text-zinc-400">{{ $p->method }}</td>
                            <td class="text-xs text-zinc-400">{{ $p->kind }}</td>
                            <td class="text-xs text-zinc-500">{{ $p->user?->name ?? 'system' }}</td>
                            <td class="text-xs text-zinc-500 whitespace-nowrap">{{ $p->created_at->format('d M Y, g:i A') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-12 text-center text-sm text-zinc-500">No payments recorded for these filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">{{ $payments->links() }}</div>
@endsection
