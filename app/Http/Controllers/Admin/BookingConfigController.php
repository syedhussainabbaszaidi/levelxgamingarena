<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Setting;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controls what the customer sees on the booking site and the booking policy
 * (slot length, minimum duration, how far ahead bookings are allowed).
 */
class BookingConfigController extends Controller
{
    /** Boolean display toggles exposed to the customer interface. */
    public const TOGGLES = [
        'show_activity_description' => 'Show activity descriptions',
        'show_activity_image' => 'Show activity images',
        'show_hourly_rate' => 'Show starting hourly rate',
        'show_planner' => 'Show the booking planner',
        'show_remaining_slots' => 'Show remaining availability count',
        'allow_online_booking' => 'Allow new online bookings',
        'auto_confirm_online' => 'Auto-confirm online bookings',
    ];

    public function index(Request $request): View
    {
        return view('admin.config.index', [
            'toggles' => self::TOGGLES,
            'settings' => Setting::allCached(),
            'business' => Business::orderBy('id')->first(),
        ]);
    }

    public function update(Request $request, ActivityLogger $logger)
    {
        $data = $request->validate([
            'slot_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'min_duration_minutes' => ['required', 'integer', 'min:15', 'max:480'],
            'max_advance_days' => ['required', 'integer', 'min:1', 'max:365'],
            'cancellation_window_hours' => ['required', 'integer', 'min:0', 'max:168'],
        ]);

        // Boolean display toggles
        foreach (array_keys(self::TOGGLES) as $key) {
            Setting::set($key, $request->boolean($key) ? '1' : '0');
        }
        Setting::set('cancellation_window_hours', (string) $data['cancellation_window_hours']);

        $business = Business::orderBy('id')->first();
        if ($business) {
            $business->update([
                'slot_minutes' => $data['slot_minutes'],
                'min_duration_minutes' => $data['min_duration_minutes'],
                'max_advance_days' => $data['max_advance_days'],
            ]);
        }

        $logger->log(
            ActivityLogger::CONFIG_UPDATED,
            'Booking configuration updated (display toggles + booking policy).',
            $business,
            $data + collect(array_keys(self::TOGGLES))->mapWithKeys(fn ($k) => [$k => $request->boolean($k)])->all()
        );

        return back()->with('success', 'Booking configuration saved.');
    }
}
