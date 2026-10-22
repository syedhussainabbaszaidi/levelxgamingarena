<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Category;
use App\Models\Resource;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers requirement #2/#3/#10: a single Paid/Unpaid flag drives revenue.
 * Marking paid adds the amount to Today's revenue / total payments; unpaid
 * moves it back to Due — with no duplicate payment rows (issue #2, #10).
 */
class PaymentStatusTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
    }

    protected function createBooking(array $overrides = []): Booking
    {
        $resource = Resource::where('code', 'PC-2')->firstOrFail();

        return app(\App\Services\BookingService::class)->create(array_merge([
            'resource_id' => $resource->id,
            'starts_at' => now()->addDays(3)->setHour(12)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'admin',
        ], $overrides));
    }

    public function test_new_bookings_start_unpaid(): void
    {
        $booking = $this->createBooking();

        $this->assertEquals('unpaid', $booking->payment_status);
        $this->assertEquals(0, $booking->payments()->count());
    }

    public function test_marking_paid_records_one_settle_payment_and_updates_flag(): void
    {
        $booking = $this->createBooking(); // 60 min @ 350/h = 350

        $this->actingAs($this->admin)
            ->patchJson(route('admin.bookings.payment-status', $booking), ['payment_status' => 'paid', 'method' => 'cash'])
            ->assertOk();

        $booking->refresh();

        $this->assertEquals('paid', $booking->payment_status);
        $this->assertNotNull($booking->paid_at);
        $this->assertEquals(1, $booking->payments()->count());
        $this->assertEquals((float) $booking->final_cost, (float) $booking->payments()->sum('amount'));
    }

    public function test_marking_paid_then_unpaid_removes_the_settle_payment_without_duplicates(): void
    {
        $booking = $this->createBooking();
        $endpoint = route('admin.bookings.payment-status', $booking);

        // Paid → one settle row.
        $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'paid'])->assertOk();
        $this->assertEquals(1, $booking->payments()->count());

        // Back to unpaid → the settle row is removed, flag cleared.
        $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'unpaid'])->assertOk();
        $booking->refresh();

        $this->assertEquals('unpaid', $booking->payment_status);
        $this->assertNull($booking->paid_at);
        $this->assertEquals(0, $booking->payments()->count());
        $this->assertEquals(0.0, (float) $booking->payments()->sum('amount'));
    }

    public function test_repeated_toggles_never_duplicate_amounts(): void
    {
        $booking = $this->createBooking();
        $endpoint = route('admin.bookings.payment-status', $booking);

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'paid'])->assertOk();
            $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'unpaid'])->assertOk();
        }

        // Finally paid: exactly one settle payment for the full amount.
        $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'paid'])->assertOk();

        $booking->refresh();
        $this->assertEquals(1, $booking->payments()->count());
        $this->assertEquals((float) $booking->final_cost, (float) $booking->payments()->sum('amount'));
    }

    public function test_advance_payments_are_preserved_when_toggled_back_to_unpaid(): void
    {
        $booking = $this->createBooking([
            'advance_amount' => 100,
            'advance_method' => 'cash',
            'customer' => ['name' => 'Adv', 'phone' => '03099887766'],
        ]);

        $this->assertEquals(1, $booking->payments()->count()); // the advance

        $this->actingAs($this->admin)
            ->patchJson(route('admin.bookings.payment-status', $booking), ['payment_status' => 'paid'])
            ->assertOk();

        // Advance (100) + settle for the remaining balance (250) = 350.
        $booking->refresh();
        $this->assertEquals(2, $booking->payments()->count());
        $this->assertEquals(350.0, (float) $booking->payments()->sum('amount'));

        // Back to unpaid: only the auto settle row is removed, advance stays.
        $this->actingAs($this->admin)
            ->patchJson(route('admin.bookings.payment-status', $booking), ['payment_status' => 'unpaid'])
            ->assertOk();

        $booking->refresh();
        $this->assertEquals(1, $booking->payments()->count());
        $this->assertEquals('unpaid', $booking->payment_status);
    }

    public function test_revenue_totals_follow_the_payment_flag(): void
    {
        $booking = $this->createBooking();
        $endpoint = route('admin.bookings.payment-status', $booking);

        $before = $this->actingAs($this->admin)->getJson(route('admin.bookings.counts'))->json('totals');

        $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'paid'])->assertOk();

        $afterPaid = $this->actingAs($this->admin)->getJson(route('admin.bookings.counts'))->json('totals');
        $this->assertEquals($before['today_revenue'] + (float) $booking->final_cost, $afterPaid['today_revenue']);
        $this->assertEquals($before['total_payment'] + (float) $booking->final_cost, $afterPaid['total_payment']);
        $this->assertEquals($before['unpaid_amount'] - (float) $booking->final_cost, $afterPaid['unpaid_amount']);

        $this->actingAs($this->admin)->patchJson($endpoint, ['payment_status' => 'unpaid'])->assertOk();

        $afterUnpaid = $this->actingAs($this->admin)->getJson(route('admin.bookings.counts'))->json('totals');
        $this->assertEquals($before['today_revenue'], $afterUnpaid['today_revenue']);
        $this->assertEquals($before['total_payment'], $afterUnpaid['total_payment']);
        $this->assertEquals($before['unpaid_amount'], $afterUnpaid['unpaid_amount']);
    }

    public function test_counts_endpoint_reports_only_the_four_statuses(): void
    {
        $before = $this->actingAs($this->admin)->getJson(route('admin.bookings.counts'))->json('counts');

        // Distinct resources/times so the overlap guard never rejects a fixture.
        $codes = ['PC-2', 'PC-1', 'PS5-2', 'POOL-1'];
        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        foreach ($statuses as $i => $status) {
            $this->createBooking([
                'status' => $status,
                'resource_id' => Resource::where('code', $codes[$i])->firstOrFail()->id,
                'starts_at' => now()->addDays(4)->setHour(12 + $i)->format('Y-m-d H:i'),
            ]);
        }

        $counts = $this->actingAs($this->admin)->getJson(route('admin.bookings.counts'))->json('counts');

        foreach ($statuses as $status) {
            $this->assertEquals($before[$status] + 1, $counts[$status]);
        }
        $this->assertArrayNotHasKey('checked_in', $counts);
        $this->assertArrayNotHasKey('no_show', $counts);
    }

    public function test_customers_cannot_toggle_payment_status(): void
    {
        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $booking = $this->createBooking();

        $this->actingAs($customer)
            ->patch(route('admin.bookings.payment-status', $booking), ['payment_status' => 'paid'])
            ->assertForbidden();
    }

    public function test_online_booking_and_manual_booking_share_the_same_payment_logic(): void
    {
        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $resource = Resource::where('code', 'SNK-2')->firstOrFail();

        // Customer-side booking (same engine, source=online, unpaid by default).
        $this->actingAs($customer)->post('/book', [
            'resource_id' => $resource->id,
            'date' => now()->addDays(6)->format('Y-m-d'),
            'time' => '15:00',
            'duration_minutes' => 60,
            'fields' => [],
        ]);

        $booking = Booking::where('source', 'online')->latest('id')->firstOrFail();
        $this->assertEquals('unpaid', $booking->payment_status);

        // Admin marks it paid → counts exactly like a manual booking.
        $this->actingAs($this->admin)
            ->patchJson(route('admin.bookings.payment-status', $booking), ['payment_status' => 'paid'])
            ->assertOk();

        $booking->refresh();
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals((float) $booking->final_cost, (float) $booking->payments()->sum('amount'));
    }
}
