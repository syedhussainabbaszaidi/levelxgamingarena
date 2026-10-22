<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /** Whitelisted sorts — newest customers first by default. */
    public const SORTS = ['newest', 'name', 'spent', 'visits'];

    public function index(Request $request): View
    {
        $search = $request->input('q');
        $sort = in_array($request->input('sort'), self::SORTS, true) ? $request->input('sort') : 'newest';

        $query = Customer::query()->withCount('bookings');

        if ($search) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }

        match ($sort) {
            'name' => $query->orderBy('name'),
            'spent' => $query->orderByDesc('total_spent'),
            'visits' => $query->orderByDesc('total_visits'),
            default => $query->orderByDesc('created_at'),
        };

        $customers = $query->paginate(20)->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'filters' => ['q' => $search, 'sort' => $sort],
        ]);
    }

    public function create(): View
    {
        return view('admin.customers.create');
    }

    public function store(Request $request)
    {
        Customer::create($this->validated($request));

        return redirect()->route('admin.customers.index')->with('success', 'Customer created.');
    }

    public function show(Customer $customer): View
    {
        $customer->loadCount('bookings');
        $bookings = $customer->bookings()->with('resource')
            ->latest('starts_at')->paginate(10);
        $payments = $customer->payments()->with('booking')
            ->latest()->paginate(10);

        return view('admin.customers.show', compact('customer', 'bookings', 'payments'));
    }

    public function edit(Customer $customer): View
    {
        return view('admin.customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $customer->update($this->validated($request, $customer->id));

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated.');
    }

    public function destroy(Customer $customer)
    {
        if ($customer->bookings()->exists()) {
            return back()->with('error', 'Customer has bookings and cannot be deleted.');
        }

        $customer->delete();

        return back()->with('success', 'Customer deleted.');
    }

    protected function validated(Request $request, ?int $ignoreId = null): array
    {
        $phoneRule = ['required', 'string', 'max:30'];

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => $phoneRule,
            'email' => ['nullable', 'email', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        $data['business_id'] = \App\Models\Business::primary()?->id;

        return $data;
    }
}
