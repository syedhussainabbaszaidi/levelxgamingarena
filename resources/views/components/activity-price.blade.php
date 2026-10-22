@props(['activity', 'show' => true, 'align' => 'left', 'currency' => null])

@if($show)
    @php
        $business = $activity->business;
        $money = $currency ?? ($business?->currency ?? 'PKR');
        $alignment = $align === 'right' ? 'items-end text-right' : 'items-start text-left';
    @endphp

    <div {{ $attributes->merge(['class' => 'flex min-w-0 flex-col '.$alignment]) }}>
        @if($activity->hasDiscount())
            <p class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-zinc-500 line-through">{{ number_format($activity->regularUnitPrice(), 0) }}</span>
                <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-300">{{ $activity->discountPercent() }}% off</span>
            </p>
        @endif

        <p class="flex flex-wrap items-baseline gap-x-1.5">
            <span class="font-display text-2xl font-bold leading-tight text-white">{{ number_format($activity->price(), 0) }}</span>
            @if($activity->priceUnit())
                <span class="text-[11px] uppercase tracking-wider text-zinc-500">{{ $money }}/{{ $activity->priceUnit() }}</span>
            @else
                <span class="text-[11px] uppercase tracking-wider text-zinc-500">{{ $money }}</span>
            @endif
        </p>

        @if($activity->packageLabel())
            <p class="text-[11px] text-zinc-500">{{ $activity->packageLabel() }}</p>
        @elseif($activity->hasDiscount() && $activity->promo_label)
            <p class="text-[11px] text-emerald-400/80">{{ $activity->promo_label }}</p>
        @endif
    </div>
@endif
