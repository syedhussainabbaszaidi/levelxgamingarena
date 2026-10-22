<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

class Permission extends Model
{
    /**
     * Canonical permission catalog. The slug is the ability name used by
     * gates (`$this->authorize('bookings.view')`) and by the `permission:`
     * middleware, so this list is the single source of truth for the app.
     *
     * [slug => [display name, group]]
     */
    public const CATALOG = [
        'bookings.view' => ['View bookings', 'Bookings'],
        'bookings.create' => ['Create bookings', 'Bookings'],
        'bookings.edit' => ['Edit bookings', 'Bookings'],
        'bookings.delete' => ['Delete bookings', 'Bookings'],
        'bookings.status' => ['Change booking status', 'Bookings'],
        'activities.manage' => ['Manage gaming activities', 'Activities'],
        'customers.manage' => ['Manage customers', 'Customers'],
        'pricing.manage' => ['Manage pricing', 'Pricing'],
        'users.manage' => ['Manage users', 'Users'],
        'roles.manage' => ['Manage roles & permissions', 'Users'],
        'reports.view' => ['View reports', 'Reports'],
        'logs.view' => ['View activity logs', 'Reports'],
        'config.manage' => ['Manage booking configuration', 'System'],
        'settings.manage' => ['Manage system settings', 'System'],
    ];

    protected $fillable = ['name', 'slug', 'group', 'description'];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    /** All permission slugs, grouped for the roles editor UI. */
    public static function grouped(): Collection
    {
        return static::query()
            ->orderBy('group')
            ->orderBy('name')
            ->get()
            ->groupBy('group');
    }

    public static function slugs(): array
    {
        return array_keys(static::CATALOG);
    }
}
