<?php

namespace Database\Seeders;

use App\Models\Booking;
use App\Models\Business;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\Role;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Roles + permission catalog must exist before any user is assigned one.
        $this->call(RolePermissionSeeder::class);

        // Users
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);
        $admin->assignRole(Role::SUPER_ADMIN);

        $manager = User::create([
            'name' => 'Booking Manager',
            'email' => 'manager@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);
        $manager->assignRole(Role::BOOKING_MANAGER);

        $staff = User::create([
            'name' => 'Counter Staff',
            'email' => 'staff@example.com',
            'password' => bcrypt('password'),
            'role' => 'staff',
            'is_active' => true,
        ]);
        $staff->assignRole(Role::STAFF);

        $customerUser = User::create([
            'name' => 'Demo Customer',
            'email' => 'customer@example.com',
            'password' => bcrypt('password'),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Business
        $business = Business::create([
            'name' => 'LevelXGamingArena',
            'slug' => 'levelxgamingarena',
            'address' => 'Federal B Area, Block 12, Karachi',
            'phone' => '+92 342 2631057',
            'opens_at' => '00:00',
            'closes_at' => '00:00',
            'overnight' => true,
            'timezone' => 'Asia/Karachi',
            'currency' => 'PKR',
            'slot_minutes' => 60,
            'min_duration_minutes' => 30,
            'max_advance_days' => 30,
        ]);

        // Categories + Resources
        $cats = [];
        $catData = [
            'Gaming Zone' => ['gaming-zone', '#6366f1', 'Consoles and gaming PCs'],
            'Snooker' => ['snooker', '#10b981', 'Snooker tables'],
            'PS5' => ['ps5', '#3b82f6', 'PlayStation 5 booths'],
            'PC Gaming' => ['pc-gaming', '#8b5cf6', 'High-end gaming PCs'],
            'Pool' => ['pool', '#f59e0b', 'Pool tables'],
            'VIP Rooms' => ['vip-rooms', '#ec4899', 'Private rooms'],
        ];
        foreach ($catData as $name => [$slug, $color, $desc]) {
            $cats[$name] = Category::create([
                'business_id' => $business->id,
                'name' => $name,
                'slug' => $slug,
                'description' => $desc,
                'color' => $color,
                'is_active' => true,
            ]);
        }

        $resourcesData = [
            ['Gaming Zone TV 1', 'GZ-1', 'Gaming Zone', 500],
            ['Gaming Zone TV 2', 'GZ-2', 'Gaming Zone', 500],
            ['Snooker Table 1', 'SNK-1', 'Snooker', 400],
            ['Snooker Table 2', 'SNK-2', 'Snooker', 400],
            ['PS5 Booth 1', 'PS5-1', 'PS5', 600],
            ['PS5 Booth 2', 'PS5-2', 'PS5', 600],
            ['Gaming PC 1', 'PC-1', 'PC Gaming', 350],
            ['Gaming PC 2', 'PC-2', 'PC Gaming', 350],
            ['Pool Table 1', 'POOL-1', 'Pool', 300],
            ['VIP Room A', 'VIP-A', 'VIP Rooms', 1200],
        ];
        foreach ($resourcesData as $i => [$name, $code, $cat, $rate]) {
            Resource::create([
                'business_id' => $business->id,
                'category_id' => $cats[$cat]->id,
                'name' => $name,
                'code' => $code,
                'capacity' => $cat === 'VIP Rooms' ? 8 : 4,
                'hourly_rate' => $rate,
                'status' => 'active',
                'sort_order' => $i,
            ]);
        }
        Resource::create([
            'business_id' => $business->id,
            'category_id' => $cats['Snooker']->id,
            'name' => 'Snooker Table 3 (Maintenance)',
            'code' => 'SNK-3',
            'capacity' => 4,
            'hourly_rate' => 400,
            'status' => 'maintenance',
            'sort_order' => 11,
        ]);

        // Themed cover artwork for every activity (used by customer cards + admin).
        $this->call(ActivityImageSeeder::class);

        // The published price list shown on the customer website (idempotent:
        // matched by activity code, so re-seeding never duplicates a package).
        $this->call(ActivityCatalogSeeder::class);

        // Pricing rules: off-peak + peak per category (business-wide simple set)
        PricingRule::create([
            'business_id' => $business->id,
            'name' => 'Off-Peak (10:00–18:00)',
            'start_time' => '10:00',
            'end_time' => '18:00',
            'hourly_rate' => 0, // not used: business-wide rule would override resource rates
            'min_minutes' => 30,
            'slot_minutes' => 30,
            'is_active' => false, // disabled by default; demo uses resource base rates
            'priority' => 1,
        ]);
        PricingRule::create([
            'business_id' => $business->id,
            'category_id' => $cats['Snooker']->id,
            'name' => 'Snooker Peak (18:00–02:00)',
            'start_time' => '18:00',
            'end_time' => '02:00',
            'overnight' => true,
            'hourly_rate' => 600,
            'min_minutes' => 30,
            'slot_minutes' => 30,
            'is_active' => true,
            'priority' => 10,
        ]);
        PricingRule::create([
            'business_id' => $business->id,
            'category_id' => $cats['PS5']->id,
            'name' => 'PS5 Evening (18:00–00:00)',
            'start_time' => '18:00',
            'end_time' => '00:00',
            'overnight' => true,
            'hourly_rate' => 800,
            'min_minutes' => 30,
            'slot_minutes' => 30,
            'is_active' => true,
            'priority' => 10,
        ]);

        // Customers
        $custNames = [
            ['Ali Raza', '03001234567'], ['Bilal Khan', '03007654321'], ['Usman Tariq', '03211112222'],
            ['Hamza Sheikh', '03334445555'], ['Zain Abbas', '03456667777'],
        ];
        $customers = [];
        foreach ($custNames as [$name, $phone]) {
            $customers[] = Customer::create([
                'business_id' => $business->id,
                'name' => $name,
                'phone' => $phone,
            ]);
        }

        // Link the demo customer account to a customer record so the customer
        // dashboard has real bookings to show out of the box.
        $demoCustomer = Customer::create([
            'business_id' => $business->id,
            'user_id' => $customerUser->id,
            'name' => $customerUser->name,
            'phone' => '03000000000',
            'email' => $customerUser->email,
        ]);
        $customers[] = $demoCustomer;

        // Booking form fields + time-slot presets (admin-editable, no code changes)
        $this->call(BookingConfigSeeder::class);

        // Demo bookings: one live session, some upcoming, some completed
        $snooker1 = Resource::where('code', 'SNK-1')->first();
        $ps5b1 = Resource::where('code', 'PS5-1')->first();
        $pc1 = Resource::where('code', 'PC-1')->first();

        // Live session (started 40 min ago, 1h booking)
        $live = Booking::create([
            'business_id' => $business->id,
            'resource_id' => $snooker1->id,
            'customer_id' => $customers[0]->id,
            'user_id' => $staff->id,
            'code' => 'BK-DEMO-00001',
            'source' => 'counter',
            'status' => 'confirmed',
            'starts_at' => now()->subMinutes(40),
            'ends_at' => now()->addMinutes(20),
            'duration_minutes' => 60,
            'estimated_cost' => 400,
            'final_cost' => 400,
            'checked_in_at' => now()->subMinutes(40),
        ]);

        // Upcoming reservation tonight
        Booking::create([
            'business_id' => $business->id,
            'resource_id' => $ps5b1->id,
            'customer_id' => $customers[1]->id,
            'user_id' => null,
            'code' => 'BK-DEMO-00002',
            'source' => 'online',
            'status' => 'confirmed',
            'starts_at' => now()->addHours(2)->startOfHour(),
            'ends_at' => now()->addHours(2)->startOfHour()->addHours(2),
            'duration_minutes' => 120,
            'estimated_cost' => 1600,
            'final_cost' => 1600,
        ]);

        // Pending online booking tomorrow
        Booking::create([
            'business_id' => $business->id,
            'resource_id' => $pc1->id,
            'customer_id' => $customers[2]->id,
            'code' => 'BK-DEMO-00003',
            'source' => 'online',
            'status' => 'pending',
            'starts_at' => now()->addDay()->setHour(17),
            'ends_at' => now()->addDay()->setHour(19),
            'duration_minutes' => 120,
            'estimated_cost' => 700,
            'final_cost' => 700,
        ]);

        // Completed yesterday with payment
        $done = Booking::create([
            'business_id' => $business->id,
            'resource_id' => $snooker1->id,
            'customer_id' => $customers[3]->id,
            'user_id' => $staff->id,
            'code' => 'BK-DEMO-00004',
            'source' => 'counter',
            'status' => 'completed',
            'starts_at' => now()->subDay()->setHour(19),
            'ends_at' => now()->subDay()->setHour(21),
            'duration_minutes' => 120,
            'estimated_cost' => 1200,
            'final_cost' => 1200,
            'checked_in_at' => now()->subDay()->setHour(19),
            'ended_at' => now()->subDay()->setHour(21),
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'paid_at' => now()->subDay()->setHour(21),
        ]);
        Payment::create([
            'business_id' => $business->id,
            'booking_id' => $done->id,
            'customer_id' => $customers[3]->id,
            'user_id' => $staff->id,
            'code' => 'PAY-DEMO-00001',
            'amount' => 1200,
            'method' => 'cash',
            'kind' => 'full',
        ]);
        $customers[3]->increment('total_spent', 1200);
        $customers[3]->increment('total_visits');

        // A booking for the demo customer account (customer dashboard demo)
        Booking::create([
            'business_id' => $business->id,
            'resource_id' => $snooker1->id,
            'customer_id' => $demoCustomer->id,
            'user_id' => $customerUser->id,
            'code' => 'BK-DEMO-00005',
            'source' => 'online',
            'status' => 'confirmed',
            'starts_at' => now()->addDays(2)->setHour(18),
            'ends_at' => now()->addDays(2)->setHour(20),
            'duration_minutes' => 120,
            'estimated_cost' => 1200,
            'final_cost' => 1200,
        ]);

        // Settings
        Setting::set('auto_confirm_online', '0');
        Setting::set('cancellation_window_hours', '2');
        Setting::set('show_activity_description', '1');
        Setting::set('show_activity_image', '1');
        Setting::set('show_hourly_rate', '1');
        Setting::set('show_planner', '1');
    }
}
