@extends('layouts.admin')

@section('title', 'Dashboard')
@section('subtitle', 'Live overview of your venue')

@section('content')
    @php($maxTrend = max(1, $trend->max()))

    {{-- Stat cards --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Live sessions', $stats['active_sessions'], 'gamepad', 'text-neon-400'],
            ['Today\'s bookings', $stats['today_bookings'], 'calendar-days', 'text-white'],
            ['Today\'s revenue', number_format($stats['today_revenue'], 0), 'banknotes', 'text-emerald-400'],
            ['Unpaid / due', number_format($stats['due_amount'], 0), 'clock', 'text-amber-400'],
        ] as [$label, $value, $icon, $colour])
            <div class="neon-card neon-card-hover p-5">
                <div class="flex items-center justify-between">
                    <span class="text-xs uppercase tracking-wider text-zinc-500">{{ $label }}</span>
                    <x-icon :name="$icon" class="h-5 w-5 {{ $colour }}" />
                </div>
                <p class="stat-value mt-2 {{ $colour }}">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">
        {{-- Trend --}}
        <div class="neon-card p-6 lg:col-span-2">
            <div class="mb-5 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-semibold text-white">Bookings — last 7 days</h2>
                    <p class="text-xs text-zinc-500">Week revenue: {{ number_format($stats['week_revenue'], 0) }}</p>
                </div>
                <span class="chip">Floor occupancy {{ $stats['occupancy'] }}%</span>
            </div>

            <div class="flex h-40 items-end gap-3">
                @foreach($trend as $day => $count)
                    <div class="group flex flex-1 flex-col items-center gap-2">
                        <span class="text-xs text-zinc-500 opacity-0 transition group-hover:opacity-100">{{ $count }}</span>
                        <div class="w-full rounded-t-md bg-gradient-to-t from-neon-700 to-neon-500 transition-all duration-300 hover:from-neon-600 hover:to-neon-400"
                             style="height: {{ max(4, ($count / $maxTrend) * 100) }}%; box-shadow: 0 0 18px -6px rgba(255,45,85,.8)"></div>
                        <span class="text-[10px] uppercase text-zinc-600">{{ \Carbon\Carbon::parse($day)->format('D') }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Top activities --}}
        <div class="neon-card p-6">
            <h2 class="mb-4 text-sm font-semibold text-white">Top activities (30 days)</h2>
            @forelse($topActivities as $row)
                <div class="mb-3 last:mb-0">
                    <div class="flex items-center justify-between text-sm">
                        <span class="truncate text-zinc-300">{{ $row->resource?->name ?? 'Removed activity' }}</span>
                        <span class="text-zinc-500">{{ $row->bookings }}</span>
                    </div>
                    <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-ink-500">
                        @php($pct = $topActivities->max('bookings') ? ($row->bookings / $topActivities->max('bookings')) * 100 : 0)
                        <div class="h-full rounded-full bg-neon-500" style="width: {{ $pct }}%"></div>
                    </div>
                </div>
            @empty
                <p class="text-sm text-zinc-500">No bookings in the last 30 days.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        {{-- Upcoming --}}
        <div class="neon-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Upcoming bookings</h2>
                <a href="{{ route('admin.bookings.index') }}" class="text-xs text-neon-400 hover:text-neon-300">All bookings →</a>
            </div>
            @forelse($stats['upcoming'] as $booking)
                <a href="{{ route('admin.bookings.show', $booking) }}"
                   class="flex items-center justify-between gap-3 border-b border-white/[0.04] px-5 py-3 transition hover:bg-white/[0.02]">
                    <div class="min-w-0">
                        <p class="truncate text-sm text-white">{{ $booking->resource?->name }}</p>
                        <p class="text-xs text-zinc-500">{{ $booking->customer?->name ?? 'Walk-in' }} · {{ $booking->starts_at->format('d M, g:i A') }}</p>
                    </div>
                    <span class="badge {{ $booking->statusBadge() }}">{{ $booking->status }}</span>
                </a>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">Nothing scheduled.</p>
            @endforelse
        </div>

        {{-- Recent activity --}}
        <div class="neon-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Recent activity</h2>
                @if(auth()->user()->hasPermissionTo('logs.view'))
                    <a href="{{ route('admin.logs.index') }}" class="text-xs text-neon-400 hover:text-neon-300">All logs →</a>
                @endif
            </div>
            @forelse($recentLogs as $log)
                <div class="flex items-start gap-3 border-b border-white/[0.04] px-5 py-3">
                    <span class="mt-1.5 h-1.5 w-1.5 shrink-0 rounded-full bg-neon-500"></span>
                    <div class="min-w-0">
                        <p class="truncate text-sm text-zinc-300">{{ $log->description }}</p>
                        <p class="text-[11px] text-zinc-600">{{ $log->user?->name ?? 'system' }} · {{ $log->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            @empty
                <p class="px-5 py-8 text-center text-sm text-zinc-500">No activity yet.</p>
            @endforelse
        </div>
    </div>
@endsection
