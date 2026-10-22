<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent to the site owner when a visitor submits the contact form.
 */
class ContactMessageMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array{name:string,email:string,phone:?string,subject:?string,message:string,submitted_at:string}  $submission
     */
    public function __construct(public array $submission) {}

    public function envelope(): Envelope
    {
        $subject = trim((string) ($this->submission['subject'] ?? ''));

        return new Envelope(
            subject: 'Contact form: '.($subject !== '' ? $subject : 'New website enquiry'),
            replyTo: [$this->submission['email']],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-message',
            with: ['submission' => $this->submission],
        );
    }
}
