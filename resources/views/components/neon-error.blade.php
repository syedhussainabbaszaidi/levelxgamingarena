@props(['name'])

@error($name)
    <p {{ $attributes->merge(['class' => 'mt-1 text-xs text-rose-400']) }}>{{ $message }}</p>
@enderror
