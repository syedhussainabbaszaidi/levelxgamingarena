@extends('layouts.admin')

@section('title', 'Time Slots')
@section('subtitle', 'Configure the bookable windows offered to customers')

@php($editing = request('edit') ? \App\Models\TimeSlot::find(request('edit')) : null)

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        <div class="neon-card p-5 lg:col-span-1">
            <h2 class="mb-1 text-sm font-semibold text-white">{{ $editing ? 'Edit slot' : 'Add time slot' }}</h2>
            <p class="mb-4 text-xs text-zinc-500">A shared slot (no activity) applies everywhere. Activity slots override the generated grid.</p>

            <form method="POST" action="{{ $editing ? route('admin.slots.update', $editing) : route('admin.slots.store') }}" class="space-y-4">
                @csrf
                @if($editing) @method('PATCH') @endif

                <div>
                    <label class="neon-label">Activity</label>
                    <select name="resource_id" class="select-neon">
                        <option value="">Shared (all activities)</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource->id }}" @selected(old('resource_id', $editing?->resource_id) == $resource->id)>{{ $resource->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="neon-label">Label</label>
                    <input name="label" value="{{ old('label', $editing?->label) }}" class="input-neon" placeholder="Evening 6–8 PM" required>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="neon-label">Start</label>
                        <input type="time" name="start_time" value="{{ old('start_time', $editing ? substr($editing->start_time, 0, 5) : '18:00') }}" class="input-neon" required>
                    </div>
                    <div>
                        <label class="neon-label">End</label>
                        <input type="time" name="end_time" value="{{ old('end_time', $editing ? substr($editing->end_time, 0, 5) : '20:00') }}" class="input-neon" required>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="neon-label">Day</label>
                        <select name="day_of_week" class="select-neon">
                            <option value="">Every day</option>
                            @foreach($days as $index => $day)
                                <option value="{{ $index }}" @selected(old('day_of_week', $editing?->day_of_week) === $index)>{{ $day }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="neon-label">Slot length (min)</label>
                        <input type="number" name="slot_minutes" value="{{ old('slot_minutes', $editing?->slot_minutes ?? 60) }}" class="input-neon" min="15" max="480">
                    </div>
                </div>

                <div>
                    <label class="neon-label">Sort order</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $editing?->sort_order ?? 0) }}" class="input-neon" min="0">
                </div>

                <label class="flex items-center gap-2 text-sm text-zinc-300">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                           @checked(old('is_active', $editing?->is_active ?? true))> Active
                </label>

                <div class="flex gap-2">
                    <button class="btn-neon">{{ $editing ? 'Save changes' : 'Add slot' }}</button>
                    @if($editing)<a href="{{ route('admin.slots.index') }}" class="btn-ghost">Cancel</a>@endif
                </div>
            </form>
        </div>

        <div class="neon-card overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Configured slots</h2>
                <form method="GET">
                    <select name="resource" class="select-neon !w-auto !py-1.5 text-xs" onchange="this.form.submit()">
                        <option value="">All scopes</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource->id }}" @selected((string) $selected === (string) $resource->id)>{{ $resource->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table-neon">
                    <thead>
                        <tr><th>Slot</th><th>Scope</th><th>Window</th><th>Day</th><th>Length</th><th class="text-right">Actions</th></tr>
                    </thead>
                    <tbody>
                        @forelse($slots as $slot)
                            <tr>
                                <td class="font-medium text-white">{{ $slot->label }}</td>
                                <td class="text-xs">
                                    @if($slot->resource_id)
                                        <span class="badge border-white/10 bg-white/[0.03] text-zinc-300">{{ $slot->resource?->name }}</span>
                                    @else
                                        <span class="badge border-neon-500/30 bg-neon-500/10 text-neon-300">Shared</span>
                                    @endif
                                </td>
                                <td class="text-xs text-zinc-400">{{ $slot->timeLabel() }}</td>
                                <td class="text-xs text-zinc-400">{{ $slot->dayLabel() }}</td>
                                <td class="text-xs text-zinc-400">{{ $slot->slot_minutes }} min</td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.slots.index', ['edit' => $slot->id]) }}" class="btn-mini">Edit</a>
                                        <form method="POST" action="{{ route('admin.slots.destroy', $slot) }}" onsubmit="return confirm('Delete this slot?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="py-8 text-center text-sm text-zinc-500">No time slots configured — the booking grid is generated from business hours.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
