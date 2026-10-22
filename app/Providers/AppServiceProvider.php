<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Permission;
use App\Models\Resource;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Dark/neon pagination everywhere (admin tables, customer pages).
        Paginator::defaultView('vendor.pagination.neon');
        Paginator::defaultSimpleView('vendor.pagination.neon');

        // Stable morph aliases so activity-log subject references survive renames.
        Relation::enforceMorphMap([
            'booking' => Booking::class,
            'resource' => Resource::class,
            'customer' => Customer::class,
            'payment' => Payment::class,
            'user' => User::class,
            'role' => Role::class,
        ]);

        // Super admins bypass every permission check.
        Gate::before(fn (?User $user) => ($user?->isSuperAdmin()) ? true : null);

        // One gate per permission slug, so controllers can $this->authorize('bookings.view').
        foreach (Permission::slugs() as $slug) {
            Gate::define($slug, fn (User $user) => $user->hasPermissionTo($slug));
        }
    }
}
