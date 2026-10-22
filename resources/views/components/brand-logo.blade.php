@props(['height' => 'h-9', 'href' => null, 'showName' => false])

@php
    $logo = asset('img/logo.png');
    $name = \App\Models\Business::primary()?->name ?? config('app.name');
@endphp

@if($href)
    <a href="{{ $href }}" class="inline-flex min-w-0 items-center gap-2" aria-label="{{ $name }} — home">
@endif

<img src="{{ $logo }}"
     alt="{{ $name }} — E-Sports and Gaming Arena"
     width="1400" height="379" decoding="async"
     {{ $attributes->merge(['class' => $height.' w-auto max-w-full object-contain']) }}>

@if($showName)
    <span class="sr-only">{{ $name }}</span>
@endif

@if($href)
    </a>
@endif
