<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\Category;
use App\Models\Resource;
use App\Models\User;
use App\Services\BookingService;
use App\Services\PricingService;
use Carbon\Carbon;
use Database\Seeders\ActivityCatalogSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * The published price list (Monday Madness, Account Packages, Opening
 * Discounts and the PC Mid Day Package) and the pricing engine behind it.
 */
class ActivityPricingTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->firstOrFail();
    }

    public function test_every_published_activity_and_package_is_imported_with_the_right_price(): void
    {
        $expected = [
            'PC-GAMES' => ['PC Games', 'hourly', 250, 200, null, null],
            'PS5' => ['PS5', 'hourly', 1000, 650, null, null],
            'SNK-PREMIUM' => ['Premium Snooker Room', 'per_minute', 900, 720, null, null],
            'TT-PRIVATE' => ['Private Table Tennis Room', 'hourly', 800, 600, null, null],
            'ACC-OPENING' => ['Account Opening', 'fixed', 0, null, 1000, null],
            'PKG-5H' => ['5 Hours', 'fixed', 0, null, 900, 300],
            'PKG-8H' => ['8 Hours', 'fixed', 0, null, 1400, 480],
            'PKG-10H' => ['10 Hours', 'fixed', 0, null, 1700, 600],
            'PKG-20H' => ['20 Hours', 'fixed', 0, null, 3200, 1200],
        ];

        foreach ($expected as $code => [$name, $type, $rate, $sale, $fixed, $minutes]) {
            $activity = Resource::where('code', $code)->first();

            $this->assertNotNull($activity, "Missing imported activity {$code}");
            $this->assertSame($name, $activity->name);
            $this->assertSame($type, $activity->pricing_type);
            $this->assertSame((float) $rate, (float) $activity->hourly_rate);
            $this->assertSame($sale === null ? null : (float) $sale, $activity->sale_price === null ? null : (float) $activity->sale_price);
            $this->assertSame($fixed === null ? null : (float) $fixed, $activity->fixed_price === null ? null : (float) $activity->fixed_price);
            $this->assertSame($minutes, $activity->package_minutes);
        }

        // Per-minute and fixed activities display the unit from the posters.
        $this->assertSame(15.0, Resource::where('code', 'SNK-PREMIUM')->first()->regularUnitPrice());
        $this->assertSame(12.0, Resource::where('code', 'SNK-PREMIUM')->first()->price());
        $this->assertSame(900.0, Resource::where('code', 'PKG-5H')->first()->price());
        $this->assertSame(300, Resource::where('code', 'PKG-5H')->first()->package_minutes);

        // The extra-controller add-on from the PS5 poster.
        $ps5 = Resource::where('code', 'PS5')->first();
        $this->assertSame(300.0, (float) $ps5->addon_price);
        $this->assertSame('additional controller', $ps5->addon_label);
    }

    public function test_importing_the_catalogue_again_never_duplicates_packages(): void
    {
        $before = Resource::count();

        $this->seed(ActivityCatalogSeeder::class);
        $this->seed(ActivityCatalogSeeder::class);

        $this->assertSame($before, Resource::count());
        $this->assertSame(1, Resource::where('code', 'PC-GAMES')->count());
        $this->assertSame(1, Resource::where('code', 'PKG-20H')->count());
    }

    public function test_hourly_and_promotional_prices_are_charged_correctly(): void
    {
        $pricing = app(PricingService::class);
        $pc = Resource::where('code', 'PC-GAMES')->first();

        // Tuesday evening: no rate window, opening discount (200/hr) applies.
        $start = $this->nextWeekdayAt(Carbon::TUESDAY, '20:00');
        $result = $pricing->calculate($pc, $start, $start->copy()->addHours(2));

        $this->assertSame(400.0, $result['total']);
        $this->assertSame(400.0, $pc->price() * 2);
    }

    public function test_per_minute_activities_are_billed_by_the_minute(): void
    {
        $pricing = app(PricingService::class);
        $snooker = Resource::where('code', 'SNK-PREMIUM')->first();

        $start = $this->nextWeekdayAt(Carbon::TUESDAY, '13:00'); // Tuesday, no Monday offer
        $result = $pricing->calculate($snooker, $start, $start->copy()->addMinutes(90));

        // 12 RS/min (opening discount) × 90 minutes.
        $this->assertSame(1080.0, $result['total']);
    }

    public function test_fixed_packages_charge_one_price_for_the_whole_bundle(): void
    {
        $package = Resource::where('code', 'PKG-8H')->first();
        $pricing = app(PricingService::class);

        $start = $this->nextWeekdayAt(Carbon::TUESDAY, '13:00');
        $result = $pricing->calculate($package, $start, $start->copy()->addMinutes(480));

        $this->assertTrue($result['fixed']);
        $this->assertSame(1400.0, $result['total']);
    }

    public function test_monday_madness_and_mid_day_windows_apply_automatically(): void
    {
        $pricing = app(PricingService::class);
        $pc = Resource::where('code', 'PC-GAMES')->first();

        // Monday 3-hour special: 140/hr with a 3-hour minimum.
        $monday = $this->nextWeekdayAt(Carbon::MONDAY, '15:00');
        $result = $pricing->calculate($pc, $monday, $monday->copy()->addHours(3));
        $this->assertSame(420.0, $result['total']);
        $this->assertSame(180, $pricing->minimumMinutesFor($pc, $monday));

        // Tuesday midday: 150/hr Mid Day package.
        $midDay = $this->nextWeekdayAt(Carbon::TUESDAY, '13:00');
        $result = $pricing->calculate($pc, $midDay, $midDay->copy()->addHours(3));
        $this->assertSame(450.0, $result['total']);

        // Tuesday evening: standard opening-discount rate.
        $evening = $this->nextWeekdayAt(Carbon::TUESDAY, '19:00');
        $result = $pricing->calculate($pc, $evening, $evening->copy()->addHours(2));
        $this->assertSame(400.0, $result['total']);
        $this->assertSame(30, $pricing->minimumMinutesFor($pc, $evening));
    }

    public function test_minimum_duration_is_enforced_on_the_server(): void
    {
        $pc = Resource::where('code', 'PC-GAMES')->first();

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('3 hours');

        app(BookingService::class)->create([
            'resource_id' => $pc->id,
            'starts_at' => $this->nextWeekdayAt(Carbon::MONDAY, '15:00')->format('Y-m-d H:i'), // Monday special
            'duration_minutes' => 60,
            'source' => 'counter',
        ]);
    }

    public function test_fixed_package_bookings_lock_the_package_duration(): void
    {
        $package = Resource::where('code', 'PKG-5H')->first();

        $booking = app(BookingService::class)->create([
            'resource_id' => $package->id,
            'starts_at' => $this->nextWeekdayAt(Carbon::TUESDAY, '13:00')->format('Y-m-d H:i'),
            'duration_minutes' => 60, // engine forces the 5-hour package
            'source' => 'counter',
        ]);

        $this->assertSame(300, $booking->duration_minutes);
        $this->assertSame(900.0, (float) $booking->final_cost);
    }

    public function test_admin_can_create_a_per_minute_activity_with_a_sale_price(): void
    {
        $category = Category::where('slug', 'snooker')->firstOrFail();
        $business = Business::primary();

        $this->actingAs($this->admin)->post(route('admin.activities.store'), [
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => 'Snooker Practice Table',
            'code' => 'SNK-PRACTICE',
            'capacity' => 2,
            'duration_minutes' => 60,
            'pricing_type' => 'per_minute',
            'price' => 15,
            'sale_price' => 10,
            'promo_label' => 'Opening discount',
            'status' => 'active',
            'sort_order' => 5,
            'is_online_bookable' => 1,
        ])->assertRedirect(route('admin.activities.index'));

        $activity = Resource::where('code', 'SNK-PRACTICE')->firstOrFail();

        $this->assertSame('per_minute', $activity->pricing_type);
        $this->assertSame(900.0, (float) $activity->hourly_rate); // 15/min → 900/hr
        $this->assertSame(600.0, (float) $activity->sale_price);  // 10/min → 600/hr
        $this->assertSame(10.0, $activity->price());
        $this->assertSame('min', $activity->priceUnit());

        $this->get(route('public.activity', $activity))
            ->assertOk()
            ->assertSee('10')
            ->assertSee('PKR/min');

        // Admin price changes are what the public page shows.
        $this->actingAs($this->admin)->patch(route('admin.activities.update', $activity), [
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => $activity->name,
            'code' => $activity->code,
            'capacity' => 2,
            'duration_minutes' => 60,
            'pricing_type' => 'per_minute',
            'price' => 15,
            'sale_price' => 8,
            'status' => 'active',
            'sort_order' => 5,
        ])->assertRedirect(route('admin.activities.index'));

        $this->assertSame(8.0, $activity->fresh()->price());
        $this->get(route('public.activity', $activity))->assertSee('PKR/min');
    }

    public function test_admin_can_create_a_fixed_package_with_a_package_duration(): void
    {
        $category = Category::where('slug', 'account-packages')->firstOrFail();
        $business = Business::primary();

        $this->actingAs($this->admin)->post(route('admin.activities.store'), [
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => '15 Hours',
            'code' => 'PKG-15H',
            'capacity' => 1,
            'duration_minutes' => 900,
            'pricing_type' => 'fixed',
            'price' => 2400,
            'package_minutes' => 900,
            'status' => 'active',
            'sort_order' => 15,
        ])->assertRedirect(route('admin.activities.index'));

        $package = Resource::where('code', 'PKG-15H')->firstOrFail();

        $this->assertSame('fixed', $package->pricing_type);
        $this->assertSame(2400.0, (float) $package->fixed_price);
        $this->assertSame(900, $package->package_minutes);
        $this->assertSame('15-hour package', $package->packageLabel());
        $this->assertSame(2400.0, app(PricingService::class)
            ->calculate($package, $this->nextWeekdayAt(Carbon::TUESDAY, '13:00'), $this->nextWeekdayAt(Carbon::TUESDAY, '13:00')->addMinutes(900))['total']);

        $this->actingAs($this->admin)
            ->get(route('admin.activities.edit', $package))
            ->assertOk()
            ->assertSee('name="pricing_type"', false)
            ->assertSee('name="package_minutes"', false);
    }

    public function test_admin_can_activate_and_deactivate_an_activity_from_the_list(): void
    {
        $activity = Resource::where('code', 'PC-GAMES')->firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('admin.activities.toggle-active', $activity))
            ->assertRedirect();

        $this->assertSame('inactive', $activity->fresh()->status);
        $this->get(route('public.index'))->assertDontSee('PC Games');

        $this->actingAs($this->admin)
            ->post(route('admin.activities.toggle-active', $activity))
            ->assertRedirect();

        $this->assertSame('active', $activity->fresh()->status);
        $this->get(route('public.index'))->assertSee('PC Games');
    }

    public function test_admin_can_remove_an_activity_image(): void
    {
        Storage::fake('public');

        $activity = Resource::where('code', 'PS5')->firstOrFail();
        Storage::disk('public')->put('activities/old-cover.jpg', 'fake');

        $activity->update(['image_path' => 'activities/old-cover.jpg']);

        $business = Business::primary();

        $this->actingAs($this->admin)->patch(route('admin.activities.update', $activity), [
            'business_id' => $business->id,
            'category_id' => $activity->category_id,
            'name' => $activity->name,
            'code' => $activity->code,
            'capacity' => $activity->capacity,
            'duration_minutes' => $activity->duration_minutes,
            'pricing_type' => $activity->pricing_type,
            'price' => 1000,
            'sale_price' => 650,
            'status' => $activity->status,
            'remove_image' => 1,
        ])->assertRedirect(route('admin.activities.index'));

        $this->assertNull($activity->fresh()->image_path);
        Storage::disk('public')->assertMissing('activities/old-cover.jpg');
    }

    public function test_public_homepage_shows_the_fixed_packages_from_the_database(): void
    {
        $this->get(route('public.index'))
            ->assertOk()
            ->assertSee('Account packages')
            ->assertSee('5 Hours')
            ->assertSee('900')
            ->assertSee('20 Hours')
            ->assertSee('3200');
    }

    public function test_validation_rejects_a_sale_price_above_the_regular_price(): void
    {
        $category = Category::where('slug', 'pc-gaming')->firstOrFail();
        $business = Business::primary();

        $this->actingAs($this->admin)->post(route('admin.activities.store'), [
            'business_id' => $business->id,
            'category_id' => $category->id,
            'name' => 'Bad Price Activity',
            'capacity' => 1,
            'duration_minutes' => 60,
            'pricing_type' => 'hourly',
            'price' => 100,
            'sale_price' => 200,
            'status' => 'active',
        ])->assertSessionHasErrors('sale_price');

        $this->assertDatabaseMissing('resources', ['name' => 'Bad Price Activity']);
    }

    /** A future occurrence of a weekday, so the tests never depend on "today". */
    protected function nextWeekdayAt(int $dayOfWeek, string $time): Carbon
    {
        $date = Carbon::now()->startOfDay();

        do {
            $date->addDay();
        } while ((int) $date->dayOfWeek !== $dayOfWeek);

        return $date->setTimeFromTimeString($time);
    }
}
