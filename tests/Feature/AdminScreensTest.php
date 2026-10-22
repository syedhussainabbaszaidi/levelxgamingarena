<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScreensTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::where('role', 'admin')->firstOrFail();
    }

    public function test_all_core_admin_screens_render(): void
    {
        $routes = [
            'admin.dashboard',
            'admin.bookings.index',
            'admin.bookings.create',
            'admin.customers.index',
            'admin.customers.create',
            'admin.activities.index',
            'admin.activities.create',
            'admin.categories.index',
            'admin.categories.create',
            'admin.pricing.index',
            'admin.payments.index',
            'admin.reports.index',
            'admin.settings.index',
            'admin.users.index',
            'admin.users.create',
            'admin.roles.index',
            'admin.roles.create',
            'admin.logs.index',
            'admin.config.index',
            'admin.fields.index',
            'admin.slots.index',
        ];

        foreach ($routes as $route) {
            $this->actingAs($this->admin)
                ->get(route($route))
                ->assertOk();
        }
    }

    public function test_booking_detail_and_customer_profile_render(): void
    {
        $booking = \App\Models\Booking::firstOrFail();
        $customer = \App\Models\Customer::firstOrFail();

        $this->actingAs($this->admin)->get(route('admin.bookings.show', $booking))->assertOk();
        $this->actingAs($this->admin)->get(route('admin.customers.show', $customer))->assertOk();
    }

    public function test_public_pages_render_with_seeded_data(): void
    {
        $this->get(route('public.index'))->assertOk();

        $activity = \App\Models\Resource::where('code', 'SNK-1')->firstOrFail();
        $this->get(route('public.activity', $activity))->assertOk();

        // Confirmation pages are private to the booking owner.
        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $this->actingAs($customer)->get(route('public.my-bookings'))->assertOk();
        $this->actingAs($customer)->get(route('customer.dashboard'))->assertOk();

        $booking = \App\Models\Booking::where('code', 'BK-DEMO-00005')->firstOrFail();
        $this->actingAs($customer)->get(route('public.confirmation', $booking->code))->assertOk();
    }

    public function test_activity_store_and_update_redirect_to_the_activities_index(): void
    {
        $category = \App\Models\Category::firstOrFail();
        $business = \App\Models\Business::firstOrFail();

        $payload = [
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => 'Regression Activity',
            'code' => 'REG-1',
            'description' => 'created by the regression test',
            'capacity' => 4,
            'duration_minutes' => 60,
            'hourly_rate' => 20,
            'status' => 'active',
            'sort_order' => 1,
        ];

        $this->actingAs($this->admin)
            ->post(route('admin.activities.store'), $payload)
            ->assertRedirect(route('admin.activities.index'));

        $activity = \App\Models\Resource::where('code', 'REG-1')->firstOrFail();

        $this->actingAs($this->admin)
            ->patch(route('admin.activities.update', $activity), [...$payload, 'name' => 'Regression Activity (edited)'])
            ->assertRedirect(route('admin.activities.index'));

        $this->assertSame('Regression Activity (edited)', $activity->fresh()->name);
    }

    public function test_booking_confirmation_is_private(): void
    {
        $booking = \App\Models\Booking::where('code', 'BK-DEMO-00005')->firstOrFail();

        $this->get(route('public.confirmation', $booking->code))->assertRedirect(route('login'));
    }
}
