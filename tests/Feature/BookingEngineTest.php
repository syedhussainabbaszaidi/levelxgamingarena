<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PricingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BookingEngineTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;

    protected Resource $resource;

    protected BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->business = Business::create([
            'name' => 'Test Lounge',
            'slug' => 'test-lounge',
            'opens_at' => '10:00',
            'closes_at' => '02:00',
            'overnight' => true,
        ]);

        $cat = Category::create([
            'business_id' => $this->business->id,
            'name' => 'Snooker',
            'slug' => 'snooker',
        ]);

        $this->resource = Resource::create([
            'business_id' => $this->business->id,
            'category_id' => $cat->id,
            'name' => 'Table 1',
            'hourly_rate' => 300,
        ]);

        $this->service = app(BookingService::class);
    }

    public function test_creates_booking_and_calculates_base_price(): void
    {
        $start = now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);

        $booking = $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'counter',
            'customer' => ['name' => 'Ali', 'phone' => '03001112222'],
        ]);

        $this->assertNotNull($booking->id);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals(300.0, (float) $booking->estimated_cost);
        $this->assertNotNull($booking->customer_id);
        $this->assertStringStartsWith('BK-', $booking->code);
    }

    public function test_overlapping_booking_is_rejected(): void
    {
        $start = now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);

        $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'counter',
        ]);

        $this->expectException(ValidationException::class);

        $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->copy()->addMinutes(30)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'counter',
        ]);
    }

    public function test_back_to_back_bookings_are_allowed(): void
    {
        $start = now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);

        $b1 = $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'counter',
        ]);

        $b2 = $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->copy()->addHour()->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'counter',
        ]);

        $this->assertNotNull($b2->id);
    }

    public function test_cancelled_booking_frees_the_slot(): void
    {
        $start = now()->addDay()->setHour(15)->setMinute(0)->setSecond(0);

        $b1 = $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'counter',
        ]);

        $this->service->cancel($b1, 'test');

        // Slot must be free again after cancellation.
        $check = app(AvailabilityService::class)->validate(
            $this->resource->fresh(),
            $start,
            $start->copy()->addHour()
        );
        $this->assertTrue($check['ok'], $check['message'] ?? 'not ok');
    }

    public function test_maintenance_resource_cannot_be_booked(): void
    {
        $this->resource->update(['status' => 'maintenance']);

        $this->expectException(ValidationException::class);

        $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => now()->addDay()->setHour(14)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
        ]);
    }

    public function test_walk_in_starts_a_live_session(): void
    {
        $booking = $this->service->walkIn([
            'resource_id' => $this->resource->id,
            'starts_at' => now()->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'customer' => ['name' => 'Walkin', 'phone' => '03112223333'],
        ]);

        $this->assertEquals('confirmed', $booking->status);
        $this->assertNotNull($booking->checked_in_at);
    }

    public function test_extend_adds_time_and_cost(): void
    {
        $booking = $this->service->walkIn([
            'resource_id' => $this->resource->id,
            'starts_at' => now()->subMinutes(30)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
        ]);

        $result = $this->service->extend($booking, 30);

        $this->assertEquals(30, $booking->extended_minutes);
        $this->assertEquals(90, $booking->duration_minutes);
        $this->assertEquals(450.0, (float) $booking->final_cost); // 90 min @ 300/h
        $this->assertEquals(150.0, $result['extra_cost']);
    }

    public function test_extend_blocked_when_next_slot_booked(): void
    {
        $start = now()->subMinutes(30)->setSecond(0);

        $booking = $this->service->walkIn([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->format('Y-m-d H:i'),
            'duration_minutes' => 60,
        ]);

        // Book the next slot (counter override: same-day booking past hours check)
        $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->copy()->addMinutes(60)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'allow_past' => true,
        ]);

        $this->expectException(ValidationException::class);

        $this->service->extend($booking, 30);
    }

    public function test_end_session_settles_payment(): void
    {
        $booking = $this->service->walkIn([
            'resource_id' => $this->resource->id,
            'starts_at' => now()->subHour()->format('Y-m-d H:i'),
            'duration_minutes' => 60,
        ]);

        $this->service->endSession($booking, ['method' => 'cash']);

        $this->assertEquals('completed', $booking->status);
        $this->assertNotNull($booking->ended_at);

        // 60min @300/h = 300 → one full payment of 300
        $this->assertEquals(1, $booking->payments()->count());
        $this->assertEquals(300.0, (float) $booking->payments()->sum('amount'));
    }

    public function test_pricing_service_splits_peak_windows(): void
    {
        // Add a peak rule: 18:00–22:00 @ 600/h (higher priority than base rate)
        PricingRule::create([
            'business_id' => $this->business->id,
            'category_id' => $this->resource->category_id,
            'name' => 'Peak',
            'start_time' => '18:00',
            'end_time' => '22:00',
            'hourly_rate' => 600,
            'min_minutes' => 30,
            'slot_minutes' => 30,
            'priority' => 10,
        ]);

        $pricing = app(PricingService::class);

        // 17:00 → 19:00: 1h off-peak @300 + 1h peak @600 = 900
        $day = now()->addDay()->setHour(17)->setMinute(0)->setSecond(0);
        $result = $pricing->calculate($this->resource->fresh(), $day, $day->copy()->addHours(2));

        $this->assertEquals(900.0, $result['total']);
        $this->assertCount(2, $result['segments']);
    }

    public function test_availability_slots_mark_busy(): void
    {
        $start = now()->addDay()->setHour(14)->setMinute(0)->setSecond(0);
        $day = $start->copy()->startOfDay();

        $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => $start->format('Y-m-d H:i'),
            'duration_minutes' => 60,
        ]);

        $availability = app(AvailabilityService::class);
        $result = $availability->slotsForResource($this->resource->fresh(), $day);

        $byTime = collect($result['slots'])->pluck('available', 'time');

        $this->assertFalse($byTime['14:00']);
        $this->assertFalse($byTime['14:30']);
        $this->assertTrue($byTime['15:00']);
        $this->assertTrue($byTime['11:00']);
    }

    public function test_online_booking_requires_authentication(): void
    {
        $this->post('/book', [
            'resource_id' => $this->resource->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '14:00',
            'duration_minutes' => 60,
        ])->assertRedirect(route('login'));

        $this->assertNull(Booking::where('source', 'online')->first());
    }

    public function test_authenticated_customer_creates_pending_booking(): void
    {
        $user = User::create([
            'name' => 'Online Customer',
            'email' => 'online@test.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $response = $this->actingAs($user)->post('/book', [
            'resource_id' => $this->resource->id,
            'date' => now()->addDay()->format('Y-m-d'),
            'time' => '14:00',
            'duration_minutes' => 60,
        ]);

        $booking = Booking::where('source', 'online')->first();
        $this->assertNotNull($booking);
        $this->assertEquals('pending', $booking->status);
        $this->assertEquals($user->id, $booking->user_id);

        $response->assertRedirect(route('public.confirmation', $booking->code));
    }

    public function test_admin_can_check_in_from_ui(): void
    {
        $admin = User::create([
            'name' => 'Admin', 'email' => 'a@test.com', 'password' => bcrypt('password'), 'role' => 'admin',
        ]);

        $booking = $this->service->create([
            'resource_id' => $this->resource->id,
            'starts_at' => now()->addDay()->setHour(14)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'admin',
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->post(route('admin.sessions.check-in', $booking))
            ->assertRedirect();

        $this->assertEquals('confirmed', $booking->fresh()->status);
        $this->assertNotNull($booking->fresh()->checked_in_at);
    }

    public function test_customer_cannot_access_admin(): void
    {
        $customer = User::create([
            'name' => 'Cust', 'email' => 'c@test.com', 'password' => bcrypt('password'), 'role' => 'customer',
        ]);

        $this->actingAs($customer)
            ->get(route('admin.dashboard'))
            ->assertForbidden();
    }
}
