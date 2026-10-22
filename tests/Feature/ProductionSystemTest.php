<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\BookingField;
use App\Models\Permission;
use App\Models\Resource;
use App\Models\Role;
use App\Models\TimeSlot;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Covers the production features: RBAC enforcement, dynamic booking
 * configuration, the customer flow and the audit log.
 */
class ProductionSystemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    /* ------------------------------------------------------------------ */
    /* Roles & permissions */
    /* ------------------------------------------------------------------ */

    public function test_roles_and_permissions_are_seeded(): void
    {
        $this->assertDatabaseCount('permissions', count(Permission::CATALOG));
        $this->assertDatabaseCount('roles', 4);

        $staff = Role::where('slug', Role::STAFF)->firstOrFail();
        $this->assertTrue($staff->hasPermission('bookings.view'));
        $this->assertFalse($staff->hasPermission('users.manage'));

        $superAdmin = User::where('email', 'admin@example.com')->firstOrFail();
        $this->assertTrue($superAdmin->isSuperAdmin());
        $this->assertTrue($superAdmin->hasPermissionTo('roles.manage'));
    }

    public function test_staff_cannot_access_user_management_but_admin_can(): void
    {
        $staff = User::where('email', 'staff@example.com')->firstOrFail();
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    }

    public function test_booking_manager_can_view_bookings_but_not_roles(): void
    {
        $manager = User::where('email', 'manager@example.com')->firstOrFail();

        $this->actingAs($manager)->get(route('admin.bookings.index'))->assertOk();
        $this->actingAs($manager)->get(route('admin.roles.index'))->assertForbidden();
    }

    public function test_customer_cannot_reach_admin_or_change_status(): void
    {
        $customer = User::where('email', 'customer@example.com')->firstOrFail();
        $booking = Booking::firstOrFail();

        $this->actingAs($customer)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($customer)
            ->patch(route('admin.bookings.status', $booking), ['status' => 'cancelled'])
            ->assertForbidden();
    }

    /* ------------------------------------------------------------------ */
    /* Dynamic booking configuration */
    /* ------------------------------------------------------------------ */

    public function test_time_slot_configuration_drives_availability(): void
    {
        $resource = Resource::where('code', 'SNK-1')->firstOrFail();

        TimeSlot::create([
            'resource_id' => $resource->id,
            'label' => 'Test window',
            'start_time' => '15:00',
            'end_time' => '17:00',
            'slot_minutes' => 60,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $result = app(AvailabilityService::class)
            ->configuredSlots($resource->fresh(), now()->addDays(3), 60);

        $times = collect($result['slots'])->pluck('time');

        $this->assertTrue($times->contains('15:00'));
        $this->assertTrue($times->contains('16:00'));
    }

    public function test_required_dynamic_field_is_validated_on_booking(): void
    {
        $resource = Resource::where('code', 'PS5')->firstOrFail();
        $customer = User::where('email', 'customer@example.com')->firstOrFail();

        $values = $this->answersFor($resource);

        // Drop one required answer → validation must fail for that field.
        $field = BookingField::query()
            ->visible()
            ->forResource($resource->id)
            ->where('is_required', true)
            ->firstOrFail();

        $payload = $this->bookingPayload($resource, $values);
        unset($payload['fields'][$field->id]);

        $this->actingAs($customer)->post('/book', $payload)
            ->assertSessionHasErrors("fields.{$field->id}");
    }

    public function test_customer_booking_flow_stores_answers_and_breakdown(): void
    {
        $resource = Resource::where('code', 'PS5')->firstOrFail();
        $customer = User::where('email', 'customer@example.com')->firstOrFail();

        $response = $this->actingAs($customer)->post('/book', $this->bookingPayload($resource, $this->answersFor($resource)));

        $booking = Booking::where('user_id', $customer->id)->latest('id')->firstOrFail();

        $response->assertRedirect(route('public.confirmation', $booking->code));
        $this->assertGreaterThan(0, (float) $booking->final_cost);
        $this->assertNotNull($booking->customer_id);
        $this->assertTrue($booking->fieldValues()->exists());

        $this->actingAs($customer)->get(route('public.confirmation', $booking->code))->assertOk();
    }

    public function test_missing_time_slot_reports_a_readable_error(): void
    {
        $resource = Resource::where('code', 'PS5')->firstOrFail();
        $customer = User::where('email', 'customer@example.com')->firstOrFail();

        $payload = $this->bookingPayload($resource, $this->answersFor($resource));
        unset($payload['time']);

        $this->actingAs($customer)->post('/book', $payload)
            ->assertSessionHasErrors(['time' => 'The time slot field is required.']);
    }

    /**
     * Regression: the booking planner's inline script must be emitted after the
     * form markup, otherwise it runs in <head>, finds no elements, throws and
     * leaves the hidden `time` input empty ("time is required" on submit).
     */
    public function test_booking_planner_script_is_emitted_after_the_markup(): void
    {
        $resource = Resource::where('code', 'PS5')->firstOrFail();
        $html = $this->get(route('public.activity', $resource))->getContent();

        $formPos = strpos($html, 'id="booking-form"');
        $scriptPos = strpos($html, 'function init()');
        $headEnd = strpos($html, '</head>');

        $this->assertNotFalse($formPos, 'The booking form should be rendered.');
        $this->assertNotFalse($scriptPos, 'The planner script should be rendered.');
        $this->assertGreaterThan($formPos, $scriptPos, 'The planner script must load after the form markup.');
        $this->assertStringNotContainsString(
            'function init()',
            substr($html, 0, $headEnd),
            'Inline page scripts must not be emitted inside <head>.'
        );
    }

    public function test_customer_cannot_view_another_customers_booking(): void
    {
        $owner = User::where('email', 'customer@example.com')->firstOrFail();
        $booking = Booking::where('user_id', $owner->id)->firstOrFail();

        $intruder = User::create([
            'name' => 'Intruder',
            'email' => 'intruder@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
        ]);

        $this->actingAs($intruder)->get(route('public.booking', $booking))->assertForbidden();
    }

    /* ------------------------------------------------------------------ */
    /* Audit log */
    /* ------------------------------------------------------------------ */

    public function test_booking_lifecycle_is_audited(): void
    {
        $resource = Resource::where('code', 'PS5-2')->firstOrFail();

        $booking = app(BookingService::class)->create([
            'resource_id' => $resource->id,
            'starts_at' => now()->addDays(4)->setHour(13)->format('Y-m-d H:i'),
            'duration_minutes' => 60,
            'source' => 'admin',
        ]);

        $this->assertDatabaseHas('activity_logs', ['action' => ActivityLogger::BOOKING_CREATED]);

        app(BookingService::class)->changeStatus($booking, 'confirmed');

        $this->assertDatabaseHas('activity_logs', [
            'action' => ActivityLogger::BOOKING_STATUS,
            'subject_id' => $booking->id,
        ]);
    }

    public function test_activity_log_page_supports_filters(): void
    {
        $admin = User::where('email', 'admin@example.com')->firstOrFail();

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'settings.updated',
            'description' => 'System settings updated.',
            'ip_address' => '127.0.0.1',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.logs.index', ['action' => 'settings.updated']))
            ->assertOk()
            ->assertSee('System settings updated.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers */
    /* ------------------------------------------------------------------ */

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

    protected function bookingPayload(Resource $resource, array $answers): array
    {
        return [
            'resource_id' => $resource->id,
            'date' => now()->addDays(5)->format('Y-m-d'),
            'time' => '13:00',
            'duration_minutes' => 60,
            'fields' => $answers,
        ];
    }
}
