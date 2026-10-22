@props([
    'label' => 'WhatsApp Now',
    'message' => null,
    'variant' => 'primary',
])

@php
    $business = \App\Models\Business::primary();

    $number = $business?->whatsAppNumber()
        ?: preg_replace('/\D+/', '', (string) config('site.whatsapp_number', '923422631057'));

    $text = $message ?: config('site.whatsapp_message');

    $href = 'https://wa.me/'.$number.'?text='.rawurlencode($text);

    $base = $variant === 'ghost' ? 'btn-ghost' : 'btn-neon';
@endphp

<a href="{{ $href }}" target="_blank" rel="noopener noreferrer"
   {{ $attributes->class([$base]) }}>
    <x-icon name="whatsapp" class="h-4 w-4" />
    <span>{{ $label }}</span>
</a>
