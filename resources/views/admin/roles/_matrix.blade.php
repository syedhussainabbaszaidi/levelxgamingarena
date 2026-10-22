@php($granted = $granted ?? [])

<div class="space-y-4">
    @foreach($groups as $group => $permissions)
        <div class="neon-panel">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-xs font-bold uppercase tracking-widest text-neon-400">{{ $group }}</h3>
                <span class="text-[11px] text-zinc-500">{{ $permissions->count() }} permissions</span>
            </div>
            <div class="grid gap-2 sm:grid-cols-2">
                @foreach($permissions as $permission)
                    <label class="flex cursor-pointer items-start gap-2.5 rounded-lg px-2 py-1.5 transition hover:bg-white/[0.03]">
                        <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                               class="mt-0.5 rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500 disabled:opacity-40"
                               @checked(in_array($permission->id, $granted)) @disabled($disabled ?? false)>
                        <span>
                            <span class="block text-sm text-zinc-200">{{ $permission->name }}</span>
                            <span class="block font-mono text-[11px] text-zinc-500">{{ $permission->slug }}</span>
                        </span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>
