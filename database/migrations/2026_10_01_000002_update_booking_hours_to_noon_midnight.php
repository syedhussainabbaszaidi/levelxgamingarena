<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Bookable window is 12:00 PM (noon) through 12:00 AM (midnight).
     * Time-slot presets are regenerated as clean hourly windows so the
     * customer sees every slot from noon to midnight.
     */
    public function up(): void
    {
        DB::table('businesses')->update([
            'opens_at' => '12:00:00',
            'closes_at' => '00:00:00',
            'overnight' => true,
            'updated_at' => now(),
        ]);

        DB::table('time_slots')->delete();

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

    public function down(): void
    {
        DB::table('businesses')->update([
            'opens_at' => '10:00:00',
            'closes_at' => '02:00:00',
            'overnight' => true,
            'updated_at' => now(),
        ]);

        DB::table('time_slots')->delete();
    }
};
