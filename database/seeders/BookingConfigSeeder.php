<?php

namespace Database\Seeders;

use App\Models\BookingField;
use App\Models\Resource;
use App\Models\TimeSlot;
use Illuminate\Database\Seeder;

class BookingConfigSeeder extends Seeder
{
    public function run(): void
    {
        // ---- Global booking fields (resource_id = null → every activity) ----
        $global = [
            ['player_name', 'Player name', 'text', 'Full name of the player', true, true, 1],
            ['players_count', 'Number of players', 'number', 'e.g. 4', true, true, 2],
            ['phone', 'Contact number', 'tel', '+92 300 0000000', true, true, 3],
            ['email', 'Email (optional)', 'email', 'you@example.com', false, true, 4],
            ['special_requests', 'Special requests', 'textarea', 'Anything we should prepare?', false, true, 5],
        ];

        foreach ($global as [$key, $label, $type, $placeholder, $required, $visible, $order]) {
            BookingField::updateOrCreate(
                ['resource_id' => null, 'key' => $key],
                [
                    'label' => $label,
                    'type' => $type,
                    'placeholder' => $placeholder,
                    'options' => null,
                    'is_required' => $required,
                    'is_visible' => $visible,
                    'is_active' => true,
                    'sort_order' => $order,
                ]
            );
        }

        // ---- Activity-specific fields ----
        $perActivity = [
            'SNK-1' => [
                ['cue_preference', 'Cue preference', 'select', null, false, true, 7,
                    ['House cue', 'Own cue', 'Coaching cue']],
                ['coaching', 'Add coaching session', 'checkbox', null, false, true, 8, null],
            ],
            'PS5-1' => [
                ['game_title', 'Preferred game', 'text', 'FIFA, Tekken, GT7…', false, true, 7, null],
                ['controllers', 'Extra controllers needed', 'number', '0', false, true, 8, null],
            ],
            'VIP-A' => [
                ['food_package', 'Food package', 'select', null, false, true, 7,
                    ['None', 'Snacks', 'Full platter']],
            ],
        ];

        foreach ($perActivity as $code => $fields) {
            $resource = Resource::where('code', $code)->first();
            if (! $resource) {
                continue;
            }

            foreach ($fields as [$key, $label, $type, $placeholder, $required, $visible, $order, $options]) {
                BookingField::updateOrCreate(
                    ['resource_id' => $resource->id, 'key' => $key],
                    [
                        'label' => $label,
                        'type' => $type,
                        'placeholder' => $placeholder,
                        'options' => $options,
                        'is_required' => $required,
                        'is_visible' => $visible,
                        'is_active' => true,
                        'sort_order' => $order,
                    ]
                );
            }
        }

        // ---- Shared time-slot presets ----
        // The venue trades 24/7: two half-day windows cover the whole clock.
        $slots = [
            ['Overnight', '00:00', '12:00', 60, 1],
            ['Daytime', '12:00', '00:00', 60, 2],
        ];

        foreach ($slots as [$label, $start, $end, $minutes, $order]) {
            TimeSlot::updateOrCreate(
                ['resource_id' => null, 'label' => $label],
                [
                    'start_time' => $start,
                    'end_time' => $end,
                    'day_of_week' => null,
                    'slot_minutes' => $minutes,
                    'is_active' => true,
                    'sort_order' => $order,
                ]
            );
        }
    }
}
