@extends('layouts.admin')

@section('title', $customer->name)
@section('subtitle', 'Customer profile')

@section('header')
    <div class="flex gap-2">
        <a href="{{ route('admin.customers.edit', $customer) }}" class="btn-neon">Edit customer</a>
        <a href="{{ route('admin.customers.index') }}" class="btn-ghost">← All customers</a>
    </div>
@endsection

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Phone</p>
            <p class="mt-2 font-semibold text-white">{{ $customer->phone }}</p>
            @if($customer->email)<p class="text-xs text-zinc-500">{{ $customer->email }}</p>@endif
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Total bookings</p>
            <p class="stat-value mt-2">{{ $customer->bookings_count }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Total spent</p>
            <p class="stat-value mt-2 text-neon-400">{{ number_format($customer->total_spent, 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Customer since</p>
            <p class="mt-2 font-semibold text-white">{{ $customer->created_at->format('d M Y') }}</p>
            <p class="text-xs text-zinc-500">{{ $customer->created_at->diffForHumans() }}</p>
        </div>
    </div>

    @if($customer->notes)
        <div class="neon-card mt-6 p-5">
            <h2 class="neon-label">Notes</h2>
            <p class="text-sm text-zinc-300">{{ $customer->notes }}</p>
        </div>
    @endif

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Recent bookings --}}
        <div class="neon-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Recent bookings</h2>
                <a href="{{ route('admin.bookings.index', ['q' => $customer->phone]) }}" class="text-xs text-neon-400 hover:text-neon-300">View all →</a>
            </div>
            <div class="overflow-x-auto">
                <table class="table-neon">
                    <thead><tr><th>Code</th><th>Activity</th><th>When</th><th>Status</th></tr></thead>
                    <tbody>
                        @forelse($bookings as $b)
                            <tr>
                                <td class="font-mono text-xs"><a href="{{ route('admin.bookings.show', $b) }}" class="text-neon-400 hover:text-neon-300">{{ $b->code }}</a></td>
                                <td class="text-sm">{{ $b->resource?->name }}</td>
                                <td class="text-xs text-zinc-400 whitespace-nowrap">{{ $b->starts_at->format('d M, g:i A') }}</td>
                                <td><span class="badge {{ $b->statusBadge() }}">{{ str_replace('_', ' ', $b->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-sm text-zinc-500">No bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Payments --}}
        <div class="neon-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Payments</h2>
                <span class="chip text-emerald-300">Received</span>
            </div>
            <div class="overflow-x-auto">
                <table class="table-neon">
                    <thead><tr><th>Code</th><th>Amount</th><th>Method</th><th>Date</th></tr></thead>
                    <tbody>
                        @forelse($payments as $p)
                            <tr>
                                <td class="font-mono text-xs text-zinc-400">{{ $p->code }}</td>
                                <td class="font-semibold text-emerald-300">{{ number_format($p->amount, 2) }}</td>
                                <td class="text-xs uppercase text-zinc-400">{{ $p->method }}</td>
                                <td class="text-xs text-zinc-500 whitespace-nowrap">{{ $p->created_at->format('d M, g:i A') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-8 text-center text-sm text-zinc-500">No payments yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
