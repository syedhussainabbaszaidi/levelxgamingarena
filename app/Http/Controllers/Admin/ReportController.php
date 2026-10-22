<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Resource;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function index(): View
    {
        $from = now()->startOfMonth();
        $to = now()->endOfDay();

        $revenueByDay = Payment::selectRaw('DATE(created_at) as day, SUM(amount) as total')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $revenueByMethod = Payment::selectRaw('method, SUM(amount) as total, COUNT(*) as count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('method')
            ->get();

        $bookingsByStatus = Booking::selectRaw('status, COUNT(*) as count')
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('status')
            ->pluck('count', 'status');

        $topResources = Resource::selectRaw('resources.name, COUNT(bookings.id) as bookings_count, COALESCE(SUM(bookings.final_cost),0) as revenue')
            ->leftJoin('bookings', 'bookings.resource_id', '=', 'resources.id')
            ->where('bookings.status', 'completed')
            ->groupBy('resources.id', 'resources.name')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $topCustomers = \App\Models\Customer::selectRaw('customers.name, customers.phone, COUNT(bookings.id) as bookings_count, COALESCE(SUM(bookings.final_cost),0) as revenue')
            ->leftJoin('bookings', 'bookings.customer_id', '=', 'customers.id')
            ->where('bookings.status', 'completed')
            ->groupBy('customers.id', 'customers.name', 'customers.phone')
            ->orderByDesc('revenue')
            ->limit(10)
            ->get();

        $totals = [
            'month_revenue' => (float) Payment::whereBetween('created_at', [$from, $to])->sum('amount'),
            'month_bookings' => Booking::whereBetween('created_at', [$from, $to])->count(),
            'completed' => (int) ($bookingsByStatus['completed'] ?? 0),
            'cancelled' => (int) ($bookingsByStatus['cancelled'] ?? 0),
        ];

        return view('admin.reports.index', [
            'revenueByDay' => $revenueByDay,
            'revenueByMethod' => $revenueByMethod,
            'bookingsByStatus' => $bookingsByStatus,
            'topResources' => $topResources,
            'topCustomers' => $topCustomers,
            'totals' => $totals,
        ]);
    }
}
