<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Customer dashboard: profile summary, booking stats and recent bookings.
 */
class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $customer = Customer::where('user_id', $user->id)->first();

        $query = Booking::with(['resource.category'])->forUser($user);

        return view('customer.dashboard', [
            'user' => $user,
            'customer' => $customer,
            'stats' => [
                'total' => (clone $query)->count(),
                'upcoming' => (clone $query)->whereIn('status', ['pending', 'confirmed'])->where('starts_at', '>=', now())->count(),
                'completed' => (clone $query)->where('status', 'completed')->count(),
                'spent' => (float) (clone $query)->where('status', 'completed')->sum('final_cost'),
            ],
            'bookings' => (clone $query)->latest('starts_at')->limit(5)->get(),
        ]);
    }
}
