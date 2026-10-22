@extends('layouts.admin')

@section('title', 'Booking Configuration')
@section('subtitle', 'Control what customers see and how bookings behave')

@section('content')
    <form method="POST" action="{{ route('admin.config.update') }}" class="grid gap-6 lg:grid-cols-2">
        @csrf
        @method('PATCH')

        <div class="neon-card p-5">
            <h2 class="mb-1 text-sm font-semibold text-white">Customer interface display</h2>
            <p class="mb-4 text-xs text-zinc-500">Toggle these to change the customer booking site instantly — no code changes.</p>

            <div class="space-y-1">
                @foreach($toggles as $key => $label)
                    <label class="flex cursor-pointer items-center justify-between rounded-lg px-3 py-2.5 transition hover:bg-white/[0.03]">
                        <span class="text-sm text-zinc-300">{{ $label }}</span>
                        <input type="checkbox" name="{{ $key }}" value="1"
                               class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                               @checked(($settings[$key] ?? '0') === '1')>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="neon-card space-y-4 p-5">
            <div>
                <h2 class="mb-1 text-sm font-semibold text-white">Booking policy</h2>
                <p class="text-xs text-zinc-500">Applied to every online booking across all activities.</p>
            </div>

            <div>
                <label class="neon-label">Slot length (minutes)</label>
                <input type="number" name="slot_minutes" value="{{ old('slot_minutes', $business->slot_minutes ?? 60) }}" class="input-neon" min="15" max="240" required>
            </div>
            <div>
                <label class="neon-label">Minimum booking duration (minutes)</label>
                <input type="number" name="min_duration_minutes" value="{{ old('min_duration_minutes', $business->min_duration_minutes ?? 30) }}" class="input-neon" min="15" max="480" required>
            </div>
            <div>
                <label class="neon-label">Book up to (days ahead)</label>
                <input type="number" name="max_advance_days" value="{{ old('max_advance_days', $business->max_advance_days ?? 30) }}" class="input-neon" min="1" max="365" required>
            </div>
            <div>
                <label class="neon-label">Free cancellation window (hours before start)</label>
                <input type="number" name="cancellation_window_hours" value="{{ old('cancellation_window_hours', $settings['cancellation_window_hours'] ?? 2) }}" class="input-neon" min="0" max="168" required>
            </div>

            <button class="btn-neon w-full">Save configuration</button>
            <p class="text-xs text-zinc-500">Business hours: {{ $business?->opens_at ? $business->hoursLabel() : '—' }}</p>
        </div>
    </form>
@endsection
