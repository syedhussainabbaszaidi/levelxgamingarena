@props([
    'desktop' => 'sm:grid-cols-2 xl:grid-cols-3',
    'gap' => 'gap-5',
    'label' => 'Cards',
])

{{--
    Horizontal swipe slider on phones, the normal grid from `sm` upwards.
    The first card is shown in full with roughly half of the next one peeking
    in to hint that more content is available. Pagination dots are generated
    and kept in sync by resources/js/public.js (data-slider).
--}}
<div {{ $attributes->class(['mobile-slider']) }} data-slider>
    <div class="mobile-slider__track flex snap-x snap-mandatory overflow-x-auto sm:grid sm:overflow-visible {{ $gap }} {{ $desktop }}">
        {{ $slot }}
    </div>

    <div class="mobile-slider__dots" data-slider-dots aria-label="{{ $label }} pagination"></div>
</div>
