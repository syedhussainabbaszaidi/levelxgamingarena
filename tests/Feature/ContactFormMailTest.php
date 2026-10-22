<?php

namespace Tests\Feature;

use App\Mail\ContactMessageMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_form_emails_the_configured_recipient(): void
    {
        Mail::fake();

        $response = $this->post(route('public.contact.send'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'phone' => '03001234567',
            'subject' => 'PS5 booking enquiry',
            'message' => 'Do you have a PS5 booth free tonight?',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        Mail::assertSent(ContactMessageMail::class, function (ContactMessageMail $mail) {
            return $mail->hasTo('syed8069163@gmail.com')
                && $mail->submission['name'] === 'Jane Doe'
                && $mail->submission['email'] === 'jane@example.com'
                && $mail->submission['subject'] === 'PS5 booking enquiry'
                && str_contains($mail->submission['message'], 'PS5 booth');
        });
    }

    public function test_contact_form_validates_required_fields(): void
    {
        Mail::fake();

        $this->post(route('public.contact.send'), [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        Mail::assertNothingSent();
    }

    public function test_contact_form_shows_an_error_when_mail_delivery_fails(): void
    {
        Mail::shouldReceive('to')->once()->andReturnSelf();
        Mail::shouldReceive('send')->once()->andThrow(new \RuntimeException('SMTP unavailable'));

        $this->post(route('public.contact.send'), [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'message' => 'Hello?',
        ])->assertSessionHas('error');
    }
}
