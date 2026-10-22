<?php

namespace Tests\Feature;

use App\Models\BookingField;
use App\Models\Business;
use App\Models\Category;
use App\Models\Resource;
use App\Models\User;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Carbon\Carbon;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Phase-two regression coverage: calendar removal, the booking-slot window,
 * live availability, delete flows and the customer information pages.
 */
class PhaseTwoRegressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
    }

    public function test_calendar_route_and_controller_are_gone(): void
    {
        $this->assertFalse(app('router')->has('admin.calendar'));
        $this->assertFileDoesNotExist(app_path('Http/Controllers/Admin/CalendarController.php'));
    }

    public function test_occasion_field_is_removed_from_the_booking_flow(): void
    {
        $this->assertDatabaseMissing('booking_fields', ['key' => 'occasion']);

        $resource = Resource::where('code', 'SNK-1')->firstOrFail();
        $html = $this->get(route('public.activity', $resource))->getContent();

        $this->assertStringNotContainsString('Occasion', $html);
    }

    public function test_slots_cover_the_full_24_hour_day(): void
    {
        $resource = Resource::where('code', 'SNK-1')->firstOrFail();

        $result = app(AvailabilityService::class)
            ->configuredSlots($resource->fresh(), Carbon::now()->addDays(4), 60);

        $times = collect($result['slots'])->pluck('time');

        $this->assertSame('00:00', $times->first());
        $this->assertSame('23:00', $times->last());
        $this->assertCount(24, $times);
    }

    public function test_a_booked_slot_is_marked_unavailable_but_still_listed(): void
    {
        $resource = Resource::where('code', 'POOL-1')->firstOrFail();
        $date = Carbon::now()->addDays(3);

        app(BookingService::class)->create([
            'resource_id' => $resource->id,
            'starts_at' => $date->copy()->setTime(15, 0)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'admin',
        ]);

        $slots = collect(app(AvailabilityService::class)
            ->configuredSlots($resource->fresh(), $date, 60)['slots'])
            ->keyBy('time');

        // Still present at its correct time…
        $this->assertTrue($slots->has('15:00'));
        // …but flagged unavailable, while neighbouring slots stay available.
        $this->assertFalse($slots['15:00']['available']);
        $this->assertTrue($slots['16:00']['available']);
    }

    public function test_double_booking_the_same_slot_is_rejected(): void
    {
        $resource = Resource::where('code', 'PS5')->firstOrFail();
        $customer = User::where('email', 'customer@example.com')->firstOrFail();

        app(BookingService::class)->create([
            'resource_id' => $resource->id,
            'starts_at' => Carbon::now()->addDays(7)->setTime(14, 0)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'admin',
        ]);

        $this->actingAs($customer)->post('/book', [
            'resource_id' => $resource->id,
            'date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'time' => '14:00',
            'duration_minutes' => 60,
            'fields' => $this->answersFor($resource),
        ])->assertSessionHasErrors('time');
    }

    /** Build a valid answer set for every required visible field. */
    protected function answersFor(Resource $resource): array
    {
        $answers = [];

        foreach (BookingField::query()->visible()->forResource($resource->id)->get() as $field) {
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

        return $answers;
    }

    public function test_homepage_cards_show_availability_badges(): void
    {
        $this->get(route('public.index'))
            ->assertOk()
            ->assertSee('Available');
    }

    public function test_category_delete_is_blocked_while_it_has_activities(): void
    {
        $category = Category::where('slug', 'snooker')->firstOrFail();

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('categories', ['id' => $category->id]);
    }

    public function test_empty_category_deletes_from_the_listing(): void
    {
        $category = Category::create([
            'business_id' => Business::primary()->id,
            'name' => 'Empty Category',
            'slug' => 'empty-category',
            'color' => '#ff2d55',
            'is_active' => true,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.categories.destroy', $category))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }

    public function test_activity_without_bookings_is_deleted_via_the_listing(): void
    {
        $resource = Resource::create([
            'business_id' => Business::primary()->id,
            'category_id' => Category::firstOrFail()->id,
            'name' => 'Disposable Activity',
            'code' => 'DEL-1',
            'capacity' => 2,
            'duration_minutes' => 60,
            'hourly_rate' => 100,
            'status' => 'active',
            'sort_order' => 99,
        ]);

        $this->actingAs($this->admin)
            ->delete(route('admin.activities.destroy', $resource))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('resources', ['id' => $resource->id]);
    }

    public function test_activity_form_ships_a_client_side_image_preview(): void
    {
        $html = $this->actingAs($this->admin)
            ->get(route('admin.activities.create'))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('id="image-preview"', $html);
        $this->assertStringContainsString('id="image-input"', $html);
        $this->assertStringContainsString('URL.createObjectURL', $html);
    }

    public function test_customer_information_pages_render(): void
    {
        foreach (['public.about', 'public.contact', 'public.privacy', 'public.cookies', 'public.terms', 'public.disclaimer'] as $route) {
            $this->get(route($route))->assertOk()->assertSee('+92 342 2631057');
        }
    }

    public function test_seeded_activities_have_cover_images(): void
    {
        foreach (Resource::all() as $resource) {
            $this->assertNotNull($resource->imageUrl());
        }

        $this->get(route('public.index'))->assertSee('/storage/activities/');
    }

    public function test_activities_without_an_uploaded_image_fall_back_to_category_artwork(): void
    {
        $category = Category::where('slug', 'snooker')->firstOrFail();

        $resource = Resource::create([
            'business_id' => Business::primary()->id,
            'category_id' => $category->id,
            'name' => 'Bare Table',
            'capacity' => 2,
            'duration_minutes' => 60,
            'hourly_rate' => 100,
            'status' => 'active',
            'sort_order' => 50,
        ]);

        $this->assertNull($resource->image_path);
        $this->assertStringContainsString('/img/activities/covers/snooker.jpg', $resource->imageUrl());
    }
}
