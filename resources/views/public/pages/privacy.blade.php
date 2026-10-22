@extends('layouts.public')

@section('title', 'Privacy Policy — '.config('app.name'))

@section('content')
        <span class="inline-flex items-center gap-2 rounded-full border border-neon-500/30 bg-neon-500/10 px-3 py-1 text-xs font-semibold text-neon-300">
            <x-icon name="shield" class="h-3.5 w-3.5" /> Privacy Policy
        </span>

        <h1 class="mt-5 font-display text-3xl font-black text-white sm:text-4xl">Privacy Policy</h1>
        <p class="mt-2 text-xs text-zinc-500">Last updated {{ $updatedAt }}</p>

        <div class="mt-6 grid gap-6 text-sm leading-relaxed text-zinc-400 lg:grid-cols-2">
            <p>
                {{ $business?->name ?? config('app.name') }} respects your privacy. This policy explains what
                information we collect when you use our booking platform and how we use it.
            </p>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Information we collect</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5">
                    <li>Account details such as your name, email address and password (stored securely hashed).</li>
                    <li>Booking details, including the activity, date, time and duration you reserve.</li>
                    <li>Contact information you provide on the booking or contact forms.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">How we use your information</h2>
                <ul class="mt-3 list-disc space-y-1.5 pl-5">
                    <li>To create and manage your bookings and your account.</li>
                    <li>To contact you about your reservations and respond to enquiries.</li>
                    <li>To keep the platform secure, prevent abuse and improve our service.</li>
                </ul>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Sharing &amp; security</h2>
                <p class="mt-3">
                    We do not sell your personal information. Access is limited to authorised staff who need it to
                    operate the business. We apply reasonable technical and organisational measures to protect the
                    data we hold.
                </p>
            </div>

            <div>
                <h2 class="font-display text-lg font-bold text-white">Your choices</h2>
                <p class="mt-3">
                    You can review and update your profile at any time, or contact us to request access to, correction
                    of, or deletion of your personal data.
                </p>
            </div>

            <div class="neon-card p-5">
                <h2 class="text-sm font-semibold text-white">Contact us</h2>
                <p class="mt-2">
                    Questions about this policy? Email
                    <a href="mailto:info@levelxgamingarena.com" class="text-neon-400 hover:text-neon-300">info@levelxgamingarena.com</a>
                    or call <a href="tel:+923422631057" class="text-neon-400 hover:text-neon-300">+92 342 2631057</a>.
                </p>
            </div>
        </div>
@endsection
