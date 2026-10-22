<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Business;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function __construct(protected ActivityLogger $logger) {}

    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => 'customer',
            'is_active' => true,
        ]);

        // Pre-create the customer profile so their booking history is linked.
        if (! empty($data['phone']) && $business = Business::orderBy('id')->first()) {
            Customer::updateOrCreate(
                ['business_id' => $business->id, 'phone' => $data['phone']],
                ['user_id' => $user->id, 'name' => $user->name, 'email' => $user->email]
            );
        }

        event(new Registered($user));

        Auth::login($user);

        $this->logger->auth(ActivityLogger::AUTH_REGISTERED, $user, "New customer account registered: {$user->email}.");

        return redirect(route('dashboard', absolute: false));
    }
}
