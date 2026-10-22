<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the back office whenever a customer creates a booking online.
 */
class NewBookingMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Booking $booking) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New booking '.$this->booking->code.' — '.($this->booking->resource?->name ?? 'Gaming activity'),
        );
    }

    public function content(): Content
    {
        $this->booking->loadMissing(['resource.category', 'business', 'customer', 'fieldValues.field']);

        return new Content(
            view: 'emails.new-booking',
            with: ['booking' => $this->booking],
        );
    }
}
