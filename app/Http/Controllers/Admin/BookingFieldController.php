<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingField;
use App\Models\Resource;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Lets an admin decide which booking fields appear on the customer form,
 * whether they are required, and in what order — all without code changes.
 */
class BookingFieldController extends Controller
{
    public function index(Request $request): View
    {
        $fields = BookingField::with('resource.category')
            ->when($request->filled('resource'), fn ($q) => $q->where('resource_id', $request->integer('resource')))
            ->when($request->input('resource') === 'global', fn ($q) => $q->whereNull('resource_id'))
            ->orderByRaw('resource_id is null desc')
            ->orderBy('sort_order')
            ->get();

        return view('admin.fields.index', [
            'fields' => $fields,
            'resources' => Resource::with('category')->orderBy('name')->get(),
            'types' => BookingField::TYPES,
            'selected' => $request->input('resource'),
        ]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $this->payload($request);

        $field = BookingField::create($data);

        $logger->log(
            ActivityLogger::CONFIG_UPDATED,
            "Booking field \"{$field->label}\" added.".($field->isGlobal() ? ' (all activities)' : ''),
            $field,
            ['resource_id' => $field->resource_id, 'required' => $field->is_required]
        );

        return back()->with('success', 'Booking field added.');
    }

    public function update(Request $request, BookingField $field, ActivityLogger $logger)
    {
        $data = $this->payload($request, $field);

        $field->update($data);

        $logger->log(
            ActivityLogger::CONFIG_UPDATED,
            "Booking field \"{$field->label}\" updated.",
            $field,
            ['required' => $field->is_required, 'visible' => $field->is_visible]
        );

        return back()->with('success', 'Booking field updated.');
    }

    public function destroy(BookingField $field, ActivityLogger $logger)
    {
        $label = $field->label;
        $field->delete();

        $logger->log(ActivityLogger::CONFIG_UPDATED, "Booking field \"{$label}\" removed.", null, ['field' => $label]);

        return back()->with('success', 'Booking field removed.');
    }

    /** Normalise + validate the incoming payload. */
    protected function payload(Request $request, ?BookingField $existing = null): array
    {
        $data = $request->validate([
            'resource_id' => ['nullable', 'exists:resources,id'],
            'key' => ['required', 'string', 'max:50', 'regex:/^[a-z0-9_]+$/'],
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:'.implode(',', array_keys(BookingField::TYPES))],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'options' => ['nullable', 'string', 'max:2000'],
            'default_value' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [
            'key.regex' => 'The key may only contain lowercase letters, numbers and underscores.',
        ]);

        $resourceId = $data['resource_id'] ?? null;

        // MySQL unique indexes ignore NULLs, so enforce the pair manually.
        $duplicate = BookingField::query()
            ->where('key', $data['key'])
            ->when($resourceId, fn ($q) => $q->where('resource_id', $resourceId), fn ($q) => $q->whereNull('resource_id'))
            ->when($existing, fn ($q) => $q->whereKeyNot($existing->id))
            ->exists();

        if ($duplicate) {
            throw ValidationException::withMessages(['key' => 'A field with this key already exists for that scope.']);
        }

        $options = collect(explode("\n", (string) ($data['options'] ?? '')))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();

        return [
            'resource_id' => $resourceId,
            'key' => $data['key'],
            'label' => $data['label'],
            'type' => $data['type'],
            'placeholder' => $data['placeholder'] ?? null,
            'default_value' => $data['default_value'] ?? null,
            'options' => $options ?: null,
            'is_required' => $request->boolean('is_required'),
            'is_visible' => $request->boolean('is_visible'),
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => (int) ($data['sort_order'] ?? 0),
        ];
    }
}
