<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Resource;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * Imports the published Level X price list (Monday Madness, Account Packages,
 * Opening Discounts and the PC Games Mid Day Package) as real, admin-managed
 * activities.
 *
 * Safe to re-run: records are matched by their activity code and only created
 * once, so repeated deployments never duplicate a package. Rate windows are
 * matched by name and kept up to date.
 */
class ActivityCatalogSeeder extends Seeder
{
    /** Old demo "station" resources that the price list replaces online. */
    protected array $retiredDemoCodes = [
        'GZ-1', 'GZ-2', 'SNK-1', 'SNK-2', 'PS5-1', 'PS5-2',
        'PC-1', 'PC-2', 'POOL-1', 'VIP-A', 'SNK-3',
    ];

    /**
     * Demo/placeholder rate rules that conflict with the published price list.
     * They stay in the system (Admin → Pricing) and can be re-enabled there.
     */
    protected array $retiredRuleNames = [
        'Off-Peak (10:00–18:00)',
        'Snooker Peak (18:00–02:00)',
        'PS5 Evening (18:00–00:00)',
        'Rule no 1',
    ];

    public function run(): void
    {
        $business = Business::primary() ?? Business::orderBy('id')->first();

        if (! $business) {
            $this->command?->warn('Activity catalog skipped: no business record found.');

            return;
        }

        $categories = $this->categories($business);

        foreach ($this->catalog() as $item) {
            $category = $categories[$item['category']] ?? null;
            if (! $category) {
                continue;
            }

            $resource = Resource::firstOrCreate(
                ['business_id' => $business->id, 'code' => $item['code']],
                [
                    'category_id' => $category->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'capacity' => $item['capacity'],
                    'duration_minutes' => $item['duration_minutes'],
                    'hourly_rate' => $item['hourly_rate'],
                    'pricing_type' => $item['pricing_type'],
                    'sale_price' => $item['sale_price'],
                    'promo_label' => $item['promo_label'],
                    'fixed_price' => $item['fixed_price'],
                    'package_minutes' => $item['package_minutes'],
                    'min_minutes' => $item['min_minutes'],
                    'max_minutes' => $item['max_minutes'],
                    'addon_label' => $item['addon_label'],
                    'addon_price' => $item['addon_price'],
                    'status' => 'active',
                    'is_online_bookable' => $item['online'],
                    'featured' => $item['featured'],
                    'sort_order' => $item['sort_order'],
                ]
            );

            // Attach the bundled cover on first import, and repair it if the
            // stored file has gone missing (e.g. a fresh storage directory).
            // A cover the admin replaced is never overwritten.
            $coverMissing = $resource->image_path
                && ! Storage::disk('public')->exists($resource->image_path);

            if ($item['cover'] && ($resource->wasRecentlyCreated || $coverMissing)) {
                $this->installCover($item['cover']);
                $resource->forceFill(['image_path' => 'activities/'.$item['cover']])->save();
            }
        }

        // The public catalogue is the published price list — the old demo
        // stations stay in the system for existing bookings and back-office
        // use, but are no longer offered on the customer website.
        Resource::whereIn('code', $this->retiredDemoCodes)->update(['is_online_bookable' => false]);

        PricingRule::whereIn('name', $this->retiredRuleNames)->update(['is_active' => false]);

        $this->rateWindows($business, $categories);
    }

    /** @return array<string, Category> keyed by slug */
    protected function categories(Business $business): array
    {
        $definitions = [
            'pc-gaming' => ['PC Gaming', '#8b5cf6', 'High-end gaming PCs'],
            'ps5' => ['PS5', '#3b82f6', 'PlayStation 5 booths'],
            'snooker' => ['Snooker', '#10b981', 'Snooker tables and premium rooms'],
            'table-tennis' => ['Table Tennis', '#22d3ee', 'Private table tennis rooms'],
            'account-packages' => ['Account Packages', '#f59e0b', 'Prepaid play-hour packages'],
        ];

        $categories = [];

        foreach ($definitions as $slug => [$name, $color, $description]) {
            $categories[$slug] = Category::firstOrCreate(
                ['business_id' => $business->id, 'slug' => $slug],
                ['name' => $name, 'color' => $color, 'description' => $description, 'is_active' => true]
            );
        }

        return $categories;
    }

    /** The published price list. */
    protected function catalog(): array
    {
        return [
            [
                'code' => 'PC-GAMES',
                'name' => 'PC Games',
                'category' => 'pc-gaming',
                'pricing_type' => Resource::PRICING_HOURLY,
                'hourly_rate' => 250,
                'sale_price' => 200,
                'promo_label' => 'Opening discount',
                'fixed_price' => null,
                'package_minutes' => null,
                'min_minutes' => null,
                'max_minutes' => null,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 1,
                'duration_minutes' => 60,
                'description' => 'High-end gaming PCs running CS2, PUBG, Valorant, DOTA and more. Monday Madness and the Mid Day package are applied automatically at checkout.',
                'online' => true,
                'featured' => true,
                'sort_order' => 1,
                'cover' => 'pc-gaming.jpg',
            ],
            [
                'code' => 'PS5',
                'name' => 'PS5',
                'category' => 'ps5',
                'pricing_type' => Resource::PRICING_HOURLY,
                'hourly_rate' => 1000,
                'sale_price' => 650,
                'promo_label' => 'Opening discount',
                'fixed_price' => null,
                'package_minutes' => null,
                'min_minutes' => null,
                'max_minutes' => null,
                'addon_label' => 'additional controller',
                'addon_price' => 300,
                'capacity' => 2,
                'duration_minutes' => 60,
                'description' => 'PlayStation 5 with two controllers included. Add an extra controller for 300 RS per hour.',
                'online' => true,
                'featured' => true,
                'sort_order' => 2,
                'cover' => 'ps5.jpg',
            ],
            [
                'code' => 'SNK-PREMIUM',
                'name' => 'Premium Snooker Room',
                'category' => 'snooker',
                'pricing_type' => Resource::PRICING_PER_MINUTE,
                'hourly_rate' => 900, // 15 RS/minute
                'sale_price' => 720, // 12 RS/minute
                'promo_label' => 'Opening discount',
                'fixed_price' => null,
                'package_minutes' => null,
                'min_minutes' => null,
                'max_minutes' => null,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 4,
                'duration_minutes' => 60,
                'description' => 'Premium snooker room with a professional table, cues and rest. Billed per minute of play.',
                'online' => true,
                'featured' => true,
                'sort_order' => 3,
                'cover' => 'snooker.jpg',
            ],
            [
                'code' => 'TT-PRIVATE',
                'name' => 'Private Table Tennis Room',
                'category' => 'table-tennis',
                'pricing_type' => Resource::PRICING_HOURLY,
                'hourly_rate' => 800,
                'sale_price' => 600,
                'promo_label' => 'Opening discount',
                'fixed_price' => null,
                'package_minutes' => null,
                'min_minutes' => null,
                'max_minutes' => null,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 4,
                'duration_minutes' => 60,
                'description' => 'Private table tennis room with your own table, bats and balls. Book the room by the hour.',
                'online' => true,
                'featured' => false,
                'sort_order' => 4,
                'cover' => 'table-tennis.jpg',
            ],
            [
                'code' => 'ACC-OPENING',
                'name' => 'Account Opening',
                'category' => 'account-packages',
                'pricing_type' => Resource::PRICING_FIXED,
                'hourly_rate' => 0,
                'sale_price' => null,
                'promo_label' => null,
                'fixed_price' => 1000,
                'package_minutes' => null,
                'min_minutes' => null,
                'max_minutes' => null,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 1,
                'duration_minutes' => 60,
                'description' => 'One-time account opening charge — paid once before you start using play-hour packages.',
                'online' => false,
                'featured' => false,
                'sort_order' => 10,
                'cover' => null,
            ],
            [
                'code' => 'PKG-5H',
                'name' => '5 Hours',
                'category' => 'account-packages',
                'pricing_type' => Resource::PRICING_FIXED,
                'hourly_rate' => 0,
                'sale_price' => null,
                'promo_label' => null,
                'fixed_price' => 900,
                'package_minutes' => 300,
                'min_minutes' => 300,
                'max_minutes' => 300,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 1,
                'duration_minutes' => 300,
                'description' => 'Prepaid account package: 5 hours of arena time for 900 RS.',
                'online' => false,
                'featured' => false,
                'sort_order' => 11,
                'cover' => null,
            ],
            [
                'code' => 'PKG-8H',
                'name' => '8 Hours',
                'category' => 'account-packages',
                'pricing_type' => Resource::PRICING_FIXED,
                'hourly_rate' => 0,
                'sale_price' => null,
                'promo_label' => null,
                'fixed_price' => 1400,
                'package_minutes' => 480,
                'min_minutes' => 480,
                'max_minutes' => 480,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 1,
                'duration_minutes' => 480,
                'description' => 'Prepaid account package: 8 hours of arena time for 1400 RS.',
                'online' => false,
                'featured' => false,
                'sort_order' => 12,
                'cover' => null,
            ],
            [
                'code' => 'PKG-10H',
                'name' => '10 Hours',
                'category' => 'account-packages',
                'pricing_type' => Resource::PRICING_FIXED,
                'hourly_rate' => 0,
                'sale_price' => null,
                'promo_label' => null,
                'fixed_price' => 1700,
                'package_minutes' => 600,
                'min_minutes' => 600,
                'max_minutes' => 600,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 1,
                'duration_minutes' => 600,
                'description' => 'Prepaid account package: 10 hours of arena time for 1700 RS.',
                'online' => false,
                'featured' => false,
                'sort_order' => 13,
                'cover' => null,
            ],
            [
                'code' => 'PKG-20H',
                'name' => '20 Hours',
                'category' => 'account-packages',
                'pricing_type' => Resource::PRICING_FIXED,
                'hourly_rate' => 0,
                'sale_price' => null,
                'promo_label' => null,
                'fixed_price' => 3200,
                'package_minutes' => 1200,
                'min_minutes' => 1200,
                'max_minutes' => 1200,
                'addon_label' => null,
                'addon_price' => null,
                'capacity' => 1,
                'duration_minutes' => 1200,
                'description' => 'Prepaid account package: 20 hours of arena time for 3200 RS.',
                'online' => false,
                'featured' => false,
                'sort_order' => 14,
                'cover' => null,
            ],
        ];
    }

    /**
     * Monday Madness + the daily PC Mid Day package. Windows are attached to
     * the published activities themselves so legacy back-office resources and
     * older bookings are never repriced by mistake.
     */
    protected function rateWindows(Business $business, array $categories): void
    {
        $ids = Resource::where('business_id', $business->id)
            ->whereIn('code', ['PC-GAMES', 'PS5', 'SNK-PREMIUM', 'TT-PRIVATE'])
            ->pluck('id', 'code');

        $windows = [
            [
                'name' => 'Monday Madness — PC Games',
                'category_id' => $categories['pc-gaming']->id,
                'resource_id' => $ids['PC-GAMES'] ?? null,
                'day_of_week' => 1,
                'start_time' => '12:00',
                'end_time' => '00:00',
                'overnight' => true,
                'hourly_rate' => 140,
                'min_minutes' => 180,
                'priority' => 100,
            ],
            [
                'name' => 'Mid Day PC Package',
                'category_id' => $categories['pc-gaming']->id,
                'resource_id' => $ids['PC-GAMES'] ?? null,
                'day_of_week' => null,
                'start_time' => '09:00',
                'end_time' => '17:00',
                'overnight' => false,
                'hourly_rate' => 150,
                'min_minutes' => 180,
                'priority' => 50,
            ],
            [
                'name' => 'Monday Madness — Snooker',
                'category_id' => $categories['snooker']->id,
                'resource_id' => $ids['SNK-PREMIUM'] ?? null,
                'day_of_week' => 1,
                'start_time' => '12:00',
                'end_time' => '00:00',
                'overnight' => true,
                'hourly_rate' => 480, // 8 RS/minute
                'min_minutes' => 0,
                'priority' => 100,
            ],
            [
                'name' => 'Monday Madness — PS5',
                'category_id' => $categories['ps5']->id,
                'resource_id' => $ids['PS5'] ?? null,
                'day_of_week' => 1,
                'start_time' => '12:00',
                'end_time' => '00:00',
                'overnight' => true,
                'hourly_rate' => 600,
                'min_minutes' => 0,
                'priority' => 100,
            ],
            [
                'name' => 'Monday Madness — Table Tennis',
                'category_id' => $categories['table-tennis']->id,
                'resource_id' => $ids['TT-PRIVATE'] ?? null,
                'day_of_week' => 1,
                'start_time' => '12:00',
                'end_time' => '00:00',
                'overnight' => true,
                'hourly_rate' => 500,
                'min_minutes' => 0,
                'priority' => 100,
            ],
        ];

        foreach ($windows as $window) {
            PricingRule::updateOrCreate(
                ['business_id' => $business->id, 'name' => $window['name']],
                $window + [
                    'slot_minutes' => 30,
                    'is_active' => true,
                ]
            );
        }
    }

    /** Copy the bundled cover into the public disk when it is not there yet. */
    protected function installCover(string $file): void
    {
        $path = 'activities/'.$file;

        if (Storage::disk('public')->exists($path)) {
            return;
        }

        $source = public_path('img/activities/covers/'.$file);

        if (is_file($source)) {
            Storage::disk('public')->put($path, file_get_contents($source));
        }
    }
}
