<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Resource;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\PricingService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BookingController extends Controller
{
    /** Whitelisted sort columns — never interpolate user input into SQL. */
    public const SORTABLE = ['code', 'starts_at', 'ends_at', 'status', 'final_cost', 'created_at'];
    /** Payment methods offered in the admin UI (Booking Method = Cash | Online). */
    public const METHOD_OPTIONS = ['cash', 'online'];

    public function __construct(
        protected BookingService $bookingService,
        protected PricingService $pricingService,
    ) {}

    public function index(Request $request): View
    {
        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'created_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $bookings = $this->filtered($request)->orderBy($sort, $dir)->paginate(25)->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'unreadCount' => Booking::unreadForAdmin()->count(),
            'counts' => [
                'pending' => Booking::where('status', 'pending')->count(),
                'confirmed' => Booking::where('status', 'confirmed')->count(),
                'completed' => Booking::where('status', 'completed')->count(),
                'cancelled' => Booking::where('status', 'cancelled')->count(),
            ],
            'totals' => $this->paymentTotals(),
            'resources' => Resource::orderBy('name')->get(['id', 'name']),
            'statuses' => Booking::STATUSES,
            'filters' => $request->only(['status', 'q', 'date', 'resource', 'from', 'to', 'sort', 'dir'])
                + ['status' => null, 'q' => null, 'date' => null, 'resource' => null, 'from' => null, 'to' => null, 'sort' => $sort, 'dir' => $dir],
        ]);
    }

    /** Revenue/payment totals shared by the page and the polling endpoint. */
    protected function paymentTotals(): array
    {
        $paid = Booking::where('payment_status', 'paid')
            ->whereIn('status', Booking::REVENUE_STATUSES);

        $unpaid = Booking::where('payment_status', 'unpaid')
            ->whereIn('status', Booking::REVENUE_STATUSES);

        return [
            'today_revenue' => (float) (clone $paid)->whereDate('paid_at', today())->sum('final_cost'),
            'total_payment' => (float) (clone $paid)->sum('final_cost'),
            'paid_amount' => (float) (clone $paid)->sum('final_cost'),
            'unpaid_amount' => (float) $unpaid->sum('final_cost'),
            'unpaid_count' => (int) $unpaid->count(),
        ];
    }

    /** JSON: live tab counts + payment totals (polled, no page reload). */
    public function counts()
    {
        $counts = Booking::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return response()->json([
            'ok' => true,
            'counts' => [
                'all' => (int) array_sum($counts->all()),
                'pending' => (int) ($counts['pending'] ?? 0),
                'confirmed' => (int) ($counts['confirmed'] ?? 0),
                'completed' => (int) ($counts['completed'] ?? 0),
                'cancelled' => (int) ($counts['cancelled'] ?? 0),
            ],
            'unread' => Booking::unreadForAdmin()->count(),
            'totals' => $this->paymentTotals(),
        ]);
    }

    /** JSON: number of new (unacknowledged) customer bookings. */
    public function unreadCount(): JsonResponse
    {
        return response()->json([
            'ok' => true,
            'unread' => Booking::unreadForAdmin()->count(),
        ]);
    }

    /** Acknowledge every new booking (clears the sidebar badge). */
    public function markAllRead(): RedirectResponse
    {
        $updated = Booking::unreadForAdmin()->update(['admin_read_at' => now()]);

        return back()->with('success', $updated > 0
            ? "Marked {$updated} new booking".($updated === 1 ? '' : 's').' as read.'
            : 'No new bookings to mark.');
    }

    /** JSON/form: toggle a booking's Paid/Unpaid status (updates totals). */
    public function updatePaymentStatus(Request $request, Booking $booking, BookingService $service, ActivityLogger $logger)
    {
        $data = $request->validate([
            'payment_status' => ['required', Rule::in(Booking::PAYMENT_STATUSES)],
            'method' => ['nullable', 'in:cash,online'],
        ]);

        try {
            $service->setPaymentStatus(
                $booking,
                $data['payment_status'],
                $data['method'] ?? 'cash',
                $request->user()
            );
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
            }

            throw $e;
        }

        $logger->log(ActivityLogger::BOOKING_UPDATED, "Payment status of {$booking->code} set to {$data['payment_status']}.", $booking);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => ucfirst($data['payment_status']).' — totals updated.',
                'payment_status' => $booking->payment_status,
                'totals' => $this->paymentTotals(),
            ]);
        }

        return back()->with('success', 'Payment marked as '.$data['payment_status'].'.');
    }

    /** Shared filter builder for the list + CSV export. */
    protected function filtered(Request $request)
    {
        return Booking::with(['resource.category', 'customer'])
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->input('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$term}%")
                ->orWhereHas('customer', fn ($c) => $c
                    ->where('name', 'like', "%{$term}%")
                    ->orWhere('phone', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"))
                ->orWhereHas('resource', fn ($r) => $r->where('name', 'like', "%{$term}%"))))
            ->when($request->input('resource'), fn ($q, $r) => $q->where('resource_id', $r))
            ->when($request->input('date'), fn ($q, $d) => $q->whereDate('starts_at', $d))
            ->when($request->input('from'), fn ($q, $d) => $q->whereDate('starts_at', '>=', $d))
            ->when($request->input('to'), fn ($q, $d) => $q->whereDate('starts_at', '<=', $d))
            // Default window keeps the list fast: last 7 days onward.
            ->when(
                ! $request->input('date') && ! $request->input('from') && ! $request->input('status') && ! $request->input('q'),
                fn ($q) => $q->whereDate('starts_at', '>=', now()->subDays(7))
            );
    }

    public function create(Request $request): View
    {
        return view('admin.bookings.create', [
            'date' => $request->input('date', now()->format('Y-m-d')),
            'resources' => Resource::with('category')->orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'customers' => Customer::orderBy('name')->limit(200)->get(),
        ]);
    }

    public function store(Request $request, ActivityLogger $logger)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:1440'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'customer_name' => ['nullable', 'string', 'max:255'],
            'customer_phone' => ['nullable', 'string', 'max:30'],
            'notes' => ['nullable', 'string'],
            'advance_amount' => ['nullable', 'numeric', 'min:0'],
            'advance_method' => ['nullable', 'in:cash,online'],
        ]);

        $booking = $this->bookingService->create([
            'resource_id' => $data['resource_id'],
            'starts_at' => str_replace('T', ' ', $data['starts_at']),
            'duration_minutes' => $data['duration_minutes'],
            'source' => 'admin',
            'user_id' => $request->user()->id,
            'notes' => $data['notes'] ?? null,
            'customer' => $this->customerInput($data),
            'advance_amount' => $data['advance_amount'] ?? null,
            'advance_method' => $data['advance_method'] ?? 'cash',
        ]);

        $logger->log(ActivityLogger::BOOKING_CREATED, "Booking {$booking->code} created by admin.", $booking);

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Booking '.$booking->code.' created.');
    }

    public function show(Booking $booking): View
    {
        $booking->load(['resource.category', 'customer', 'payments.user', 'fieldValues.field', 'user']);

        // Opening a booking acknowledges its notification (badge clears).
        $booking->markReadForAdmin();

        return view('admin.bookings.show', [
            'booking' => $booking,
            'statuses' => Booking::STATUSES,
            'paymentMethods' => ['cash', 'online'],
        ]);
    }

    /** Change a booking's status from the admin UI (audit-logged). */
    public function updateStatus(Request $request, Booking $booking, ActivityLogger $logger)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(Booking::STATUSES)],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $this->bookingService->changeStatus($booking, $data['status'], $data['note'] ?? null);

        return back()->with('success', "Booking marked as {$data['status']}.");
    }

    /** CSV export of the current filtered booking list. */
    public function export(Request $request): StreamedResponse
    {
        $sort = in_array($request->input('sort'), self::SORTABLE, true) ? $request->input('sort') : 'starts_at';
        $dir = $request->input('dir') === 'asc' ? 'asc' : 'desc';

        $query = $this->filtered($request)->orderBy($sort, $dir);
        $filename = 'bookings-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($query) {
            $out = fopen('php://output', 'w');
            fputcsv($out, [
                'Code', 'Status', 'Activity', 'Category', 'Customer', 'Phone', 'Email',
                'Starts at', 'Ends at', 'Minutes', 'Estimated', 'Final', 'Payment status', 'Created at',
            ]);

            $query->chunk(200, function ($bookings) use ($out) {
                foreach ($bookings as $b) {
                    fputcsv($out, [
                        $b->code,
                        $b->status,
                        $b->resource?->name,
                        $b->resource?->category?->name,
                        $b->customer?->name,
                        $b->customer?->phone,
                        $b->customer?->email,
                        optional($b->starts_at)->format('Y-m-d H:i'),
                        optional($b->ends_at)->format('Y-m-d H:i'),
                        $b->duration_minutes,
                        number_format((float) $b->estimated_cost, 2, '.', ''),
                        number_format((float) $b->final_cost, 2, '.', ''),
                        $b->payment_status,
                        optional($b->created_at)->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /** AJAX: live price preview. */
    public function price(Request $request)
    {
        $data = $request->validate([
            'resource_id' => ['required', 'exists:resources,id'],
            'starts_at' => ['required', 'date'],
            'duration_minutes' => ['required', 'integer', 'min:15'],
        ]);

        $resource = Resource::with('business')->findOrFail($data['resource_id']);
        $startsAt = Carbon::parse(str_replace('T', ' ', $data['starts_at']))->startOfMinute();
        $endsAt = $startsAt->copy()->addMinutes((int) $data['duration_minutes']);

        $check = app(AvailabilityService::class)->validate($resource, $startsAt, $endsAt);

        return response()->json([
            'ok' => $check['ok'],
            'message' => $check['message'],
            'price' => $this->pricingService->calculate($resource, $startsAt, $endsAt),
        ]);
    }

    protected function customerInput(array $data): ?array
    {
        if (! empty($data['customer_id'])) {
            return ['id' => $data['customer_id']];
        }
        if (! empty($data['customer_phone'])) {
            return [
                'name' => $data['customer_name'] ?? 'Walk-in Customer',
                'phone' => $data['customer_phone'],
            ];
        }

        return null;
    }
}
