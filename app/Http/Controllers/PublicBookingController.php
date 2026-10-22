<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\BookingField;
use App\Models\Business;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\Setting;
use App\Services\AvailabilityService;
use App\Services\BookingNotifier;
use App\Services\BookingService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Customer-facing booking portal: browse activities, pick a slot, see the price
 * breakdown and confirm. Creating a booking requires an authenticated account.
 */
class PublicBookingController extends Controller
{
    public function __construct(
        protected AvailabilityService $availability,
        protected PricingService $pricing,
        protected BookingService $bookings,
        protected BookingNotifier $notifier,
    ) {}

    /** Landing page: featured activities grouped by category. */
    public function index(): View
    {
        $categories = Category::with(['resources' => fn ($q) => $q->onlineBookable()->ordered()->with('business')])
            ->where('is_active', true)
            ->orderBy('name')
            ->get()
            ->filter(fn (Category $c) => $c->resources->isNotEmpty())
            ->values();

        $featured = Resource::with('category', 'business')
            ->onlineBookable()
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->where('featured', true)
            ->ordered()
            ->limit(3)
            ->get();

        // Live "Available" / "Fully booked" flag per activity for today, derived
        // from real bookings so the homepage badges never go stale.
        $today = Carbon::now();
        $availability = collect($categories)
            ->flatMap(fn (Category $c) => $c->resources)
            ->merge($featured)
            ->unique('id')
            ->mapWithKeys(function (Resource $resource) use ($today) {
                return [$resource->id => $this->availability->hasAvailability(
                    $resource,
                    $today,
                    $resource->business->slot_minutes ?? 60
                )];
            })
            ->all();

        return view('public.index', [
            'categories' => $categories,
            'business' => Business::orderBy('id')->first(),
            'settings' => Setting::allCached(),
            'featured' => $featured,
            'availability' => $availability,
        ]);
    }

    /**
     * Activity detail + booking planner. Strictly inactive/maintenance
     * activities 404; an active activity that is counter-only still gets a
     * detail page so customers can see what it costs and how to book it.
     */
    public function show(Resource $resource): View
    {
        abort_unless($resource->isBookable(), 404);

        $resource->load('category', 'business');

        return view('public.activity', [
            'resource' => $resource,
            'fields' => $resource->isOnlineBookable() ? $this->fieldsFor($resource) : collect(),
            'business' => $resource->business,
            'settings' => Setting::allCached(),
            'policy' => $this->policy($resource),
            'promotions' => $this->promotionsFor($resource),
            'date' => now()->format('Y-m-d'),
        ]);
    }

    /** AJAX: bookable slots for a resource on a date. */
    public function slots(Request $request)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $resource = Resource::with('business')->findOrFail($data['resource_id']);
        abort_unless($resource->isOnlineBookable(), 404);

        $result = $this->availability->configuredSlots(
            $resource,
            Carbon::parse($data['date']),
            $resource->business->slot_minutes ?? 60
        );

        return response()->json($result);
    }

    /** AJAX: price breakdown for a proposed booking. */
    public function price(Request $request)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'starts_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
        ]);

        $resource = Resource::with('business')->findOrFail($data['resource_id']);
        $startsAt = Carbon::parse(str_replace('T', ' ', $data['starts_at']))->startOfMinute();
        $duration = (int) $data['duration_minutes'];
        $endsAt = $startsAt->copy()->addMinutes($duration);

        // Minimum set by the activity and by the rate window in force (e.g. the
        // 3-hour Monday PC special) — enforced in the engine as well.
        $minMinutes = $this->pricing->minimumMinutesFor($resource, $startsAt);
        $maxMinutes = $resource->maximumMinutes();

        $check = $this->availability->validate($resource, $startsAt, $endsAt);
        $message = $check['message'];

        if ($duration < $minMinutes) {
            $check['ok'] = false;
            $message = 'The minimum booking for this activity is '.$this->humanMinutes($minMinutes).'.';
        } elseif ($maxMinutes && $duration > $maxMinutes) {
            $check['ok'] = false;
            $message = 'The maximum booking for this activity is '.$this->humanMinutes($maxMinutes).'.';
        }

        return response()->json([
            'ok' => $check['ok'],
            'message' => $message,
            'min_minutes' => $minMinutes,
            'max_minutes' => $maxMinutes,
            'price' => $this->pricing->calculate($resource, $startsAt, $endsAt),
            'window' => [
                'starts_at' => $startsAt->format('D, d M Y — g:i A'),
                'ends_at' => $endsAt->format('g:i A'),
            ],
        ]);
    }

    /** Store an online booking for the signed-in customer. */
    public function store(Request $request)
    {
        $user = $request->user();

        $resource = Resource::with('business')->findOrFail($request->input('resource_id'));
        $fields = $this->fieldsFor($resource);
        $policy = $this->policy($resource);

        abort_unless($resource->isOnlineBookable(), 404);

        $rules = [
            'resource_id' => ['required', 'exists:resources,id'],
            'date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today', 'before_or_equal:'.now()->addDays($policy['max_advance_days'])->format('Y-m-d')],
            'time' => ['required', 'date_format:H:i'],
            'duration_minutes' => ['required', 'integer', 'min:'.$policy['min_duration'], 'max:'.$policy['max_duration']],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];

        foreach ($fields as $field) {
            $rules["fields.{$field->id}"] = $this->rulesForField($field);
        }

        $attributes = [
            'resource_id' => 'activity',
            'date' => 'date',
            'time' => 'time slot',
            'duration_minutes' => 'duration',
            'notes' => 'notes',
        ] + $fields->mapWithKeys(fn ($f) => ["fields.{$f->id}" => strtolower($f->label)])->all();

        $data = $request->validate($rules, [], $attributes);

        $startsAt = Carbon::parse($data['date'].' '.$data['time'].':00')->startOfMinute();

        // Derive the customer identity from the dynamic field answers.
        $answers = collect($request->input('fields', []));
        $nameKey = $fields->firstWhere('key', 'player_name') ?? $fields->firstWhere('key', 'name');
        $phone = $this->answerFor($fields, $answers, 'phone');
        $email = $this->answerFor($fields, $answers, 'email');
        $name = $nameKey ? ($answers[$nameKey->id] ?? null) : null;

        $autoConfirm = Setting::get('auto_confirm_online') === '1';

        $booking = $this->bookings->create([
            'resource_id' => $resource->id,
            'starts_at' => $startsAt->format('Y-m-d H:i'),
            'duration_minutes' => (int) $data['duration_minutes'],
            'source' => 'online',
            'status' => $autoConfirm ? 'confirmed' : 'pending',
            'user_id' => $user->id,
            'notes' => $data['notes'] ?? null,
            'customer' => [
                'user_id' => $user->id,
                'name' => $name ?: $user->name,
                'phone' => $phone ?: ('user-'.$user->id),
                'email' => $email ?: $user->email,
            ],
            'field_values' => $request->input('fields', []),
        ]);

        // Alert the back office (email + unread sidebar badge). Best-effort:
        // mail problems never block a confirmed booking.
        $this->notifier->notifyNewBooking($booking);

        return redirect()
            ->route('public.confirmation', $booking->code)
            ->with('success', 'Booking confirmed — here is your full breakdown.');
    }

    /** Booking detail/confirmation page. Only the owner (or back office) may view it. */
    public function confirmation(string $code): View
    {
        $booking = Booking::with(['resource.category', 'resource.business', 'customer', 'payments', 'fieldValues.field'])
            ->where('code', $code)
            ->firstOrFail();

        $user = auth()->user();
        abort_unless(
            $user && ($user->isStaffOrAdmin() || $user->id === $booking->user_id || $booking->customer?->user_id === $user->id),
            403
        );

        return view('public.confirmation', [
            'booking' => $booking,
            'breakdown' => $this->pricing->calculate(
                $booking->resource,
                $booking->starts_at,
                $booking->ends_at
            ),
        ]);
    }

    /* ------------------------------------------------------------------ */

    /** Visible, admin-configured booking fields for an activity. */
    protected function fieldsFor(Resource $resource)
    {
        return BookingField::query()
            ->visible()
            ->forResource($resource->id)
            ->orderBy('sort_order')
            ->get();
    }

    /** Booking policy values (all admin-configurable). */
    protected function policy(Resource $resource): array
    {
        $businessMin = (int) ($resource->business->min_duration_minutes ?? 30);
        $min = max($businessMin, $resource->minimumMinutes());
        $max = $resource->maximumMinutes() ?: 1440;

        if ($resource->isFixed() && $resource->package_minutes) {
            $min = $max = (int) $resource->package_minutes;
        }

        $default = $resource->defaultDuration();
        if ($default < $min) {
            $default = $min;
        }
        if ($default > $max) {
            $default = $max;
        }

        return [
            'min_duration' => $min,
            'max_duration' => $max,
            'slot_minutes' => (int) ($resource->business->slot_minutes ?? 60),
            'max_advance_days' => (int) ($resource->business->max_advance_days ?? 30),
            'default_duration' => $default,
            'fixed' => $resource->isFixed(),
        ];
    }

    /** Active rate windows that can change this activity's price. */
    protected function promotionsFor(Resource $resource)
    {
        return PricingRule::query()
            ->forResource($resource->id)
            ->orderByDesc('priority')
            ->get();
    }

    /** "180" → "3 hours". */
    protected function humanMinutes(int $minutes): string
    {
        if ($minutes % 60 === 0) {
            $hours = (int) ($minutes / 60);

            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return $minutes.' minutes';
    }

    protected function answerFor($fields, $answers, string $key): ?string
    {
        $field = $fields->firstWhere('key', $key);

        return $field ? ($answers[$field->id] ?? null) : null;
    }

    /** Validation rules derived from a configured booking field. */
    protected function rulesForField(BookingField $field): array
    {
        $rules = [$field->is_required ? 'required' : 'nullable'];

        return match ($field->type) {
            'number' => [...$rules, 'numeric'],
            'email' => [...$rules, 'email', 'max:255'],
            'tel' => [...$rules, 'string', 'max:30'],
            'date' => [...$rules, 'date'],
            'time' => [...$rules, 'date_format:H:i'],
            'select' => $field->choiceOptions()
                ? [...$rules, Rule::in($field->choiceOptions())]
                : [...$rules, 'string', 'max:255'],
            'textarea' => [...$rules, 'string', 'max:1000'],
            default => [...$rules, 'string', 'max:255'],
        };
    }
}
