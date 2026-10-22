<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Resource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = now()->today();
        $user = $request->user();

        $stats = [
            'active_sessions' => Booking::where('status', 'confirmed')
                ->whereNotNull('checked_in_at')->whereNull('ended_at')->count(),
            'today_bookings' => Booking::whereDate('starts_at', $today)->count(),
            // Revenue = money actually marked paid on bookings (payment_status flag).
            'today_revenue' => (float) Booking::where('payment_status', 'paid')
                ->whereIn('status', Booking::REVENUE_STATUSES)
                ->whereDate('paid_at', $today)->sum('final_cost'),
            'week_revenue' => (float) Booking::where('payment_status', 'paid')
                ->whereIn('status', Booking::REVENUE_STATUSES)
                ->where('paid_at', '>=', now()->subDays(7))->sum('final_cost'),
            'due_amount' => (float) Booking::where('payment_status', 'unpaid')
                ->whereIn('status', Booking::REVENUE_STATUSES)->sum('final_cost'),
            'pending' => Booking::where('status', 'pending')->count(),
            'occupancy' => $this->occupancyNow(),
            'upcoming' => Booking::with(['resource', 'customer'])
                ->whereIn('status', ['confirmed', 'pending'])
                ->where('starts_at', '>=', now())
                ->orderBy('starts_at')
                ->limit(6)
                ->get(),
        ];

        // 7-day booking trend for the mini chart.
        $trend = Booking::query()
            ->selectRaw('DATE(starts_at) as day, COUNT(*) as total')
            ->where('starts_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $trendDays = collect(range(6, 0))->mapWithKeys(function ($i) use ($trend) {
            $date = now()->subDays($i)->format('Y-m-d');

            return [$date => (int) ($trend[$date] ?? 0)];
        });

        $topActivities = Booking::query()
            ->select('resource_id', DB::raw('COUNT(*) as bookings'), DB::raw('SUM(final_cost) as revenue'))
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotIn('status', ['cancelled'])
            ->groupBy('resource_id')
            ->orderByDesc('bookings')
            ->with('resource:id,name')
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'trend' => $trendDays,
            'topActivities' => $topActivities,
            'recentLogs' => $user->hasPermissionTo('logs.view')
                ? ActivityLog::with('user')->latest()->limit(6)->get()
                : collect(),
        ]);
    }

    protected function occupancyNow(): float
    {
        $bookable = Resource::where('status', 'active')->count();
        if ($bookable === 0) {
            return 0.0;
        }

        $busy = Booking::where('status', 'confirmed')
            ->whereNotNull('checked_in_at')->whereNull('ended_at')
            ->distinct('resource_id')->count('resource_id');

        return round($busy / $bookable * 100, 1);
    }
}
