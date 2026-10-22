<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Services\BookingService;
use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function __construct(protected BookingService $bookingService) {}

    /** AJAX: check in. */
    public function checkIn(Request $request, Booking $booking)
    {
        try {
            $this->bookingService->checkIn($booking);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Session started.', 'status' => $booking->status]);
        }

        return back()->with('success', 'Session started.');
    }

    /** AJAX: extend live session. */
    public function extend(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'minutes' => ['required', 'integer', 'min:15', 'max:600'],
        ]);

        try {
            $result = $this->bookingService->extend($booking, (int) $data['minutes'], $request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Extended by '.$data['minutes'].' minutes. Extra: '.$result['extra_cost'],
                'new_end' => $booking->ends_at->format('g:i A'),
                'final_cost' => $booking->final_cost,
            ]);
        }

        return back()->with('success', 'Session extended.');
    }

    /** End session + settle payment. */
    public function end(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'method' => ['nullable', 'in:cash,online'],
            'charge' => ['nullable', 'boolean'],
        ]);

        try {
            $this->bookingService->endSession($booking, [
                'method' => $data['method'] ?? 'cash',
                'charge' => $data['charge'] ?? true,
                'user_id' => $request->user()->id,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Session completed. Payment settled.']);
        }

        return redirect()
            ->route('admin.bookings.show', $booking)
            ->with('success', 'Session completed and payment settled.');
    }

    /** AJAX: cancel. */
    public function cancel(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        try {
            $this->bookingService->cancel($booking, $data['reason'] ?? '', $request->user());
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()], 422);
        }

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'message' => 'Booking cancelled.']);
        }

        return back()->with('success', 'Booking cancelled.');
    }
}
