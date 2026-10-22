<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $business = Business::primary() ?? Business::create(['name' => 'Main Branch', 'slug' => 'main-branch']);

        return view('admin.settings.index', [
            'business' => $business,
            'settings' => Setting::allCached(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:30'],
            'opens_at' => ['required', 'date_format:H:i'],
            'closes_at' => ['required', 'date_format:H:i'],
            'overnight' => ['boolean'],
            'currency' => ['required', 'string', 'max:8'],
            'timezone' => ['required', 'string', 'max:50'],
            'auto_confirm_online' => ['boolean'],
            'cancellation_window_hours' => ['required', 'integer', 'min:0', 'max:168'],
        ]);

        $business = Business::primary();
        if (! $business) {
            $business = Business::create([
                'name' => $data['name'],
                'slug' => \Illuminate\Support\Str::slug($data['name']),
            ]);
        }

        $business->update([
            'name' => $data['name'],
            'address' => $data['address'] ?? null,
            'phone' => $data['phone'] ?? null,
            'opens_at' => $data['opens_at'],
            'closes_at' => $data['closes_at'],
            'overnight' => $request->boolean('overnight'),
            'currency' => $data['currency'],
            'timezone' => $data['timezone'],
        ]);

        Setting::set('auto_confirm_online', $request->boolean('auto_confirm_online') ? '1' : '0');
        Setting::set('cancellation_window_hours', (string) $data['cancellation_window_hours']);

        return back()->with('success', 'Settings saved.');
    }
}
