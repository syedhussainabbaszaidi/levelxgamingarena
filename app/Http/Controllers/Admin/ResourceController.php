<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Resource;
use App\Services\ActivityLogger;
use App\Services\ImageSearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * "Gaming activities" management. The database table is `resources` — it is the
 * bookable unit carrying the name, description, image, price and duration.
 */
class ResourceController extends Controller
{
    public function index(Request $request): View
    {
        $query = Resource::with(['category', 'business', 'pricingRules']);

        if ($search = $request->input('q')) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"));
        }
        if ($categoryId = $request->input('category')) {
            $query->where('category_id', $categoryId);
        }
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }
        if ($pricing = $request->input('pricing')) {
            $query->where('pricing_type', $pricing);
        }

        return view('admin.resources.index', [
            'resources' => $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString(),
            'categories' => Category::orderBy('name')->get(),
            'filters' => $request->only(['q', 'category', 'status', 'pricing']) + [
                'q' => null, 'category' => null, 'status' => null, 'pricing' => null,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.resources.create', [
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    /** AJAX: search gaming cover art by activity name/keywords. */
    public function searchImage(Request $request, ImageSearchService $images): JsonResponse
    {
        $query = (string) $request->input('q', '');

        return response()->json([
            'ok' => true,
            'query' => $query,
            'results' => $images->search($query),
        ]);
    }

    public function store(Request $request, ActivityLogger $logger, ImageSearchService $images)
    {
        $data = $this->validated($request);
        $data['code'] = $data['code'] ?: null;

        $image = $this->resolveImage($request, $images);

        // An image was chosen but could not be stored — tell the admin instead
        // of silently saving the activity without the picture they picked.
        if ($image === null && $this->hasImageSource($request)) {
            return back()->withInput()->with('error', 'That image could not be saved. Please try another image.');
        }

        $data['image_path'] = $image;

        $resource = Resource::create($data);

        $logger->log(
            ActivityLogger::ACTIVITY_CREATED,
            "Gaming activity \"{$resource->name}\" created (".$resource->priceLabel().').',
            $resource,
            [
                'status' => $resource->status,
                'pricing_type' => $resource->pricing_type,
                'rate' => (float) $resource->hourly_rate,
                'sale_price' => $resource->sale_price,
                'fixed_price' => $resource->fixed_price,
            ]
        );

        return redirect()->route('admin.activities.index')->with('success', 'Gaming activity created.');
    }

    public function edit(Resource $resource): View
    {
        return view('admin.resources.edit', [
            'resource' => $resource,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Resource $resource, ActivityLogger $logger, ImageSearchService $images)
    {
        $before = [
            'rate' => (float) $resource->hourly_rate,
            'sale' => $resource->sale_price,
            'fixed' => $resource->fixed_price,
            'type' => $resource->pricing_type,
        ];

        $data = $this->validated($request);
        $data['code'] = $data['code'] ?: null;

        $image = $this->resolveImage($request, $images);

        if ($image === null && $this->hasImageSource($request)) {
            return back()->withInput()->with('error', 'That image could not be saved. Please try another image.');
        }

        if ($image !== null) {
            $this->deleteImage($resource->image_path);
            $data['image_path'] = $image;
        } elseif ($request->boolean('remove_image')) {
            $this->deleteImage($resource->image_path);
            $data['image_path'] = null;
        }

        $resource->update($data);

        $logger->log(
            ActivityLogger::ACTIVITY_UPDATED,
            "Gaming activity \"{$resource->name}\" updated.",
            $resource,
            [
                'before' => $before,
                'after' => [
                    'rate' => (float) $resource->hourly_rate,
                    'sale' => $resource->sale_price,
                    'fixed' => $resource->fixed_price,
                    'type' => $resource->pricing_type,
                ],
            ]
        );

        $after = [
            'rate' => (float) $resource->hourly_rate,
            'sale' => $resource->sale_price,
            'fixed' => $resource->fixed_price,
            'type' => $resource->pricing_type,
        ];

        if ($before != $after) {
            $logger->log(
                ActivityLogger::PRICE_CHANGED,
                "Pricing for \"{$resource->name}\" changed to ".$resource->priceLabel().'.',
                $resource,
                ['from' => $before, 'to' => $after]
            );
        }

        return redirect()->route('admin.activities.index')->with('success', 'Gaming activity updated.');
    }

    /** Quick activate / deactivate from the activity list. */
    public function toggleActive(Resource $resource, ActivityLogger $logger)
    {
        $resource->update(['status' => $resource->status === 'active' ? 'inactive' : 'active']);

        $logger->log(
            ActivityLogger::ACTIVITY_UPDATED,
            "Activity \"{$resource->name}\" ".($resource->status === 'active' ? 'activated' : 'deactivated').'.',
            $resource,
            ['status' => $resource->status]
        );

        return back()->with(
            'success',
            $resource->status === 'active'
                ? 'Activity activated — it is live on the website again.'
                : 'Activity deactivated — it is hidden from the website.'
        );
    }

    public function destroy(Resource $resource, ActivityLogger $logger)
    {
        $name = $resource->name;

        if ($resource->bookings()->exists()) {
            $resource->update(['status' => 'inactive']);

            $logger->log(ActivityLogger::ACTIVITY_UPDATED, "Activity \"{$name}\" deactivated (has bookings).", $resource);

            return back()->with('warning', 'Activity has bookings — marked inactive instead of deleting.');
        }

        $this->deleteImage($resource->image_path);
        $resource->delete();

        $logger->log(ActivityLogger::ACTIVITY_DELETED, "Gaming activity \"{$name}\" deleted.", null, ['activity' => $name]);

        return back()->with('success', 'Gaming activity deleted.');
    }

    protected function validated(Request $request): array
    {
        $data = $request->validate([
            'business_id' => ['required', 'exists:businesses,id'],
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string', 'max:2000'],
            'capacity' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'pricing_type' => ['nullable', Rule::in(array_keys(Resource::PRICING_TYPES))],
            'price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'hourly_rate' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'promo_label' => ['nullable', 'string', 'max:60'],
            'fixed_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'package_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'min_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'max_minutes' => ['nullable', 'integer', 'min:15', 'max:1440'],
            'addon_label' => ['nullable', 'string', 'max:80'],
            'addon_price' => ['nullable', 'numeric', 'min:0', 'max:1000000'],
            'status' => ['required', 'in:active,inactive,maintenance'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'image_library' => ['nullable', 'string', 'max:255'],
            'image_source' => ['nullable', 'string', 'max:1000'],
        ]);

        $type = $data['pricing_type'] ?? Resource::PRICING_HOURLY;
        if (! array_key_exists($type, Resource::PRICING_TYPES)) {
            $type = Resource::PRICING_HOURLY;
        }

        // The admin form submits a single "price" in the selected unit; the
        // legacy hourly_rate field is still accepted for API/back-compat.
        $hasPrice = $request->filled('price');
        $hasHourly = $request->filled('hourly_rate');
        $hasFixed = $request->filled('fixed_price');

        $price = $hasPrice ? (float) $data['price'] : null;
        $regularHourly = $hasHourly ? (float) $data['hourly_rate'] : null;
        $fixed = $hasFixed ? (float) $data['fixed_price'] : null;

        $sale = $request->filled('sale_price') ? (float) $data['sale_price'] : null;

        if ($type === Resource::PRICING_FIXED) {
            if ($fixed === null) {
                $fixed = $price;
            }

            if ($fixed === null) {
                throw ValidationException::withMessages(['price' => 'Enter the fixed package price.']);
            }

            $sale = $sale; // same unit: a discounted package price
            $regularHourly = $regularHourly ?? 0.0;
            $regularForCompare = $fixed;
        } else {
            if ($regularHourly === null) {
                $regularHourly = $price;
            }

            if ($regularHourly === null) {
                throw ValidationException::withMessages(['price' => 'Enter the rate for this activity.']);
            }

            // Per-minute rates are stored as their hourly equivalent so the
            // pricing engine and every report stay in one unit.
            if ($type === Resource::PRICING_PER_MINUTE && $hasPrice) {
                $regularHourly *= 60;
                if ($sale !== null) {
                    $sale *= 60;
                }
            }

            $regularForCompare = $regularHourly;
            $fixed = null;
        }

        if ($sale !== null && $sale >= $regularForCompare) {
            throw ValidationException::withMessages([
                'sale_price' => 'The sale price must be lower than the regular price.',
            ]);
        }

        $min = isset($data['min_minutes']) ? (int) $data['min_minutes'] : null;
        $max = isset($data['max_minutes']) ? (int) $data['max_minutes'] : null;

        if ($min && $max && $max < $min) {
            throw ValidationException::withMessages([
                'max_minutes' => 'The maximum duration cannot be shorter than the minimum.',
            ]);
        }

        return [
            'business_id' => $data['business_id'],
            'category_id' => $data['category_id'],
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'description' => $data['description'] ?? null,
            'capacity' => $data['capacity'],
            'duration_minutes' => $data['duration_minutes'],
            'pricing_type' => $type,
            'hourly_rate' => round((float) $regularHourly, 2),
            'sale_price' => $sale !== null ? round($sale, 2) : null,
            'promo_label' => $request->filled('promo_label') ? $data['promo_label'] : null,
            'fixed_price' => $fixed !== null ? round($fixed, 2) : null,
            'package_minutes' => $type === Resource::PRICING_FIXED && ! empty($data['package_minutes'])
                ? (int) $data['package_minutes']
                : null,
            'min_minutes' => $min,
            'max_minutes' => $max,
            'addon_label' => $request->filled('addon_label') ? $data['addon_label'] : null,
            'addon_price' => $request->filled('addon_price') ? round((float) $data['addon_price'], 2) : null,
            'status' => $data['status'],
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_online_bookable' => $request->boolean('is_online_bookable', true),
            'featured' => $request->boolean('featured'),
        ];
    }

    /** True when the form submitted any image choice (upload/library/search). */
    protected function hasImageSource(Request $request): bool
    {
        return $request->hasFile('image')
            || $request->filled('image_library')
            || $request->filled('image_source');
    }

    /**
     * Resolve the submitted cover image: a manual upload wins, then a gallery
     * pick, then a downloaded search result. Returns the stored path or null.
     */
    protected function resolveImage(Request $request, ImageSearchService $images): ?string
    {
        if ($request->hasFile('image')) {
            return $request->file('image')->store('activities', 'public');
        }

        if ($library = $request->input('image_library')) {
            return $images->storeFromLibrary($library);
        }

        if ($source = $request->input('image_source')) {
            return $images->storeFromUrl($source);
        }

        return null;
    }

    protected function deleteImage(?string $path): void
    {
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
