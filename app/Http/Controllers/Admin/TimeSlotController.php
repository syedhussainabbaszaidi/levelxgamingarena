<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Resource;
use App\Models\TimeSlot;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimeSlotController extends Controller
{
    public function index(Request $request): View
    {
        $slots = TimeSlot::with('resource.category')
            ->when($request->filled('resource'), fn ($q) => $q->where('resource_id', $request->integer('resource')))
            ->orderByRaw('resource_id is null desc')
            ->orderBy('sort_order')
            ->get();

        return view('admin.slots.index', [
            'slots' => $slots,
            'resources' => Resource::with('category')->orderBy('name')->get(),
            'days' => TimeSlot::DAYS,
            'selected' => $request->input('resource'),
        ]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $slot = TimeSlot::create($this->payload($request));

        $logger->log(
            ActivityLogger::CONFIG_UPDATED,
            "Time slot \"{$slot->label}\" created ({$slot->timeLabel()}).",
            $slot,
            ['resource_id' => $slot->resource_id]
        );

        return back()->with('success', 'Time slot created.');
    }

    public function update(Request $request, TimeSlot $slot, ActivityLogger $logger)
    {
        $slot->update($this->payload($request));

        $logger->log(ActivityLogger::CONFIG_UPDATED, "Time slot \"{$slot->label}\" updated.", $slot);

        return back()->with('success', 'Time slot updated.');
    }

    public function destroy(TimeSlot $slot, ActivityLogger $logger)
    {
        $label = $slot->label;
        $slot->delete();

        $logger->log(ActivityLogger::CONFIG_UPDATED, "Time slot \"{$label}\" deleted.", null, ['slot' => $label]);

        return back()->with('success', 'Time slot deleted.');
    }

    protected function payload(Request $request): array
    {
        $data = $request->validate([
            'resource_id' => ['nullable', 'exists:resources,id'],
            'label' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
            'day_of_week' => ['nullable', 'integer', 'min:0', 'max:6'],
            'slot_minutes' => ['nullable', 'integer', 'min:15', 'max:480'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        return [
            'resource_id' => $data['resource_id'] ?? null,
            'label' => $data['label'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'day_of_week' => $data['day_of_week'] ?? null,
            'slot_minutes' => (int) ($data['slot_minutes'] ?? 60),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
