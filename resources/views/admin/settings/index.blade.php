@extends('layouts.admin')

@section('title', 'System Settings')
@section('subtitle', 'Business profile, hours and booking preferences')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="mx-auto max-w-3xl space-y-6">
        @csrf @method('PATCH')

        <div class="neon-card space-y-5 p-6">
            <div>
                <h2 class="text-sm font-semibold text-white">Business profile</h2>
                <p class="text-xs text-zinc-500">Shown to customers on the booking site and receipts.</p>
            </div>

            <div>
                <label class="neon-label" for="name">Business name</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $business->name) }}"
                       class="input-neon @error('name') input-invalid @enderror">
                <x-neon-error name="name" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="neon-label" for="phone">Phone</label>
                    <input type="text" name="phone" id="phone" value="{{ old('phone', $business->phone) }}"
                           class="input-neon @error('phone') input-invalid @enderror">
                    <x-neon-error name="phone" />
                </div>
                <div>
                    <label class="neon-label" for="currency">Currency</label>
                    <input type="text" name="currency" id="currency" required value="{{ old('currency', $business->currency) }}"
                           class="input-neon @error('currency') input-invalid @enderror">
                    <x-neon-error name="currency" />
                </div>
            </div>

            <div>
                <label class="neon-label" for="address">Address</label>
                <textarea name="address" id="address" rows="2" class="input-neon @error('address') input-invalid @enderror">{{ old('address', $business->address) }}</textarea>
                <x-neon-error name="address" />
            </div>
        </div>

        <div class="neon-card space-y-5 p-6">
            <h2 class="text-sm font-semibold text-white">Business hours</h2>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="neon-label" for="opens_at">Opens at</label>
                    <input type="time" name="opens_at" id="opens_at" required value="{{ old('opens_at', $business->opens_at->format('H:i')) }}"
                           class="input-neon @error('opens_at') input-invalid @enderror">
                    <x-neon-error name="opens_at" />
                </div>
                <div>
                    <label class="neon-label" for="closes_at">Closes at</label>
                    <input type="time" name="closes_at" id="closes_at" required value="{{ old('closes_at', $business->closes_at->format('H:i')) }}"
                           class="input-neon @error('closes_at') input-invalid @enderror">
                    <x-neon-error name="closes_at" />
                </div>
            </div>

            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-300">
                <input type="hidden" name="overnight" value="0">
                <input type="checkbox" name="overnight" value="1" @checked(old('overnight', $business->overnight))
                       class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500">
                Open past midnight (closes next day, e.g. 10:00 → 02:00)
            </label>

            <p class="rounded-lg border border-neon-500/20 bg-neon-500/[0.06] px-4 py-3 text-xs text-zinc-300">
                Currently <span class="font-semibold text-neon-300">{{ $business->hoursLabel() }}</span>.
                For 24-hour operation set both times to <span class="font-semibold text-white">00:00</span> and keep
                “Open past midnight” ticked — the booking grid then covers the whole day and the website shows 24/7 availability.
            </p>

            <div>
                <label class="neon-label" for="timezone">Timezone</label>
                <input type="text" name="timezone" id="timezone" required value="{{ old('timezone', $business->timezone) }}"
                       class="input-neon @error('timezone') input-invalid @enderror">
                <x-neon-error name="timezone" />
            </div>
        </div>

        <div class="neon-card space-y-5 p-6">
            <h2 class="text-sm font-semibold text-white">Booking preferences</h2>

            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-300">
                <input type="hidden" name="auto_confirm_online" value="0">
                <input type="checkbox" name="auto_confirm_online" value="1" @checked(\App\Models\Setting::get('auto_confirm_online') === '1')
                       class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500">
                Auto-confirm online bookings (otherwise they stay “pending” until staff approve)
            </label>

            <div class="max-w-xs">
                <label class="neon-label" for="cancellation_window_hours">Cancellation window (hours before start)</label>
                <input type="number" name="cancellation_window_hours" id="cancellation_window_hours" min="0" max="168" required
                       value="{{ old('cancellation_window_hours', \App\Models\Setting::get('cancellation_window_hours', 2)) }}"
                       class="input-neon @error('cancellation_window_hours') input-invalid @enderror">
                <x-neon-error name="cancellation_window_hours" />
            </div>
        </div>

        <div class="flex justify-end">
            <button class="btn-neon">Save settings</button>
        </div>
    </form>
@endsection
