@extends('layouts.public')

@section('title', $resource->name.' — Book')

@section('content')
    <a href="{{ route('public.index') }}#activities" class="mb-4 inline-block text-xs text-zinc-500 transition hover:text-neon-400">← All activities</a>

    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Activity info --}}
        <aside class="space-y-5">
            <div class="neon-card overflow-hidden">
                @if(($settings['show_activity_image'] ?? '1') === '1' && $resource->imageUrl())
                    <img src="{{ $resource->imageUrl() }}" alt="{{ $resource->name }}" loading="lazy"
                         onerror="this.onerror=null;this.src='{{ $resource->fallbackImageUrl() }}';"
                         class="h-48 w-full object-cover sm:h-56">
                @endif
                <div class="p-5">
                    <div class="mb-3 flex flex-wrap items-center gap-2">
                        <span class="chip">{{ $resource->category?->name }}</span>
                        <span class="badge border-emerald-500/30 bg-emerald-500/10 text-emerald-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Open 24/7
                        </span>
                    </div>
                    <h1 class="break-words font-display text-2xl font-bold text-white">{{ $resource->name }}</h1>
                    @if(($settings['show_activity_description'] ?? '1') === '1' && $resource->description)
                        <p class="mt-3 text-sm leading-relaxed text-zinc-400">{{ $resource->description }}</p>
                    @endif

                    @if(($settings['show_hourly_rate'] ?? '1') === '1')
                        <div class="mt-5 rounded-xl border border-neon-500/25 bg-neon-500/[0.06] p-4">
                            <x-activity-price :activity="$resource" />
                            @if($resource->promo_label && $resource->hasDiscount())
                                <p class="mt-1 text-[11px] font-semibold uppercase tracking-wider text-emerald-300">{{ $resource->promo_label }}</p>
                            @endif
                            @if($resource->addonDescription())
                                <p class="mt-2 border-t border-white/10 pt-2 text-xs text-zinc-400">
                                    Add-on: {{ $resource->addonDescription() }} / hour
                                </p>
                            @endif
                        </div>
                    @endif

                    <dl class="mt-5 space-y-2 text-sm">
                        <div class="flex justify-between gap-3"><dt class="text-zinc-500">Capacity</dt><dd class="text-right text-zinc-300">{{ $resource->capacity }} players</dd></div>
                        <div class="flex justify-between gap-3"><dt class="text-zinc-500">Default session</dt><dd class="text-right text-zinc-300">{{ $policy['default_duration'] }} min</dd></div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-zinc-500">Minimum</dt>
                            <dd class="text-right text-zinc-300">{{ $policy['min_duration'] }} min</dd>
                        </div>
                        @if($resource->isFixed() && $resource->packageLabel())
                            <div class="flex justify-between gap-3"><dt class="text-zinc-500">Package</dt><dd class="text-right text-zinc-300">{{ $resource->packageLabel() }}</dd></div>
                        @endif
                        <div class="flex justify-between gap-3">
                            <dt class="text-zinc-500">Booking</dt>
                            <dd class="text-right text-zinc-300">{{ $resource->isOnlineBookable() ? 'Online or walk-in' : 'At the counter' }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            @if($promotions->isNotEmpty() && ($settings['show_hourly_rate'] ?? '1') === '1')
                <div class="neon-card p-5">
                    <h2 class="text-sm font-semibold text-white">Rate windows</h2>
                    <p class="mt-1 text-xs text-zinc-500">These offers apply automatically to the times shown.</p>
                    <ul class="mt-3 space-y-2.5 text-xs">
                        @foreach($promotions as $promotion)
                            <li class="flex items-start justify-between gap-3">
                                <span class="min-w-0">
                                    <span class="block break-words font-medium text-zinc-200">{{ $promotion->name }}</span>
                                    <span class="text-zinc-500">
                                        {{ $promotion->day_of_week !== null ? ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'][$promotion->day_of_week].' · ' : '' }}
                                        {{ substr((string) $promotion->start_time, 0, 5) }}–{{ substr((string) $promotion->end_time, 0, 5) }}
                                        @if($promotion->min_minutes > 0) · min {{ $promotion->min_minutes }} min @endif
                                    </span>
                                </span>
                                <span class="shrink-0 font-semibold text-neon-300">
                                    {{ number_format($resource->isPerMinute() ? $promotion->hourly_rate / 60 : $promotion->hourly_rate, 0) }}
                                    {{ $resource->business?->currency ?? 'PKR' }}/{{ $resource->isPerMinute() ? 'min' : 'hr' }}
                                </span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @guest
                @if($resource->isOnlineBookable())
                    <div class="neon-card border-neon-500/30 p-5">
                        <p class="text-sm text-zinc-300">You need an account to complete a booking.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <a href="{{ route('register') }}" class="btn-neon !py-1.5 !text-xs">Sign up</a>
                            <a href="{{ route('login') }}" class="btn-ghost !py-1.5 !text-xs">Login</a>
                        </div>
                    </div>
                @endif
            @endguest
        </aside>

        {{-- Planner --}}
        <div class="lg:col-span-2">
            @if($resource->isOnlineBookable())
                <form id="booking-form" method="POST" action="{{ route('public.store') }}" class="neon-card p-5 sm:p-6">
                    @csrf
                    <input type="hidden" name="resource_id" value="{{ $resource->id }}">
                    <input type="hidden" name="time" id="time-input">

                    <h2 class="neon-title mb-4 text-base">1. Pick a date & time</h2>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="neon-label" for="date">Date</label>
                            <input type="date" id="date" name="date" value="{{ old('date', $date) }}"
                                   min="{{ now()->format('Y-m-d') }}"
                                   max="{{ now()->addDays($policy['max_advance_days'])->format('Y-m-d') }}"
                                   class="input-neon" required>
                        </div>
                        <div>
                            <label class="neon-label" for="duration">Duration</label>
                            <select id="duration" name="duration_minutes" class="select-neon">
                                @for($m = $policy['min_duration']; $m <= $policy['max_duration']; $m += 30)
                                    <option value="{{ $m }}" @selected($m === $policy['default_duration'])>
                                        {{ $m % 60 === 0 ? ($m / 60).' hour'.($m === 60 ? '' : 's') : $m.' minutes' }}
                                    </option>
                                @endfor
                            </select>
                            @if($resource->maximumMinutes())
                                <p class="mt-1 text-xs text-zinc-500">Maximum {{ $resource->maximumMinutes() }} minutes per booking.</p>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="neon-label">Available slots</label>
                        <div id="slots" class="flex flex-wrap gap-2">
                            <p class="text-sm text-zinc-500">Choose a date to see availability.</p>
                        </div>
                        <p id="slots-message" class="mt-2 text-xs text-rose-300"></p>
                    </div>

                    <h2 class="neon-title mb-4 mt-8 text-base">2. Your details</h2>
                    <div class="grid gap-4 sm:grid-cols-2">
                        @foreach($fields as $field)
                            @php($value = old("fields.{$field->id}"))
                            <div class="{{ $field->type === 'textarea' ? 'sm:col-span-2' : '' }}">
                                <label class="neon-label">
                                    {{ $field->label }}
                                    @if($field->is_required)<span class="text-neon-400">*</span>@endif
                                </label>

                                @switch($field->type)
                                    @case('textarea')
                                        <textarea name="fields[{{ $field->id }}]" rows="3" class="input-neon"
                                                  placeholder="{{ $field->placeholder }}">{{ $value }}</textarea>
                                        @break

                                    @case('select')
                                        <select name="fields[{{ $field->id }}]" class="select-neon">
                                            <option value="">Select…</option>
                                            @foreach($field->choiceOptions() as $option)
                                                <option value="{{ $option }}" @selected($value === $option)>{{ $option }}</option>
                                            @endforeach
                                        </select>
                                        @break

                                    @case('checkbox')
                                        <label class="flex items-center gap-2 text-sm text-zinc-300">
                                            <input type="checkbox" name="fields[{{ $field->id }}]" value="Yes"
                                                   class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500" @checked($value)>
                                            {{ $field->placeholder ?: 'Yes' }}
                                        </label>
                                        @break

                                    @default
                                        <input type="{{ in_array($field->type, ['number','email','tel','date','time']) ? $field->type : 'text' }}"
                                               name="fields[{{ $field->id }}]" value="{{ $value }}"
                                               placeholder="{{ $field->placeholder }}" class="input-neon">
                                @endswitch
                            </div>
                        @endforeach

                        <div class="sm:col-span-2">
                            <label class="neon-label" for="notes">Notes (optional)</label>
                            <textarea id="notes" name="notes" rows="2" class="input-neon" placeholder="Anything else we should know?">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    {{-- Price summary --}}
                    <div class="mt-6 rounded-xl border border-neon-500/25 bg-neon-500/[0.06] p-4">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <h3 class="text-sm font-semibold text-white">Price breakdown</h3>
                            <span id="price-total" class="font-display text-2xl font-bold text-neon-400">—</span>
                        </div>
                        <div id="price-breakdown" class="mt-2 space-y-1 text-xs text-zinc-400">
                            <p>Select a slot to calculate the exact price.</p>
                        </div>
                        <p id="price-window" class="mt-2 text-xs text-zinc-500"></p>
                        <p id="price-note" class="mt-1 text-xs text-amber-300"></p>
                    </div>

                    <button type="submit" id="submit-btn"
                            class="btn-neon mt-5 w-full !py-3"
                            @guest disabled title="Sign in to complete this booking" @endguest>
                        {{ auth()->check() ? 'Confirm booking' : 'Sign in to confirm' }}
                    </button>
                    <p class="mt-2 text-center text-xs text-zinc-500">No payment is taken online — you pay at the counter. Open 24/7.</p>
                    <p class="mt-2 text-center text-xs text-zinc-500">
                        Questions?
                        <a href="{{ $resource->business?->whatsAppUrl() ?? 'https://wa.me/'.config('site.whatsapp_number') }}"
                           target="_blank" rel="noopener noreferrer"
                           class="font-semibold text-emerald-300 transition hover:text-emerald-200">Message us on WhatsApp</a>
                    </p>
                </form>
            @else
                <div class="neon-card p-6 sm:p-8">
                    <span class="badge border-neon-500/30 bg-neon-500/10 text-neon-300">
                        <x-icon name="tag" class="h-3.5 w-3.5" /> {{ $resource->isFixed() ? 'Fixed-price package' : 'Counter booking' }}
                    </span>
                    <h2 class="mt-4 font-display text-2xl font-black uppercase tracking-tight text-white">
                        Available at the counter
                    </h2>
                    <p class="mt-3 max-w-xl text-sm leading-relaxed text-zinc-400">
                        {{ $resource->name }} is arranged by our team in person — pop in or get in touch and we will set it up for you.
                    </p>

                    @if(($settings['show_hourly_rate'] ?? '1') === '1')
                        <div class="mt-6 rounded-xl border border-neon-500/25 bg-neon-500/[0.06] p-4">
                            <x-activity-price :activity="$resource" />
                        </div>
                    @endif

                    <div class="mt-6 flex flex-wrap gap-3">
                        <a href="{{ route('public.contact') }}" class="btn-neon !rounded-xl !px-5 !py-2.5">
                            <x-icon name="phone" class="h-4 w-4" /> Contact us
                        </a>
                        <a href="tel:+923422631057" class="btn-ghost !rounded-xl !px-5 !py-2.5">
                            <x-icon name="phone" class="h-4 w-4" /> +92 342 2631057
                        </a>
                        <x-whatsapp-button variant="ghost" label="WhatsApp us" class="!rounded-xl !px-5 !py-2.5" />
                        <a href="{{ route('public.index') }}#activities" class="btn-ghost !rounded-xl !px-5 !py-2.5">
                            Browse activities
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
@if($resource->isOnlineBookable())
<script>
(function () {
  function init() {
    const resourceId = {{ $resource->id }};
    const policyMin = {{ $policy['min_duration'] }};
    const policyMax = {{ $policy['max_duration'] }};
    const isFixedPackage = {{ $policy['fixed'] ? 'true' : 'false' }};
    const slotsEl = document.getElementById('slots');
    const msgEl = document.getElementById('slots-message');
    const dateEl = document.getElementById('date');
    const durationEl = document.getElementById('duration');
    const timeInput = document.getElementById('time-input');
    const totalEl = document.getElementById('price-total');
    const breakdownEl = document.getElementById('price-breakdown');
    const windowEl = document.getElementById('price-window');
    const noteEl = document.getElementById('price-note');
    const submitBtn = document.getElementById('submit-btn');

    let selectedTime = null;
    let minAllowed = policyMin;
    const money = (n) => Number(n).toLocaleString(undefined, {minimumFractionDigits: 0, maximumFractionDigits: 2});

    function durationLabel(minutes) {
        return minutes % 60 === 0
            ? (minutes / 60) + ' hour' + (minutes === 60 ? '' : 's')
            : minutes + ' minutes';
    }

    // Rate windows can demand a longer minimum (e.g. the 3-hour PC special).
    // Rebuild the duration choices so the form never offers an invalid length.
    function applyMinimum(minutes) {
        if (isFixedPackage) return false;

        minutes = Math.max(policyMin, Math.ceil(minutes / 30) * 30);
        if (minutes <= minAllowed) return false;

        minAllowed = minutes;
        const previous = Number(durationEl.value);
        durationEl.innerHTML = '';

        for (let m = minutes; m <= policyMax; m += 30) {
            const option = document.createElement('option');
            option.value = m;
            option.textContent = durationLabel(m);
            durationEl.appendChild(option);
        }

        durationEl.value = String(Math.max(previous, minutes));

        return true;
    }

    async function loadSlots() {
        slotsEl.innerHTML = '<p class="text-sm text-zinc-500">Loading…</p>';
        msgEl.textContent = '';
        try {
            const res = await fetch(`{{ route('public.slots') }}?resource_id=${resourceId}&date=${dateEl.value}`, {
                headers: {'Accept': 'application/json'}
            });
            const data = await res.json();

            if (!data.business_open) {
                slotsEl.innerHTML = '<p class="text-sm text-rose-300">' + (data.message || 'Not bookable on this date.') + '</p>';
                return;
            }
            if (!data.slots.length) {
                slotsEl.innerHTML = '<p class="text-sm text-zinc-500">No slots available on this date.</p>';
                return;
            }

            slotsEl.innerHTML = '';
            data.slots.forEach(slot => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.disabled = !slot.available;
                btn.dataset.time = slot.time;

                if (slot.available) {
                    btn.className = 'rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2.5 text-xs font-semibold text-zinc-200 transition hover:border-neon-500/60 hover:bg-neon-500/10 hover:text-white';
                    btn.textContent = slot.label;
                    btn.addEventListener('click', () => selectSlot(btn, slot));
                } else {
                    btn.className = 'cursor-not-allowed rounded-lg border border-white/5 bg-white/[0.01] px-3 py-2.5 text-xs font-semibold text-zinc-600';
                    btn.title = 'Unavailable — this slot is already booked';
                    btn.setAttribute('aria-disabled', 'true');
                    btn.innerHTML = '<span class="line-through">' + slot.label + '</span>' +
                        '<span class="ml-1 text-[10px] font-bold uppercase tracking-wide text-rose-400/70">booked</span>';
                }

                slotsEl.appendChild(btn);
            });
        } catch (e) {
            slotsEl.innerHTML = '<p class="text-sm text-rose-300">Could not load availability.</p>';
        }
    }

    function selectSlot(btn, slot) {
        selectedTime = slot.time;
        timeInput.value = slot.time;
        document.querySelectorAll('#slots button').forEach(b => b.classList.remove('!border-neon-500', '!bg-neon-500/20', '!text-white'));
        btn.classList.add('!border-neon-500', '!bg-neon-500/20', '!text-white');
        if (slot.minutes && [...durationEl.options].some(o => +o.value === slot.minutes)) {
            durationEl.value = slot.minutes;
        }
        loadPrice();
    }

    function minimumLabel(minutes) {
        return minutes % 60 === 0 ? (minutes / 60) + ' hour' + (minutes === 60 ? '' : 's') : minutes + ' minutes';
    }

    async function loadPrice() {
        if (!selectedTime) return;
        breakdownEl.innerHTML = '<p>Calculating…</p>';
        const requestedDuration = durationEl.value;
        noteEl.textContent = '';
        try {
            const url = `{{ route('public.price') }}?resource_id=${resourceId}` +
                `&starts_at=${encodeURIComponent(dateEl.value + 'T' + selectedTime)}` +
                `&duration_minutes=${durationEl.value}`;
            const res = await fetch(url, {headers: {'Accept': 'application/json'}});
            const data = await res.json();

            if (data.min_minutes && applyMinimum(data.min_minutes)) {
                noteEl.textContent = 'A minimum booking of ' + minimumLabel(minAllowed) + ' applies at this time — your duration was adjusted.';
                if (Number(durationEl.value) !== Number(requestedDuration)) {
                    loadPrice();
                    return;
                }
            }

            if (!data.ok) {
                totalEl.textContent = '—';
                breakdownEl.innerHTML = '<p class="text-rose-300">' + (data.message || 'This slot is not available.') + '</p>';
                if (submitBtn) submitBtn.disabled = true;
                return;
            }

            if (submitBtn && {{ auth()->check() ? 'true' : 'false' }}) submitBtn.disabled = false;

            totalEl.textContent = money(data.price.total);
            breakdownEl.innerHTML = data.price.segments.map(seg =>
                `<div class="flex justify-between gap-3"><span>${seg.start}–${seg.end} · ${seg.minutes} min${seg.rule ? ' · ' + seg.rule : ''}</span><span class="shrink-0">${money(seg.cost)}</span></div>`
            ).join('') + `<div class="flex justify-between gap-3 border-t border-neon-500/20 pt-1 font-semibold text-zinc-200"><span>Total (${data.price.minutes} min)</span><span>${money(data.price.total)}</span></div>`;
            windowEl.textContent = data.window ? `${data.window.starts_at} → ${data.window.ends_at}` : '';
        } catch (e) {
            breakdownEl.innerHTML = '<p class="text-rose-300">Could not calculate the price.</p>';
        }
    }

    // Block submission until a slot is chosen, with a helpful message.
    document.getElementById('booking-form').addEventListener('submit', (e) => {
        if (!timeInput.value) {
            e.preventDefault();
            msgEl.textContent = 'Please pick an available time slot before confirming.';
            msgEl.scrollIntoView({behavior: 'smooth', block: 'center'});
        } else if (Number(durationEl.value) < minAllowed) {
            e.preventDefault();
            msgEl.textContent = 'The minimum booking at this time is ' + minimumLabel(minAllowed) + '.';
        }
    });

    dateEl.addEventListener('change', () => {
        selectedTime = null;
        timeInput.value = '';
        minAllowed = policyMin;
        noteEl.textContent = '';

        if (!isFixedPackage) {
            durationEl.innerHTML = '';
            for (let m = policyMin; m <= policyMax; m += 30) {
                const option = document.createElement('option');
                option.value = m;
                option.textContent = durationLabel(m);
                durationEl.appendChild(option);
            }
            durationEl.value = String(Math.max(Number(durationEl.value), policyMin));
        }

        loadSlots();
    });
    durationEl.addEventListener('change', loadPrice);

    loadSlots();
  }

  if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', init);
  } else {
      init();
  }
})();
</script>
@endif
@endpush
