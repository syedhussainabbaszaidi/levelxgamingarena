<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Legacy enum values kept in sync with the assigned role for BC. */
    public const ROLES = ['admin', 'staff', 'customer'];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /** Request-scoped caches so permission checks do not hammer the DB. */
    protected ?array $roleSlugCache = null;

    protected ?array $permissionSlugCache = null;

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /* ---------------------------------------------------------------------
     | Relationships
     | ------------------------------------------------------------------- */

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /* ---------------------------------------------------------------------
     | Roles
     | ------------------------------------------------------------------- */

    /** @return array<int, string> */
    public function roleSlugs(): array
    {
        return $this->roleSlugCache ??= $this->roles()->pluck('slug')->all();
    }

    public function hasRole(string ...$slugs): bool
    {
        $haystack = $this->roleSlugs();

        if ($this->role) {
            $haystack[] = $this->role; // legacy enum fallback
        }

        foreach ($slugs as $slug) {
            if (in_array($slug, $haystack, true)) {
                return true;
            }
        }

        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN);
    }

    public function isAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN, Role::ADMIN);
    }

    public function isStaff(): bool
    {
        return $this->hasRole(Role::STAFF, Role::BOOKING_MANAGER);
    }

    public function isStaffOrAdmin(): bool
    {
        return $this->hasRole(Role::SUPER_ADMIN, Role::ADMIN, Role::STAFF, Role::BOOKING_MANAGER);
    }

    public function isCustomer(): bool
    {
        return ! $this->isStaffOrAdmin();
    }

    /* ---------------------------------------------------------------------
     | Permissions
     | ------------------------------------------------------------------- */

    /** @return array<int, string> */
    public function permissionSlugs(): array
    {
        if ($this->permissionSlugCache === null) {
            $this->permissionSlugCache = Permission::query()
                ->whereHas('roles.users', fn ($q) => $q->where('users.id', $this->id))
                ->pluck('slug')
                ->unique()
                ->all();
        }

        return $this->permissionSlugCache;
    }

    public function hasPermissionTo(string $slug): bool
    {
        // Legacy rows may have a NULL flag — treat "not explicitly disabled" as active.
        if ($this->is_active === false) {
            return false;
        }

        if ($this->isSuperAdmin()) {
            return true;
        }

        if (in_array($slug, $this->permissionSlugs(), true)) {
            return true;
        }

        // Backwards compatibility: a back-office user created before roles were
        // introduced (legacy enum, no pivot rows) inherits that role's defaults.
        if ($this->roleSlugs() === [] && in_array($this->role, ['admin', 'staff'], true)) {
            $matrix = Role::MATRIX[$this->role === 'admin' ? Role::ADMIN : Role::STAFF];

            return $matrix === '*' || in_array($slug, $matrix, true);
        }

        return false;
    }

    public function hasAnyPermission(array $slugs): bool
    {
        foreach ($slugs as $slug) {
            if ($this->hasPermissionTo($slug)) {
                return true;
            }
        }

        return false;
    }

    /* ---------------------------------------------------------------------
     | Mutation helpers
     | ------------------------------------------------------------------- */

    public function assignRole(string|int|Role ...$roles): void
    {
        $ids = collect($roles)
            ->flatten()
            ->map(fn ($r) => $r instanceof Role
                ? $r->id
                : (is_numeric($r) ? (int) $r : Role::where('slug', $r)->value('id')))
            ->filter()
            ->all();

        $this->roles()->syncWithoutDetaching($ids);
        $this->refreshRoleCaches()->syncLegacyRole();
    }

    public function syncRoles(array $roleIds): void
    {
        $this->roles()->sync($roleIds);
        $this->refreshRoleCaches()->syncLegacyRole();
    }

    public function refreshRoleCaches(): static
    {
        $this->roleSlugCache = null;
        $this->permissionSlugCache = null;
        $this->unsetRelation('roles');

        return $this;
    }

    /**
     * Mirror the highest assigned role into the legacy `role` enum column so
     * existing middleware, redirects and analytics keep working.
     */
    public function syncLegacyRole(): void
    {
        $slugs = $this->roleSlugs();
        $legacy = 'customer';

        foreach ([Role::SUPER_ADMIN, Role::ADMIN, Role::BOOKING_MANAGER, Role::STAFF] as $slug) {
            if (in_array($slug, $slugs, true)) {
                $legacy = in_array($slug, [Role::SUPER_ADMIN, Role::ADMIN], true) ? 'admin' : 'staff';
                break;
            }
        }

        if ($this->role !== $legacy) {
            $this->forceFill(['role' => $legacy])->saveQuietly();
        }
    }

    /* ---------------------------------------------------------------------
     | Display helpers
     | ------------------------------------------------------------------- */

    /** Primary role label for badges ("Super Admin", "Staff", "Customer"). */
    public function primaryRoleLabel(): string
    {
        foreach ([Role::SUPER_ADMIN, Role::ADMIN, Role::BOOKING_MANAGER, Role::STAFF] as $slug) {
            if ($this->hasRole($slug)) {
                return match ($slug) {
                    Role::SUPER_ADMIN => 'Super Admin',
                    Role::ADMIN => 'Admin',
                    Role::BOOKING_MANAGER => 'Booking Manager',
                    Role::STAFF => 'Staff',
                };
            }
        }

        return 'Customer';
    }

    public function roleBadge(): string
    {
        return match (true) {
            $this->isSuperAdmin() => 'bg-rose-500/15 text-rose-300 border-rose-500/30',
            $this->isAdmin() => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
            $this->isStaff() => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
            default => 'bg-zinc-500/15 text-zinc-300 border-zinc-600/40',
        };
    }
}
