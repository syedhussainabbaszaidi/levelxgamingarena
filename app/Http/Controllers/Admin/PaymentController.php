<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\BookingService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    public function index(Request $request): View
    {
        $query = Payment::with(['booking.resource', 'customer', 'user']);

        if ($method = $request->input('method')) {
            $query->where('method', $method);
        }
        if ($date = $request->input('date')) {
            $query->whereDate('created_at', $date);
        }

        $payments = $query->latest()->paginate(25)->withQueryString();

        $totals = [
            // Money actually collected (payments ledger) — kept in sync with
            // booking payment_status by BookingService::setPaymentStatus().
            'today' => (float) Payment::whereDate('created_at', today())->sum('amount'),
            'month' => (float) Payment::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('amount'),
            'due' => (float) Booking::where('payment_status', 'unpaid')
                ->whereIn('status', Booking::REVENUE_STATUSES)->sum('final_cost'),
        ];

        return view('admin.payments.index', [
            'payments' => $payments,
            'totals' => $totals,
            'filters' => $request->only(['method', 'date']),
        ]);
    }

    /** Record a manual payment against a booking. */
    public function store(Request $request)
    {
        $data = $request->validate([
            'booking_id' => ['required', 'exists:bookings,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'in:cash,online'],
            'kind' => ['required', 'in:advance,full,partial,extension,addon'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $booking = Booking::findOrFail($data['booking_id']);
        $this->bookingService->addPayment($booking, [
            'amount' => $data['amount'],
            'method' => $data['method'],
            'kind' => $data['kind'],
            'notes' => $data['notes'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        return back()->with('success', 'Payment recorded.');
    }
}
