@php($showImage = ($settings['show_activity_image'] ?? '1') === '1')
@php($showRate = ($settings['show_hourly_rate'] ?? '1') === '1')
@php($available = ($availability[$activity->id] ?? true))
@php($bookable = $activity->isOnlineBookable())

<a href="{{ route('public.activity', $activity) }}"
   class="neon-card neon-card-hover group relative flex h-full flex-col overflow-hidden">
    {{-- Availability badge (live, from real bookings for today) --}}
    <span class="absolute right-3 top-3 z-10">
        @if(! $bookable)
            <span class="badge border-white/10 bg-ink-950/80 text-zinc-300 backdrop-blur">
                <x-icon name="lock" class="h-3.5 w-3.5" /> Counter only
            </span>
        @elseif($available)
            <span class="badge border-emerald-500/40 bg-emerald-500/15 text-emerald-300 backdrop-blur">
                <x-icon name="check-circle" class="h-3.5 w-3.5" /> Available
            </span>
        @else
            <span class="badge border-rose-500/40 bg-rose-500/15 text-rose-300 backdrop-blur">
                <x-icon name="x-circle" class="h-3.5 w-3.5" /> Fully booked
            </span>
        @endif
    </span>

    @if($showImage && $activity->imageUrl())
        <div class="h-40 w-full overflow-hidden sm:h-44">
            <img src="{{ $activity->imageUrl() }}" alt="{{ $activity->name }}" loading="lazy"
                 onerror="this.onerror=null;this.src='{{ $activity->fallbackImageUrl() }}';"
                 class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
        </div>
    @else
        <div class="grid h-40 w-full place-items-center border-b border-white/[0.06] bg-gradient-to-br from-ink-600 to-ink-800 sm:h-44">
            <span class="font-display text-4xl font-black text-neon-500/30">{{ strtoupper(substr($activity->name, 0, 2)) }}</span>
        </div>
    @endif

    <div class="flex flex-1 flex-col p-5">
        <div class="flex min-w-0 items-start justify-between gap-3">
            <h3 class="min-w-0 break-words font-semibold text-white transition group-hover:text-neon-300">{{ $activity->name }}</h3>
            @if($featured ?? false)
                <span class="badge shrink-0 border-neon-500/30 bg-neon-500/10 text-neon-300">
                    <x-icon name="star" class="h-3.5 w-3.5" /> Featured
                </span>
            @endif
        </div>

        <p class="mt-1 text-xs text-zinc-500">{{ $activity->category?->name }} · up to {{ $activity->capacity }} players</p>

        @if(($settings['show_activity_description'] ?? '1') === '1' && $activity->description)
            <p class="mt-3 line-clamp-2 text-sm text-zinc-400">{{ Str::limit($activity->description, 110) }}</p>
        @endif

        @if($activity->minimumMinutes() || $activity->addonDescription())
            <p class="mt-2 text-[11px] text-zinc-500">
                @if($activity->minimumMinutes())
                    Minimum {{ $activity->minimumMinutes() }} min
                @endif
                @if($activity->minimumMinutes() && $activity->addonDescription()) · @endif
                {{ $activity->addonDescription() }}
            </p>
        @endif

        <div class="mt-auto flex flex-wrap items-end justify-between gap-3 border-t border-white/[0.06] pt-5">
            @if($showRate)
                <x-activity-price :activity="$activity" class="min-w-0" />
            @else
                <span class="text-xs text-zinc-500">{{ $activity->defaultDuration() }} min sessions</span>
            @endif

            <span class="btn-neon shrink-0 !px-3 !py-2 !text-xs">
                Book <x-icon name="arrow-right" class="h-3.5 w-3.5" />
            </span>
        </div>
    </div>
</a>
