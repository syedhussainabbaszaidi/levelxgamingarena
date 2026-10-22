<?php

namespace App\Services;

use App\Mail\NewBookingMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Back-office notifications for new customer bookings.
 *
 * Mail problems must never fail a booking, so delivery is best-effort and
 * every failure is logged for the admin to retry later.
 */
class BookingNotifier
{
    public function notifyNewBooking(Booking $booking): bool
    {
        $recipient = config('mail.booking_recipient') ?: config('mail.contact_recipient');

        if (! $recipient) {
            return false;
        }

        try {
            Mail::to($recipient)->send(new NewBookingMail($booking));

            return true;
        } catch (\Throwable $e) {
            Log::error('New booking notification email failed.', [
                'booking' => $booking->code,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
