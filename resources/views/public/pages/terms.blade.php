@extends('layouts.public')

@section('title', 'Terms & Conditions — '.config('app.name'))

@section('content')
        <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
            <x-icon name="document" class="h-3.5 w-3.5" /> Terms &amp; Conditions
        </span>

        <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">Terms &amp; Conditions</h1>
        <p class="mt-2 text-xs text-zinc-500">Last updated {{ $updatedAt }}</p>

        <div class="mt-6 grid gap-6 text-sm leading-relaxed text-zinc-400 lg:grid-cols-2">
            <p>
                By booking an activity through {{ config('app.name') }} you agree to the terms below. Please read
                them before confirming a reservation.
            </p>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Bookings</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5">
                    <li>Slots are reserved on a first-come, first-served basis and are confirmed once shown as booked.</li>
                    <li>Please arrive on time; sessions start at the booked time.</li>
                    <li>You are responsible for the accuracy of the details you submit.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Payment</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5">
                    <li>No payment is taken online. Payment is made at the counter at the end of your session.</li>
                    <li>Prices shown reflect the applicable rate at the time of booking and may vary by time of day.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Cancellations</h2>
                <p class="mt-3">
                    If you can no longer attend, please cancel as early as possible so the slot can be released.
                    Late cancellations or no-shows may be subject to the venue's policy.
                </p>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Conduct &amp; liability</h2>
                <p class="mt-3">
                    Please treat equipment and other guests with respect. Damaged equipment may be charged. The venue
                    is not liable for loss or damage to personal belongings.
                </p>
            </div>

            <div class="neon-card p-5">
                <h2 class="text-sm font-semibold text-white">Need help?</h2>
                <p class="mt-2">
                    Email
                    <a href="mailto:info@levelxgamingarena.com" class="text-neon-400 hover:text-neon-300">info@levelxgamingarena.com</a>
                    or call <a href="tel:+923422631057" class="text-neon-400 hover:text-neon-300">+92 342 2631057</a>.
                </p>
            </div>
        </div>
@endsection
