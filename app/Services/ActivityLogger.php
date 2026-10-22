<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Central audit trail. Every meaningful mutation in the app funnels through
 * here so the admin activity-log page has a complete, searchable history.
 */
class ActivityLogger
{
    /** Action constants keep log filters stable across the codebase. */
    public const BOOKING_CREATED = 'booking.created';
    public const BOOKING_UPDATED = 'booking.updated';
    public const BOOKING_STATUS = 'booking.status_changed';
    public const BOOKING_CANCELLED = 'booking.cancelled';
    public const ACTIVITY_CREATED = 'activity.created';
    public const ACTIVITY_UPDATED = 'activity.updated';
    public const ACTIVITY_DELETED = 'activity.deleted';
    public const PRICE_CHANGED = 'price.changed';
    public const CUSTOMER_UPDATED = 'customer.updated';
    public const CUSTOMER_CREATED = 'customer.created';
    public const USER_CREATED = 'user.created';
    public const USER_UPDATED = 'user.updated';
    public const USER_DELETED = 'user.deleted';
    public const ROLE_UPDATED = 'role.updated';
    public const SETTINGS_UPDATED = 'settings.updated';
    public const CONFIG_UPDATED = 'config.updated';
    public const AUTH_LOGIN = 'auth.login';
    public const AUTH_LOGOUT = 'auth.logout';
    public const AUTH_REGISTERED = 'auth.registered';

    /**
     * Record an activity.
     */
    public function log(
        string $action,
        string $description,
        ?Model $subject = null,
        array $meta = [],
        ?User $user = null,
    ): ActivityLog {
        $request = Request::instance() ?? request();
        $actor = $user ?? Auth::user();

        return ActivityLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'description' => $description,
            'subject_type' => $subject?->getMorphClass(),
            'subject_id' => $subject?->getKey(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request ? substr((string) $request->userAgent(), 0, 500) : null,
            'meta' => $meta ?: null,
        ]);
    }

    /** Log a login/logout event for an explicit user (no session yet on login). */
    public function auth(string $action, User $user, string $description): ActivityLog
    {
        return $this->log($action, $description, $user, [], $user);
    }
}
