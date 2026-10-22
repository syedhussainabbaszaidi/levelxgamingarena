<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer booking history. Every query is scoped to the signed-in account so
 * one customer can never read another customer's bookings.
 */
class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $bookings = Booking::with(['resource.category'])
            ->forUser($request->user())
            ->when($request->input('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest('starts_at')
            ->paginate(10)
            ->withQueryString();

        return view('customer.bookings.index', [
            'bookings' => $bookings,
            'statuses' => Booking::STATUSES,
            'filter' => $request->input('status'),
        ]);
    }

    public function show(Request $request, Booking $booking): View
    {
        $this->authorizeView($request, $booking);

        $booking->load(['resource.category', 'resource.business', 'customer', 'payments', 'fieldValues.field']);

        return view('customer.bookings.show', ['booking' => $booking]);
    }

    protected function authorizeView(Request $request, Booking $booking): void
    {
        $user = $request->user();

        abort_unless(
            $user->isStaffOrAdmin()
                || $booking->user_id === $user->id
                || $booking->customer?->user_id === $user->id,
            403
        );
    }
}
