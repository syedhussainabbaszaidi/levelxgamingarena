<?php

namespace Tests\Feature;

use App\Mail\NewBookingMail;
use App\Models\Booking;
use App\Models\BookingField;
use App\Models\Resource;
use App\Models\User;
use App\Services\BookingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * New customer bookings must email the back office, raise the sidebar badge and
 * clear once an admin acknowledges them.
 */
class BookingNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected User $customer;

    protected Resource $resource;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);

        // The demo dataset ships online bookings; start from a clean slate so
        // unread counts are deterministic.
        Booking::query()->update(['admin_read_at' => now()]);

        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->customer = User::where('email', 'customer@example.com')->firstOrFail();
        $this->resource = Resource::where('code', 'PS5')->firstOrFail();
    }

    public function test_online_booking_emails_the_back_office_and_is_unread(): void
    {
        Mail::fake();

        $this->actingAs($this->customer)
            ->post('/book', $this->bookingPayload())
            ->assertRedirect();

        $booking = Booking::where('user_id', $this->customer->id)->latest('id')->firstOrFail();

        Mail::assertSent(NewBookingMail::class, function (NewBookingMail $mail) use ($booking) {
            return $mail->booking->id === $booking->id
                && $mail->hasTo(config('mail.booking_recipient'));
        });

        $this->assertTrue($booking->fresh()->isUnreadForAdmin());
        $this->assertSame(1, Booking::unreadForAdmin()->count());
    }

    public function test_unread_count_endpoint_reports_the_database_count(): void
    {
        $this->createOnlineBooking();

        $this->actingAs($this->admin)
            ->getJson(route('admin.bookings.unread-count'))
            ->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonPath('unread', 1);
    }

    public function test_opening_a_booking_clears_it_from_the_unread_count(): void
    {
        $booking = $this->createOnlineBooking();

        $this->assertSame(1, Booking::unreadForAdmin()->count());

        $this->actingAs($this->admin)
            ->get(route('admin.bookings.show', $booking))
            ->assertOk();

        $this->assertNotNull($booking->fresh()->admin_read_at);
        $this->assertSame(0, Booking::unreadForAdmin()->count());
    }

    public function test_admin_can_mark_every_new_booking_as_read(): void
    {
        $this->createOnlineBooking(7, 14);
        $this->createOnlineBooking(7, 16);

        $this->assertSame(2, Booking::unreadForAdmin()->count());

        $this->actingAs($this->admin)
            ->post(route('admin.bookings.mark-all-read'))
            ->assertRedirect();

        $this->assertSame(0, Booking::unreadForAdmin()->count());
    }

    public function test_admin_sidebar_renders_the_unread_badge(): void
    {
        $this->createOnlineBooking();

        $this->actingAs($this->admin)
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('bookings-unread-badge', false);
    }

    /* ------------------------------------------------------------------ */

    protected function createOnlineBooking(int $daysAhead = 6, int $hour = 14): Booking
    {
        return app(BookingService::class)->create([
            'resource_id' => $this->resource->id,
            'starts_at' => now()->addDays($daysAhead)->setHour($hour)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'online',
            'user_id' => $this->customer->id,
        ]);
    }

    protected function bookingPayload(): array
    {
        $answers = [];

        foreach (BookingField::query()->visible()->forResource($this->resource->id)->get() as $field) {
            if (! $field->is_required) {
                continue;
            }

            $answers[$field->id] = match ($field->type) {
                'number' => 2,
                'select' => $field->choiceOptions()[0] ?? 'Option',
                'checkbox' => 'Yes',
                'email' => 'customer@example.com',
                'tel' => '03123456789',
                default => 'Test Player',
            };
        }

        return [
            'resource_id' => $this->resource->id,
            'date' => now()->addDays(6)->format('Y-m-d'),
            'time' => '14:00',
            'duration_minutes' => 60,
            'fields' => $answers,
        ];
    }
}
