<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Role extends Model
{
    public const SUPER_ADMIN = 'super_admin';
    public const ADMIN = 'admin';
    public const BOOKING_MANAGER = 'booking_manager';
    public const STAFF = 'staff';

    /** Built-in roles and the permissions they receive on a fresh install. */
    public const MATRIX = [
        self::SUPER_ADMIN => '*',
        self::ADMIN => '*',
        self::BOOKING_MANAGER => [
            'bookings.view', 'bookings.create', 'bookings.edit', 'bookings.status',
            'activities.manage', 'customers.manage', 'pricing.manage',
            'config.manage', 'reports.view', 'logs.view',
        ],
        self::STAFF => [
            'bookings.view', 'bookings.create', 'bookings.status', 'customers.manage',
        ],
    ];

    protected $fillable = ['name', 'slug', 'description', 'is_system'];

    protected $casts = ['is_system' => 'boolean'];

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function hasPermission(string $slug): bool
    {
        return $this->permissions->contains('slug', $slug);
    }

    public function isSuperAdmin(): bool
    {
        return $this->slug === self::SUPER_ADMIN;
    }

    /** True when the role cannot be deleted or renamed. */
    public function isLocked(): bool
    {
        return (bool) $this->is_system;
    }

    public function label(): string
    {
        return $this->name ?: Str::headline($this->slug);
    }
}
