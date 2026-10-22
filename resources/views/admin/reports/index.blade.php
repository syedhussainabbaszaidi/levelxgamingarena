@extends('layouts.admin')

@section('title', 'Reports')
@section('subtitle', now()->format('F Y').' performance overview')

@section('content')
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Revenue (month)</p>
            <p class="stat-value mt-2 text-emerald-400">{{ number_format($totals['month_revenue'], 0) }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Bookings (month)</p>
            <p class="stat-value mt-2">{{ $totals['month_bookings'] }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Completed</p>
            <p class="stat-value mt-2 text-neon-400">{{ $totals['completed'] }}</p>
        </div>
        <div class="neon-card p-5">
            <p class="text-xs uppercase tracking-wider text-zinc-500">Cancelled</p>
            <p class="stat-value mt-2 text-rose-400">{{ $totals['cancelled'] }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Revenue by day --}}
        <div class="neon-card p-6">
            <h2 class="mb-5 text-sm font-semibold text-white">Revenue by day</h2>
            @if($revenueByDay->isEmpty())
                <p class="text-sm text-zinc-500">No revenue yet this month.</p>
            @else
                @php($max = $revenueByDay->max('total') ?: 1)
                <div class="space-y-2.5">
                    @foreach($revenueByDay as $row)
                        <div class="flex items-center gap-3 text-sm">
                            <span class="w-16 shrink-0 text-xs text-zinc-500">{{ \Carbon\Carbon::parse($row->day)->format('M d') }}</span>
                            <div class="h-2.5 flex-1 overflow-hidden rounded-full bg-ink-500">
                                <div class="h-full rounded-full bg-gradient-to-r from-neon-700 to-neon-500 shadow-neon-sm" style="width: {{ round($row->total / $max * 100) }}%"></div>
                            </div>
                            <span class="w-20 shrink-0 text-right text-sm font-medium text-zinc-200">{{ number_format($row->total, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Revenue by method --}}
        <div class="neon-card p-6">
            <h2 class="mb-5 text-sm font-semibold text-white">Revenue by method</h2>
            @if($revenueByMethod->isEmpty())
                <p class="text-sm text-zinc-500">No data.</p>
            @else
                <ul class="space-y-3 text-sm">
                    @foreach($revenueByMethod as $row)
                        <li class="flex items-center justify-between border-b border-white/[0.04] pb-2 last:border-0 last:pb-0">
                            <span class="uppercase text-zinc-400">{{ $row->method }} <span class="text-xs text-zinc-600">({{ $row->count }})</span></span>
                            <strong class="text-zinc-100">{{ number_format($row->total, 0) }}</strong>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Top resources --}}
        <div class="neon-card overflow-hidden">
            <h2 class="border-b border-white/[0.06] px-6 py-4 text-sm font-semibold text-white">Top activities</h2>
            <div class="overflow-x-auto">
                <table class="table-neon">
                    <thead><tr><th>Activity</th><th>Bookings</th><th class="text-right">Revenue</th></tr></thead>
                    <tbody>
                        @forelse($topResources as $r)
                            <tr>
                                <td class="text-sm">{{ $r->name }}</td>
                                <td class="text-sm">{{ $r->bookings_count }}</td>
                                <td class="text-right font-semibold text-neon-400">{{ number_format($r->revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-sm text-zinc-500">No completed bookings yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Top customers --}}
        <div class="neon-card overflow-hidden">
            <h2 class="border-b border-white/[0.06] px-6 py-4 text-sm font-semibold text-white">Top customers</h2>
            <div class="overflow-x-auto">
                <table class="table-neon">
                    <thead><tr><th>Customer</th><th>Bookings</th><th class="text-right">Spent</th></tr></thead>
                    <tbody>
                        @forelse($topCustomers as $c)
                            <tr>
                                <td class="text-sm">{{ $c->name }} <span class="text-xs text-zinc-500">{{ $c->phone }}</span></td>
                                <td class="text-sm">{{ $c->bookings_count }}</td>
                                <td class="text-right font-semibold text-zinc-100">{{ number_format($c->revenue, 0) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="py-8 text-center text-sm text-zinc-500">No data yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
