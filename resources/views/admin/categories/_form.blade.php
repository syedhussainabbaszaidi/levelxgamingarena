@php($category = $category ?? null)

<div class="space-y-5">
    <div>
        <label class="neon-label" for="business_id">Business</label>
        <select name="business_id" id="business_id" required class="select-neon @error('business_id') input-invalid @enderror">
            @foreach($businesses as $b)
                <option value="{{ $b->id }}" @selected(old('business_id', $category->business_id ?? '') == $b->id)>{{ $b->name }}</option>
            @endforeach
        </select>
        <x-neon-error name="business_id" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="neon-label" for="name">Name</label>
            <input type="text" name="name" id="name" required value="{{ old('name', $category->name ?? '') }}"
                   placeholder="Snooker" class="input-neon @error('name') input-invalid @enderror">
            <x-neon-error name="name" />
        </div>
        <div>
            <label class="neon-label" for="slug">Slug</label>
            <input type="text" name="slug" id="slug" required value="{{ old('slug', $category->slug ?? '') }}"
                   placeholder="snooker" class="input-neon @error('slug') input-invalid @enderror">
            <x-neon-error name="slug" />
        </div>
    </div>

    <div>
        <label class="neon-label" for="description">Description</label>
        <textarea name="description" id="description" rows="2" placeholder="Optional summary"
                  class="input-neon @error('description') input-invalid @enderror">{{ old('description', $category->description ?? '') }}</textarea>
        <x-neon-error name="description" />
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div>
            <label class="neon-label" for="color">Colour</label>
            <input type="color" name="color" id="color" value="{{ old('color', $category->color ?? '#ff2d55') }}"
                   class="input-neon h-11 !p-1.5">
            <x-neon-error name="color" />
        </div>
        <div class="flex items-end pb-1">
            <label class="flex cursor-pointer items-center gap-2.5 text-sm text-zinc-300">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))
                       class="rounded border-white/20 bg-ink-900 text-neon-600 focus:ring-neon-500">
                Active (visible for booking)
            </label>
        </div>
    </div>
</div>
