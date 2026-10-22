@php($businessId = \App\Models\Business::primary()?->id ?? 1)
@php($rule = $rule ?? null)

<div class="space-y-4">
    <input type="hidden" name="business_id" value="{{ $businessId }}">

    <div>
        <label class="neon-label" for="rule-name">Rule name</label>
        <input type="text" name="name" id="rule-name" required value="{{ old('name', $rule->name ?? '') }}"
               placeholder="Peak hours — Snooker" class="input-neon @error('name') input-invalid @enderror">
        <x-neon-error name="name" />
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div>
            <label class="neon-label">Category</label>
            <select name="category_id" class="select-neon">
                <option value="">All</option>
                @foreach($categories as $c)
                    <option value="{{ $c->id }}" @selected(old('category_id', $rule->category_id ?? '') == $c->id)>{{ $c->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="neon-label">Resource</label>
            <select name="resource_id" class="select-neon">
                <option value="">All</option>
                @foreach($resources as $r)
                    <option value="{{ $r->id }}" @selected(old('resource_id', $rule->resource_id ?? '') == $r->id)>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="neon-label">Day</label>
            <select name="day_of_week" class="select-neon">
                <option value="">Every day</option>
                @foreach(['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $i => $d)
                    <option value="{{ $i }}" @selected((string) old('day_of_week', $rule->day_of_week ?? '') === (string) $i)>{{ $d }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-3">
        <div>
            <label class="neon-label">Start time</label>
            <input type="time" name="start_time" required value="{{ old('start_time', $rule->start_time ?? '10:00') }}"
                   class="input-neon @error('start_time') input-invalid @enderror">
            <x-neon-error name="start_time" />
        </div>
        <div>
            <label class="neon-label">End time</label>
            <input type="time" name="end_time" required value="{{ old('end_time', $rule->end_time ?? '18:00') }}"
                   class="input-neon @error('end_time') input-invalid @enderror">
            <x-neon-error name="end_time" />
        </div>
        <div class="flex items-end pb-2">
            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-300">
                <input type="hidden" name="overnight" value="0">
                <input type="checkbox" name="overnight" value="1" @checked(old('overnight', $rule->overnight ?? false))
                       class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500">
                Overnight (+1 day)
            </label>
        </div>
    </div>

    <div class="grid gap-3 sm:grid-cols-4">
        <div>
            <label class="neon-label">Rate / hour</label>
            <input type="number" step="0.01" min="0" name="hourly_rate" required value="{{ old('hourly_rate', $rule->hourly_rate ?? '') }}"
                   class="input-neon @error('hourly_rate') input-invalid @enderror">
            <x-neon-error name="hourly_rate" />
        </div>
        <div>
            <label class="neon-label">Min minutes</label>
            <input type="number" min="15" name="min_minutes" required value="{{ old('min_minutes', $rule->min_minutes ?? 30) }}" class="input-neon">
        </div>
        <div>
            <label class="neon-label">Slot (min)</label>
            <input type="number" min="15" name="slot_minutes" required value="{{ old('slot_minutes', $rule->slot_minutes ?? 30) }}" class="input-neon">
        </div>
        <div>
            <label class="neon-label">Priority</label>
            <input type="number" min="0" name="priority" required value="{{ old('priority', $rule->priority ?? 10) }}" class="input-neon">
        </div>
    </div>

    <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-300">
        <input type="hidden" name="is_active" value="0">
        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $rule->is_active ?? true))
               class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500">
        Active
    </label>
</div>
