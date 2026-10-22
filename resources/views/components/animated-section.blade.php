@props(['tagline' => ''])

<div data-reveal class="reveal-section">
    @if($tagline)
        <p class="text-xs uppercase tracking-widest text-neon-500/70">{{ $tagline }}</p>
    @endif
    {{ $slot }}
</div>
