<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The gaming zone is open 24 hours a day, 7 days a week.
     *
     * Equal opening and closing times (with the overnight flag) is how the
     * engine stores a round-the-clock schedule: the slot grid then covers
     * 00:00 → 00:00 next day. The shared slot presets are widened to match so
     * online availability is not silently limited to the old noon–midnight
     * window.
     */
    public function up(): void
    {
        DB::table('businesses')->update([
            'opens_at' => '00:00:00',
            'closes_at' => '00:00:00',
            'overnight' => true,
            'updated_at' => now(),
        ]);

        // Only replace the shared presets — activity-specific windows an admin
        // configured by hand are left untouched.
        DB::table('time_slots')->whereNull('resource_id')->delete();

        $now = now();
        $presets = [
            ['Overnight', '00:00', '12:00', 60, 1],
            ['Daytime', '12:00', '00:00', 60, 2],
        ];

        $rows = [];

        foreach ($presets as [$label, $start, $end, $minutes, $order]) {
            $rows[] = [
                'resource_id' => null,
                'label' => $label,
                'start_time' => $start.':00',
                'end_time' => $end.':00',
                'day_of_week' => null,
                'slot_minutes' => $minutes,
                'is_active' => true,
                'sort_order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('time_slots')->insert($rows);
    }

    public function down(): void
    {
        DB::table('businesses')->update([
            'opens_at' => '12:00:00',
            'closes_at' => '00:00:00',
            'overnight' => true,
            'updated_at' => now(),
        ]);

        DB::table('time_slots')->whereNull('resource_id')->delete();

        $now = now();
        $presets = [
            ['Afternoon', '12:00', '17:00', 60, 1],
            ['Evening', '17:00', '21:00', 60, 2],
            ['Night', '21:00', '00:00', 60, 3],
        ];

        $rows = [];

        foreach ($presets as [$label, $start, $end, $minutes, $order]) {
            $rows[] = [
                'resource_id' => null,
                'label' => $label,
                'start_time' => $start.':00',
                'end_time' => $end.':00',
                'day_of_week' => null,
                'slot_minutes' => $minutes,
                'is_active' => true,
                'sort_order' => $order,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('time_slots')->insert($rows);
    }
};
