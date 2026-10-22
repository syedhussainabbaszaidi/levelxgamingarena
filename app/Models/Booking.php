<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    public const STATUSES = ['pending', 'confirmed', 'completed', 'cancelled'];
    public const SOURCES = ['counter', 'online', 'admin'];
    public const ACTIVE_STATUSES = ['confirmed'];
    public const PAYMENT_STATUSES = ['unpaid', 'paid'];
    /** Statuses that count towards revenue when marked paid. */
    public const REVENUE_STATUSES = ['confirmed', 'completed'];

    protected $fillable = [
        'business_id', 'resource_id', 'customer_id', 'user_id',
        'code', 'source', 'status', 'payment_status', 'paid_at', 'payment_method',
        'starts_at', 'ends_at', 'duration_minutes',
        'estimated_cost', 'final_cost', 'extended_minutes',
        'checked_in_at', 'ended_at', 'cancelled_at', 'cancellation_reason',
        'notes', 'admin_read_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'checked_in_at' => 'datetime',
        'ended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paid_at' => 'datetime',
        'admin_read_at' => 'datetime',
    ];

    /* ---------------------------------------------------------------------
     | Back-office notifications
     | ------------------------------------------------------------------- */

    /** Customer (online) bookings the back office has not acknowledged yet. */
    public function scopeUnreadForAdmin(Builder $q): Builder
    {
        return $q->whereNull('admin_read_at')->where('source', 'online');
    }

    public function isUnreadForAdmin(): bool
    {
        return $this->admin_read_at === null && $this->source === 'online';
    }

    /** Acknowledge a single booking (idempotent). */
    public function markReadForAdmin(): void
    {
        if ($this->admin_read_at === null) {
            $this->forceFill(['admin_read_at' => now()])->saveQuietly();
        }
    }

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function fieldValues(): HasMany
    {
        return $this->hasMany(BookingFieldValue::class);
    }

    /** Paid amount recorded against this booking. */
    public function paidAmount(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function dueAmount(): float
    {
        return round((float) $this->final_cost - $this->paidAmount(), 2);
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'confirmed' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
            'pending' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
            'completed' => 'bg-sky-500/15 text-sky-300 border-sky-500/30',
            'cancelled' => 'bg-zinc-500/15 text-zinc-400 border-zinc-600/40',
            default => 'bg-zinc-500/15 text-zinc-300 border-zinc-600/40',
        };
    }

    public function paymentBadge(): string
    {
        return $this->payment_status === 'paid'
            ? 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30'
            : 'bg-amber-500/15 text-amber-300 border-amber-500/30';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    /** Revenue counts paid bookings that are not cancelled. */
    public function countsTowardsRevenue(): bool
    {
        return $this->isPaid() && in_array($this->status, self::REVENUE_STATUSES, true);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->whereIn('status', self::ACTIVE_STATUSES);
    }

    /** Bookings belonging to a customer account (linked profile or creator). */
    public function scopeForUser(Builder $q, User $user): Builder
    {
        return $q->where(fn (Builder $w) => $w
            ->where('user_id', $user->id)
            ->orWhereHas('customer', fn ($c) => $c->where('user_id', $user->id)));
    }

    public function scopeOverlapping(Builder $q, int $resourceId, $startsAt, $endsAt, ?int $ignoreId = null): Builder
    {
        return $q->where('resource_id', $resourceId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->where('starts_at', '<', $endsAt)
            ->where('ends_at', '>', $startsAt)
            ->when($ignoreId, fn (Builder $qq) => $qq->where('id', '!=', $ignoreId));
    }

    public function isRunning(): bool
    {
        return $this->status === 'confirmed' && $this->checked_in_at !== null && $this->ended_at === null;
    }

    public function endWithMinutes(int $extraMinutes): void
    {
        $this->ends_at = $this->ends_at->copy()->addMinutes($extraMinutes);
        $this->extended_minutes += $extraMinutes;
        $this->duration_minutes += $extraMinutes;
    }
}
