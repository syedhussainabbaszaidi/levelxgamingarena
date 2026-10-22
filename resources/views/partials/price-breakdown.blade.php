@php($currency = $currency ?? config('app.currency', 'PKR'))

<div class="space-y-1.5 text-sm">
    @forelse($breakdown['segments'] ?? [] as $segment)
        <div class="flex items-center justify-between text-zinc-400">
            <span>
                {{ $segment['start'] }}–{{ $segment['end'] }}
                <span class="text-xs text-zinc-500">
                    · {{ $segment['minutes'] }} min
                    @if(($segment['rate'] ?? null) !== null)
                        @ {{ number_format($segment['rate'], 0) }}/hr
                    @else
                        · fixed package price
                    @endif
                </span>
                @if($segment['rule'])
                    <span class="ml-1 text-[11px] text-neon-400">{{ $segment['rule'] }}</span>
                @endif
            </span>
            <span class="font-medium text-zinc-200">{{ number_format($segment['cost'], 2) }}</span>
        </div>
    @empty
        <p class="text-sm text-zinc-500">No price segments.</p>
    @endforelse

    <div class="flex items-center justify-between border-t border-white/[0.08] pt-2 text-base">
        <span class="font-semibold text-white">Total ({{ $breakdown['minutes'] ?? 0 }} min)</span>
        <span class="font-display text-xl font-bold text-neon-400">{{ number_format($breakdown['total'] ?? 0, 2) }} {{ $currency }}</span>
    </div>
</div>
