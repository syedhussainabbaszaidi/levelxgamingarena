@extends('layouts.admin')

@section('title', 'Booking Fields')
@section('subtitle', 'Choose which fields the customer booking form shows')

@php
    $editing = request('edit') ? $fields->firstWhere('id', (int) request('edit')) : null;
    $editing = $editing ?: \App\Models\BookingField::find(request('edit'));
@endphp

@section('content')
    <div class="grid gap-6 lg:grid-cols-3">
        {{-- Form --}}
        <div class="neon-card p-5 lg:col-span-1">
            <h2 class="mb-1 text-sm font-semibold text-white">{{ $editing ? 'Edit field' : 'Add booking field' }}</h2>
            <p class="mb-4 text-xs text-zinc-500">Fields apply to a single activity, or to every activity when left global.</p>

            <form method="POST" action="{{ $editing ? route('admin.fields.update', $editing) : route('admin.fields.store') }}" class="space-y-4">
                @csrf
                @if($editing) @method('PATCH') @endif

                <div>
                    <label class="neon-label">Applies to</label>
                    <select name="resource_id" class="select-neon">
                        <option value="">All activities (global)</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource->id }}" @selected(old('resource_id', $editing?->resource_id) == $resource->id)>
                                {{ $resource->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="neon-label">Key</label>
                        <input name="key" value="{{ old('key', $editing?->key) }}" class="input-neon" placeholder="player_name" required>
                    </div>
                    <div>
                        <label class="neon-label">Type</label>
                        <select name="type" class="select-neon">
                            @foreach($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', $editing?->type) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="neon-label">Label</label>
                    <input name="label" value="{{ old('label', $editing?->label) }}" class="input-neon" placeholder="Player name" required>
                </div>

                <div>
                    <label class="neon-label">Placeholder</label>
                    <input name="placeholder" value="{{ old('placeholder', $editing?->placeholder) }}" class="input-neon">
                </div>

                <div>
                    <label class="neon-label">Options (one per line, for dropdowns)</label>
                    <textarea name="options" rows="3" class="input-neon">{{ old('options', $editing ? implode("\n", $editing->choiceOptions()) : '') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="neon-label">Sort order</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', $editing?->sort_order ?? 0) }}" class="input-neon" min="0">
                    </div>
                    <div class="space-y-2 pt-5">
                        <label class="flex items-center gap-2 text-sm text-zinc-300">
                            <input type="checkbox" name="is_required" value="1" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                                   @checked(old('is_required', $editing?->is_required))> Required
                        </label>
                        <label class="flex items-center gap-2 text-sm text-zinc-300">
                            <input type="checkbox" name="is_visible" value="1" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                                   @checked(old('is_visible', $editing?->is_visible ?? true))> Visible
                        </label>
                        <label class="flex items-center gap-2 text-sm text-zinc-300">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500"
                                   @checked(old('is_active', $editing?->is_active ?? true))> Active
                        </label>
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button class="btn-neon">{{ $editing ? 'Save changes' : 'Add field' }}</button>
                    @if($editing)
                        <a href="{{ route('admin.fields.index') }}" class="btn-ghost">Cancel</a>
                    @endif
                </div>
            </form>
        </div>

        {{-- List --}}
        <div class="neon-card overflow-hidden lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/[0.06] px-5 py-4">
                <h2 class="text-sm font-semibold text-white">Configured fields</h2>
                <form method="GET" class="flex gap-2">
                    <select name="resource" class="select-neon !w-auto !py-1.5 text-xs" onchange="this.form.submit()">
                        <option value="">All scopes</option>
                        <option value="global" @selected($selected === 'global')>Global only</option>
                        @foreach($resources as $resource)
                            <option value="{{ $resource->id }}" @selected((string) $selected === (string) $resource->id)>{{ $resource->name }}</option>
                        @endforeach
                    </select>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="table-neon">
                    <thead>
                        <tr>
                            <th>Field</th>
                            <th>Scope</th>
                            <th>Type</th>
                            <th>Rules</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($fields as $field)
                            <tr>
                                <td>
                                    <p class="font-medium text-white">{{ $field->label }}</p>
                                    <p class="font-mono text-xs text-zinc-500">{{ $field->key }}</p>
                                </td>
                                <td class="text-xs">
                                    @if($field->isGlobal())
                                        <span class="badge border-neon-500/30 bg-neon-500/10 text-neon-300">Global</span>
                                    @else
                                        <span class="badge border-white/10 bg-white/[0.03] text-zinc-300">{{ $field->resource?->name }}</span>
                                    @endif
                                </td>
                                <td class="text-xs text-zinc-400">{{ $types[$field->type] ?? $field->type }}</td>
                                <td class="space-x-1 text-xs">
                                    @if($field->is_required)<span class="chip">required</span>@endif
                                    @if($field->is_visible)<span class="chip">visible</span>@endif
                                    @unless($field->is_active)<span class="chip !text-rose-300">disabled</span>@endunless
                                </td>
                                <td>
                                    <div class="flex justify-end gap-2">
                                        <a href="{{ route('admin.fields.index', ['edit' => $field->id]) }}" class="btn-mini">Edit</a>
                                        <form method="POST" action="{{ route('admin.fields.destroy', $field) }}" onsubmit="return confirm('Remove this field?')">
                                            @csrf @method('DELETE')
                                            <button class="btn-danger">Delete</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-8 text-center text-sm text-zinc-500">No booking fields configured yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
