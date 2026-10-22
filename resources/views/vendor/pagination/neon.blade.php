@php
    $activeClasses = 'z-10 inline-flex items-center border border-neon-500/50 bg-neon-500/15 px-3.5 py-2 text-sm font-semibold text-white';
    $idleClasses = 'inline-flex items-center border border-white/10 bg-white/[0.02] px-3.5 py-2 text-sm font-medium text-zinc-400 transition hover:border-neon-500/40 hover:text-white';
    $disabledClasses = 'inline-flex cursor-not-allowed items-center border border-white/[0.06] bg-white/[0.01] px-3.5 py-2 text-sm font-medium text-zinc-600';
    $dotsClasses = 'inline-flex items-center px-3 py-2 text-sm text-zinc-600';
@endphp

@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-xs text-zinc-500">
            Showing <span class="font-semibold text-zinc-300">{{ $paginator->firstItem() }}</span>
            to <span class="font-semibold text-zinc-300">{{ $paginator->lastItem() }}</span>
            of <span class="font-semibold text-zinc-300">{{ $paginator->total() }}</span> results
        </p>

        <span class="inline-flex -space-x-px overflow-hidden rounded-lg shadow-panel rtl:space-x-reverse">
            {{-- Previous --}}
            @if ($paginator->onFirstPage())
                <span class="{{ $disabledClasses }} rounded-l-lg" aria-disabled="true" aria-label="Previous">‹</span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="{{ $idleClasses }} rounded-l-lg" rel="prev" aria-label="Previous">‹</a>
            @endif

            {{-- Page numbers --}}
            @foreach ($elements ?? [] as $element)
                @if (is_string($element))
                    <span class="{{ $dotsClasses }}" aria-disabled="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="{{ $activeClasses }}" aria-current="page">{{ $page }}</span>
                        @else
                            <a href="{{ $url }}" class="{{ $idleClasses }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next --}}
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="{{ $idleClasses }} rounded-r-lg" rel="next" aria-label="Next">›</a>
            @else
                <span class="{{ $disabledClasses }} rounded-r-lg" aria-disabled="true" aria-label="Next">›</span>
            @endif
        </span>
    </nav>
@endif
