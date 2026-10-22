@extends('layouts.admin')

@section('title', 'New Booking')
@section('subtitle', 'Create a reservation on behalf of a customer')

@section('header')
    <a href="{{ route('admin.bookings.index') }}" class="btn-ghost">← All bookings</a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.bookings.store') }}" class="grid gap-6 lg:grid-cols-3">
        @csrf

        {{-- Main details --}}
        <div class="neon-card space-y-5 p-6 lg:col-span-2">
            <div>
                <label class="neon-label" for="resource_id">Activity / resource</label>
                <select name="resource_id" id="resource_id" required class="select-neon @error('resource_id') input-invalid @enderror">
                    <option value="">— select —</option>
                    @foreach($categories as $cat)
                        @php($groupResources = $resources->where('category_id', $cat->id))
                        @if($groupResources->isNotEmpty())
                            <optgroup label="{{ $cat->name }}">
                                @foreach($groupResources as $r)
                                    <option value="{{ $r->id }}" @selected(old('resource_id') == $r->id) @if($r->status !== 'active')disabled @endif>
                                        {{ $r->name }}@if($r->status !== 'active') ({{ $r->status }})@endif
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <x-neon-error name="resource_id" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="neon-label" for="starts_at">Date &amp; start time</label>
                    <input type="datetime-local" name="starts_at" id="starts_at" required
                           value="{{ old('starts_at', $date.'T'.now()->format('H:i')) }}"
                           class="input-neon @error('starts_at') input-invalid @enderror">
                    <x-neon-error name="starts_at" />
                </div>
                <div>
                    <label class="neon-label" for="duration_minutes">Duration</label>
                    <select name="duration_minutes" id="duration_minutes" class="select-neon @error('duration_minutes') input-invalid @enderror">
                        @foreach([30, 60, 90, 120, 180, 240, 300] as $m)
                            <option value="{{ $m }}" @selected(old('duration_minutes', 60) == $m)>
                                {{ intdiv($m, 60) }}h{{ $m % 60 ? ' '.($m % 60).'m' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <x-neon-error name="duration_minutes" />
                </div>
            </div>

            <div>
                <label class="neon-label">Live price preview</label>
                <div id="price-box" class="neon-panel text-sm text-zinc-500">
                    Select an activity, time and duration to see the price…
                </div>
            </div>

            <fieldset class="neon-panel space-y-4">
                <legend class="px-1 text-xs font-semibold uppercase tracking-wider text-neon-400">Customer</legend>
                <div>
                    <label class="neon-label" for="customer_id">Existing customer</label>
                    <select name="customer_id" id="customer_id" class="select-neon @error('customer_id') input-invalid @enderror">
                        <option value="">— new / walk-in —</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" @selected(old('customer_id') == $c->id)>{{ $c->name }} ({{ $c->phone }})</option>
                        @endforeach
                    </select>
                    <x-neon-error name="customer_id" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="neon-label" for="customer_name">New customer name</label>
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name') }}"
                               placeholder="Walk-in name" class="input-neon @error('customer_name') input-invalid @enderror">
                        <x-neon-error name="customer_name" />
                    </div>
                    <div>
                        <label class="neon-label" for="customer_phone">New customer phone</label>
                        <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}"
                               placeholder="03xx…" class="input-neon @error('customer_phone') input-invalid @enderror">
                        <x-neon-error name="customer_phone" />
                    </div>
                </div>
                <p class="text-xs text-zinc-500">Leave “existing customer” empty to create a new record from the name and phone above.</p>
            </fieldset>

            <div>
                <label class="neon-label" for="notes">Notes</label>
                <textarea name="notes" id="notes" rows="2" placeholder="Internal notes (optional)"
                          class="input-neon @error('notes') input-invalid @enderror">{{ old('notes') }}</textarea>
                <x-neon-error name="notes" />
            </div>
        </div>

        {{-- Sidebar: advance + actions --}}
        <div class="space-y-6">
            <div class="neon-card space-y-4 p-6">
                <h2 class="text-sm font-semibold text-white">Advance payment</h2>
                <div>
                    <label class="neon-label" for="advance_amount">Amount (optional)</label>
                    <input type="number" step="0.01" min="0" name="advance_amount" id="advance_amount"
                           value="{{ old('advance_amount') }}" placeholder="0.00"
                           class="input-neon @error('advance_amount') input-invalid @enderror">
                    <x-neon-error name="advance_amount" />
                </div>
                <div>
                    <label class="neon-label" for="advance_method">Booking method</label>
                    <select name="advance_method" id="advance_method" class="select-neon">
                        <option value="cash" @selected(old('advance_method') === 'cash')>Cash</option>
                        <option value="online" @selected(old('advance_method') === 'online')>Online</option>
                    </select>
                </div>
            </div>

            <div class="neon-card space-y-3 p-6">
                <button type="submit" class="btn-neon w-full">Create booking</button>
                <a href="{{ route('admin.bookings.index') }}" class="btn-ghost w-full">Cancel</a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        var csrf = document.querySelector('meta[name="csrf-token"]').content;
        var box = document.getElementById('price-box');
        var rid = document.getElementById('resource_id');
        var starts = document.getElementById('starts_at');
        var dur = document.getElementById('duration_minutes');

        if (!box || !rid || !starts || !dur) return;

        function money(value) {
            return Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 });
        }

        function refreshPrice() {
            var resourceId = rid.value;
            var startValue = starts.value;
            if (!resourceId || !startValue) return;

            var warn = '<svg class="mr-1 inline h-4 w-4 align-text-bottom" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3.5l9 16H3z"/><path d="M12 9.5v4M12 16.6h.01"/></svg>';

            fetch('{{ route('admin.bookings.price') }}?resource_id=' + resourceId
                + '&starts_at=' + encodeURIComponent(startValue)
                + '&duration_minutes=' + dur.value)
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.ok) {
                        box.className = 'neon-panel text-sm text-rose-300';
                        box.innerHTML = warn + (data.message || 'Unavailable for this window.');
                        return;
                    }
                    box.className = 'neon-panel text-sm';
                    var html = '<div class="flex items-baseline justify-between">'
                        + '<span class="text-zinc-400">Total (' + data.price.minutes + ' min)</span>'
                        + '<span class="font-display text-xl font-bold text-neon-400">' + money(data.price.total) + '</span>'
                        + '</div>';
                    if (data.price.segments.length > 1) {
                        html += '<div class="mt-3 space-y-1 border-t border-white/[0.06] pt-3 text-xs text-zinc-500">';
                        data.price.segments.forEach(function (s) {
                            html += '<div class="flex justify-between"><span>' + s.start + '–' + s.end + ' · '
                                + s.minutes + 'm × ' + money(s.rate) + '/h'
                                + (s.rule ? ' <span class="text-neon-400">(' + s.rule + ')</span>' : '')
                                + '</span><span class="text-zinc-300">' + money(s.cost) + '</span></div>';
                        });
                        html += '</div>';
                    }
                    box.innerHTML = html;
                })
                .catch(function () {
                    box.className = 'neon-panel text-sm text-rose-300';
                    box.innerHTML = warn + 'Could not load price.';
                });
        }

        [rid, starts, dur].forEach(function (el) { el.addEventListener('change', refreshPrice); });
        refreshPrice();
    })();
</script>
@endpush
