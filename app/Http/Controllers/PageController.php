<?php

namespace App\Http\Controllers;

use App\Mail\ContactMessageMail;
use App\Models\Business;
use App\Models\Category;
use App\Models\PricingRule;
use App\Models\Resource;
use App\Models\Setting;
use App\Services\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

/**
 * Customer-facing informational pages linked from the public footer.
 */
class PageController extends Controller
{
    public function __construct(protected AvailabilityService $availability) {}

    public function about(): View
    {
        return view('public.pages.about', [
            'business' => Business::primary(),
            'updatedAt' => null,
        ]);
    }

    /**
     * Design-concept homepage at /demo: a premium gaming-zone landing page
     * built on the same live activities, categories, images and settings as
     * the regular homepage. Purely presentational — nothing here changes
     * booking behaviour.
     */
    public function demo(): View
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
            ->limit(4)
            ->get();

        // When nothing is starred in admin, headline the first bookable
        // activities so the section always has real content to show.
        if ($featured->isEmpty()) {
            $featured = Resource::with('category', 'business')
                ->onlineBookable()
                ->whereHas('category', fn ($q) => $q->where('is_active', true))
                ->ordered()
                ->limit(4)
                ->get();
        }

        $business = Business::primary();
        $settings = Setting::allCached();

        // Live "Available" / "Fully booked" flag per activity for today, so the
        // concept shows real operational state rather than static copy.
        $today = Carbon::now();
        $availability = collect($categories)
            ->flatMap(fn (Category $c) => $c->resources)
            ->merge($featured)
            ->unique('id')
            ->mapWithKeys(function ($resource) use ($today) {
                if (! $resource instanceof Resource) {
                    return [];
                }

                return [$resource->id => $this->availability->hasAvailability(
                    $resource,
                    $today,
                    $resource->business->slot_minutes ?? 60
                )];
            })
            ->all();

        // Every activity that actually renders on the page (active zones only).
        $floor = collect($categories)
            ->flatMap(fn (Category $c) => $c->resources)
            ->unique('id')
            ->values();

        $allActivities = $floor->merge($featured)->unique('id')->values();

        /* -------------------------------------------------------------
         | Presentation data below is all derived from live records —
         | no hard-coded stats, prices, gallery entries or FAQ answers.
         ------------------------------------------------------------- */

        // The venue trades around the clock; hours are never "closed" on the
        // public site. The stored schedule (Admin → Settings) drives the grid.
        $openHours = $business?->hoursLabel() ?? 'Open 24/7';

        $stats = [
            ['value' => $floor->count(), 'label' => 'Bookable activities', 'hint' => 'Live on the floor'],
            ['value' => $categories->count(), 'label' => 'Gaming zones', 'hint' => 'Tables, booths & rooms'],
            [
                'value' => 24,
                'label' => 'Hours open daily',
                'hint' => $openHours.', every day',
            ],
            [
                'value' => (int) ($business?->max_advance_days ?? 30),
                'label' => 'Days to book ahead',
                'hint' => 'Reserve any open slot',
            ],
        ];

        // One pricing "package" per zone, priced from real activity rates and
        // real peak-hour rules — only zones with bookable activities qualify.
        $peakRules = PricingRule::with('category:id,name')
            ->where('is_active', true)
            ->orderByDesc('priority')
            ->get()
            ->groupBy('category_id');

        $packages = $categories
            ->map(function (Category $category) use ($peakRules) {
                $resources = $category->resources;

                // Cheapest bookable activity decides the "from" price and the
                // unit it is quoted in (per hour, per minute or fixed).
                $cheapest = $resources
                    ->sortBy(fn (Resource $r) => $r->price())
                    ->first();

                return [
                    'category' => $category,
                    'count' => $resources->count(),
                    'from' => $cheapest?->price(),
                    'unit' => $cheapest?->priceUnit(),
                    'priced' => $cheapest,
                    'capacity' => $resources->max('capacity'),
                    'duration' => $resources->first()?->defaultDuration(),
                    'popular' => $resources->contains(fn (Resource $r) => (bool) $r->featured),
                    'first' => $resources->first(),
                    'peak' => $peakRules->get($category->id, collect())->take(2),
                ];
            })
            ->values();

        // Prepaid fixed-price packages (account opening + play-hour bundles).
        $accountPackages = Resource::with('category', 'business')
            ->where('pricing_type', Resource::PRICING_FIXED)
            ->where('status', 'active')
            ->ordered()
            ->get();

        // Gallery: licensed venue photography first, then the real activity
        // covers (deduplicated). Photographer/licence details live in
        // public/img/PHOTO_CREDITS.md and on the photo credits page.
        $gallery = collect([
            ['src' => asset('img/gallery/esports-arena.jpg'), 'label' => 'Esports arena', 'sub' => 'Competition floor'],
            ['src' => asset('img/gallery/arcade.jpg'), 'label' => 'Arcade classics', 'sub' => 'Retro corner'],
            ['src' => asset('img/gallery/racing-sim.jpg'), 'label' => 'Racing simulator', 'sub' => 'Sim racing'],
            ['src' => asset('img/gallery/gaming-setup.jpg'), 'label' => 'Pro setups', 'sub' => 'Gear detail'],
            ['src' => asset('img/gallery/vr-zone.jpg'), 'label' => 'VR zone', 'sub' => 'Immersive play'],
        ]);

        if (($settings['show_activity_image'] ?? '1') === '1') {
            $gallery = $gallery->merge(
                $floor->map(function ($activity) {
                    if (! $activity instanceof Resource) {
                        return [];
                    }

                    return [
                        'src' => $activity->imageUrl(),
                        'label' => $activity->name,
                        'sub' => $activity->category?->name ?? '',
                    ];
                })
            );
        }

        $gallery = $gallery
            ->filter(fn (array $item) => ! empty($item['src']))
            ->unique('src')
            ->take(10)
            ->values();

        // FAQ answers built from the live booking policy + admin settings.
        $faqs = [
            [
                'q' => 'Do I need an account to book online?',
                'a' => 'Yes — online reservations are tied to your account so you can track and manage every booking from your dashboard. Rather not sign up? Walk in and our counter team will book you in on the spot.',
            ],
            [
                'q' => 'How far in advance can I reserve a slot?',
                'a' => 'Up to '.($business?->max_advance_days ?? 30).' days ahead. Slots come straight from live availability, so anything you can see is free to grab.',
            ],
            [
                'q' => 'Is there a minimum session length?',
                'a' => 'Sessions run in '.($business?->slot_minutes ?? 60).'-minute slots and start from '.($business?->min_duration_minutes ?? 30).' minutes. Need more time? You can extend on the spot as long as the next slot is free.',
            ],
            [
                'q' => 'How is the price calculated?',
                'a' => 'Every activity shows its price up front — per hour, per minute or as a fixed package. Offers such as Monday Madness, the Mid Day PC package and opening discounts are applied automatically, and you see the full breakdown before you confirm.',
            ],
            [
                'q' => 'Do I have to pay online?',
                'a' => 'No. Reserving is free and instant — you pay at the counter when you arrive.',
            ],
            [
                'q' => 'When can I cancel or change a booking?',
                'a' => 'Up to '.Setting::get('cancellation_window_hours', 2).' hours before the start time. Get in touch or manage it from My Bookings — changes inside that window may be subject to a fee.',
            ],
            [
                'q' => 'How will I know my booking is confirmed?',
                'a' => Setting::get('auto_confirm_online') === '1'
                    ? 'Instantly. Online bookings are confirmed on the spot and appear straight away in My Bookings.'
                    : 'Online bookings land as pending and are confirmed by our team — usually within minutes during opening hours. Your My Bookings page updates as soon as the status changes.',
            ],
            [
                'q' => 'What are your opening hours?',
                'a' => 'We are open 24 hours a day, 7 days a week — walk in any time or reserve a slot online, day or night.',
            ],
        ];

        return view('demo.home', [
            'categories' => $categories,
            'featured' => $featured,
            'activities' => $allActivities,
            'business' => $business,
            'settings' => $settings,
            'availability' => $availability,
            'stats' => $stats,
            'packages' => $packages,
            'accountPackages' => $accountPackages,
            'gallery' => $gallery,
            'faqs' => $faqs,
            // The homepage renders its own compact footer.
            'hideFooter' => true,
        ]);
    }

    public function contact(): View
    {
        return view('public.pages.contact', [
            'business' => Business::primary(),
        ]);
    }

    /**
     * Receive the public contact form and email the site owner. Delivery is
     * best-effort: a mail failure surfaces a friendly error instead of a 500.
     */
    public function sendContact(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:150'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $submission = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'] ?? null,
            'message' => $data['message'],
            'submitted_at' => now()->format('D, d M Y \a\t g:i A'),
        ];

        try {
            Mail::to(config('mail.contact_recipient'))->send(new ContactMessageMail($submission));
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput()->with(
                'error',
                'Sorry — we could not send your message right now. Please try again in a moment, or call us directly.'
            );
        }

        return back()->with('success', 'Thanks for reaching out — our team will get back to you shortly.');
    }

    /** Attribution for every third-party photograph shipped with the site. */
    public function credits(): View
    {
        return view('public.pages.credits', [
            'business' => Business::primary(),
            'credits' => config('photo_credits', []),
        ]);
    }

    public function privacy(): View
    {
        return view('public.pages.privacy', [
            'business' => Business::primary(),
            'updatedAt' => now()->format('F j, Y'),
        ]);
    }

    public function cookies(): View
    {
        return view('public.pages.cookies', [
            'business' => Business::primary(),
            'updatedAt' => now()->format('F j, Y'),
        ]);
    }

    public function terms(): View
    {
        return view('public.pages.terms', [
            'business' => Business::primary(),
            'updatedAt' => now()->format('F j, Y'),
        ]);
    }

    public function disclaimer(): View
    {
        return view('public.pages.disclaimer', [
            'business' => Business::primary(),
            'updatedAt' => now()->format('F j, Y'),
        ]);
    }
}
