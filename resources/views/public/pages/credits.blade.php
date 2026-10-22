@extends('layouts.public')

@section('title', 'Photo Credits — '.config('app.name'))

@section('content')
    <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
        <x-icon name="eye" class="h-3.5 w-3.5" /> Photo Credits
    </span>

    <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">Photography credits</h1>
    <p class="mt-3 max-w-2xl text-base leading-relaxed text-zinc-400">
        Activity covers and gallery photographs are stored locally and used under the licences below.
        Images have been cropped to a 16:9 frame; the original licence still applies to the crop.
    </p>

    <div class="mt-8 grid gap-5 sm:grid-cols-2 xl:grid-cols-3">
        @foreach($credits as $credit)
            <article class="neon-card overflow-hidden">
                <img src="{{ asset($credit['file']) }}" alt="{{ $credit['title'] }}" loading="lazy"
                     class="h-40 w-full object-cover">
                <div class="p-5">
                    <h2 class="text-sm font-semibold text-white">{{ $credit['title'] }}</h2>
                    <p class="mt-1 text-xs text-zinc-500">{{ $credit['used_for'] }}</p>

                    <dl class="mt-4 space-y-1.5 text-xs">
                        <div class="flex justify-between gap-3">
                            <dt class="text-zinc-500">Photographer</dt>
                            <dd class="text-right text-zinc-300">{{ $credit['author'] }}</dd>
                        </div>
                        <div class="flex justify-between gap-3">
                            <dt class="text-zinc-500">Licence</dt>
                            <dd class="text-right">
                                <a href="{{ $credit['license_url'] }}" target="_blank" rel="noopener noreferrer license"
                                   class="text-neon-400 transition hover:text-neon-300">{{ $credit['license'] }}</a>
                            </dd>
                        </div>
                    </dl>

                    <a href="{{ $credit['source'] }}" target="_blank" rel="noopener noreferrer"
                       class="mt-4 inline-flex items-center gap-1.5 text-xs font-semibold text-zinc-400 transition hover:text-neon-300">
                        View original <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                    </a>
                </div>
            </article>
        @endforeach
    </div>
@endsection
