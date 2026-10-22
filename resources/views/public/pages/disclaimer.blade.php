@extends('layouts.public')

@section('title', 'Disclaimer — '.config('app.name'))

@section('content')
        <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
            <x-icon name="warning" class="h-3.5 w-3.5" /> Disclaimer
        </span>

        <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">Disclaimer</h1>
        <p class="mt-2 text-xs text-zinc-500">Last updated {{ $updatedAt }}</p>

        <div class="mt-6 grid gap-6 text-sm leading-relaxed text-zinc-400 lg:grid-cols-2">
            <p>
                The information and booking tools provided on {{ config('app.name') }} are published in good faith
                and for general informational purposes only. By using this website you accept the terms of this
                disclaimer in full.
            </p>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Accuracy of information</h2>
                <p class="mt-3">
                    We work hard to keep activity details, live availability and pricing accurate. Rates, opening
                    hours, promotions and session slots can still change at any time, and any final amount is
                    confirmed at the counter when you play.
                </p>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">External links</h2>
                <p class="mt-3">
                    This website may contain links to third-party sites such as maps or social pages. We do not
                    control those sites and are not responsible for their content, accuracy or practices.
                </p>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Limitation of liability</h2>
                <p class="mt-3">
                    To the fullest extent permitted by law, {{ $business?->name ?? config('app.name') }} is not
                    liable for any loss or damage arising from the use of this website, including reliance on
                    information that may become out of date. Personal belongings remain the responsibility of
                    the owner at all times.
                </p>
            </div>

            <div class="neon-card p-5">
                <h2 class="text-sm font-semibold text-white">Questions</h2>
                <p class="mt-2">
                    If you have any questions about this disclaimer, email
                    <a href="mailto:info@levelxgamingarena.com" class="text-neon-400 hover:text-neon-300">info@levelxgamingarena.com</a>
                    or call <a href="tel:+923422631057" class="text-neon-400 hover:text-neon-300">+92 342 2631057</a>.
                </p>
            </div>
        </div>
@endsection
