<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\BookingFieldValue;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\Resource;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Single booking engine used by counter, admin and online flows.
 */
class BookingService
{
    public function __construct(
        protected AvailabilityService $availability,
        protected PricingService $pricing,
        protected ActivityLogger $logger,
    ) {}

    /**
     * Create a booking (counter, online or admin).
     *
     * @param  array  $data  resource_id, starts_at (Y-m-d H:i), duration_minutes,
     *                       source, customer (array|null), user_id, notes, status
     */
    public function create(array $data): Booking
    {
        $resource = Resource::with('business')->findOrFail($data['resource_id']);

        $startsAt = Carbon::parse($data['starts_at'])->startOfMinute();
        $duration = max(15, (int) ($data['duration_minutes'] ?? 60));

        // A fixed package always sells its full, predefined block of time.
        if ($resource->isFixed() && $resource->package_minutes) {
            $duration = (int) $resource->package_minutes;
        }

        $minimum = $this->pricing->minimumMinutesFor($resource, $startsAt);
        if ($duration < $minimum) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'The minimum booking for '.$resource->name.' is '.$this->humanMinutes($minimum).'.',
            ]);
        }

        if (($maximum = $resource->maximumMinutes()) && $duration > $maximum) {
            throw ValidationException::withMessages([
                'duration_minutes' => 'The maximum booking for '.$resource->name.' is '.$this->humanMinutes($maximum).'.',
            ]);
        }

        $endsAt = $startsAt->copy()->addMinutes($duration);
        $allowPast = (bool) ($data['allow_past'] ?? false);

        $check = $this->availability->validate($resource, $startsAt, $endsAt, null, $allowPast);
        if (! $check['ok']) {
            throw ValidationException::withMessages(['time' => $check['message']]);
        }

        $customer = $this->resolveCustomer($resource->business_id, $data['customer'] ?? null);

        $price = $this->pricing->calculate($resource, $startsAt, $endsAt);

        return DB::transaction(function () use ($data, $resource, $customer, $startsAt, $endsAt, $duration, $price) {
            // Re-check overlap inside the transaction to avoid double-booking races.
            if (Booking::overlapping($resource->id, $startsAt, $endsAt)->exists()) {
                throw ValidationException::withMessages(['time' => 'Someone just booked this slot. Please choose another time.']);
            }

            $booking = Booking::create([
                'business_id' => $resource->business_id,
                'resource_id' => $resource->id,
                'customer_id' => $customer?->id,
                'user_id' => $data['user_id'] ?? null,
                'code' => $this->nextCode(),
                'source' => $data['source'] ?? 'counter',
                'status' => $data['status'] ?? 'confirmed',
                'payment_status' => 'unpaid',
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'duration_minutes' => $duration,
                'estimated_cost' => $price['total'],
                'final_cost' => $price['total'],
                'notes' => $data['notes'] ?? null,
            ]);

            // Persist the dynamic booking form answers (admin-configured fields).
            if (! empty($data['field_values'])) {
                $this->storeFieldValues($booking, $data['field_values']);
            }

            $this->logger->log(
                ActivityLogger::BOOKING_CREATED,
                sprintf('%s created for %s (%s, %d min).', $booking->code, $resource->name, $startsAt->format('d M Y H:i'), $duration),
                $booking,
                ['source' => $booking->source, 'status' => $booking->status, 'total' => (float) $booking->estimated_cost],
                $customer?->user
            );

            // Advance payment (counter / online optional)
            if (! empty($data['advance_amount']) && (float) $data['advance_amount'] > 0 && $customer) {
                $this->addPayment($booking, [
                    'amount' => (float) $data['advance_amount'],
                    'method' => $data['advance_method'] ?? 'cash',
                    'kind' => 'advance',
                    'user_id' => $data['user_id'] ?? null,
                ]);
            }

            return $booking;
        });
    }

    /**
     * Check in a confirmed booking → live session (internal state only:
     * checked_in_at/ended_at timestamps; the admin-facing status set is
     * pending/confirmed/completed/cancelled).
     */
    public function checkIn(Booking $booking): Booking
    {
        if ($booking->status !== 'confirmed' && $booking->status !== 'pending') {
            throw ValidationException::withMessages(['status' => 'Only confirmed bookings can be checked in.']);
        }

        $booking->update([
            'status' => 'confirmed',
            'checked_in_at' => now(),
        ]);

        $this->logger->log(ActivityLogger::BOOKING_STATUS, "{$booking->code} checked in.", $booking, ['status' => 'confirmed']);

        return $booking;
    }

    /**
     * Extend a live session by N minutes (availability-checked).
     * Returns the extra cost of the extension.
     */
    public function extend(Booking $booking, int $extraMinutes, ?User $byUser = null): array
    {
        if ($booking->checked_in_at === null || $booking->ended_at !== null) {
            throw ValidationException::withMessages(['status' => 'Only live sessions can be extended.']);
        }

        $extraMinutes = max(15, $extraMinutes);
        $newEnd = $booking->ends_at->copy()->addMinutes($extraMinutes);

        $overlap = Booking::overlapping(
            $booking->resource_id,
            $booking->ends_at,
            $newEnd,
            $booking->id
        )->exists();

        if ($overlap) {
            throw ValidationException::withMessages(['time' => 'Cannot extend: the next slot on this resource is already booked.']);
        }

        $extraCost = $this->pricing->extensionPrice($booking->resource, $booking->ends_at, $extraMinutes);

        return DB::transaction(function () use ($booking, $extraMinutes, $extraCost, $byUser) {
            $booking->endWithMinutes($extraMinutes);
            $booking->final_cost += $extraCost;
            $booking->save();

            if ($byUser) {
                $booking->user_id = $booking->user_id ?? $byUser->id;
            }

            return ['booking' => $booking, 'extra_cost' => $extraCost];
        });
    }

    /**
     * End a live session and settle payment.
     */
    public function endSession(Booking $booking, array $paymentData = []): Booking
    {
        if ($booking->checked_in_at === null || $booking->ended_at !== null) {
            throw ValidationException::withMessages(['status' => 'Only live sessions can be ended.']);
        }

        return DB::transaction(function () use ($booking, $paymentData) {
            $actualEnd = now();

            // Overstay: ran longer than ends_at without formal extension.
            // Count whole minutes only (sub-minute processing delay is not billed).
            $overstaySeconds = max(0, (int) $booking->ends_at->diffInSeconds($actualEnd, false));
            $overstayMinutes = intdiv($overstaySeconds, 60);
            if ($overstayMinutes > 0) {
                $extraCost = $this->pricing->extensionPrice($booking->resource, $booking->ends_at, $overstayMinutes);
                $booking->endWithMinutes($overstayMinutes);
                $booking->final_cost += $extraCost;
            }

            $booking->update([
                'status' => 'completed',
                'ended_at' => $actualEnd,
            ]);

            $paid = (float) $booking->payments()->sum('amount');
            $due = round((float) $booking->final_cost - $paid, 2);

            if ($due > 0 && ($paymentData['charge'] ?? true)) {
                $settlement = $this->addPayment($booking, [
                    'amount' => $due,
                    'method' => $paymentData['method'] ?? 'cash',
                    'kind' => 'full',
                    'user_id' => $paymentData['user_id'] ?? null,
                ]);

                $this->markPaidQuietly($booking, $settlement->method, $settlement->created_at);
            } else {
                $this->syncPaymentFlagWithLedger($booking);
            }

            $this->logger->log(
                ActivityLogger::BOOKING_STATUS,
                "{$booking->code} session ended — final total ".number_format((float) $booking->final_cost, 2).'.',
                $booking,
                ['status' => 'completed', 'final_cost' => (float) $booking->final_cost, 'payment_status' => $booking->payment_status]
            );

            return $booking;
        });
    }

    /**
     * Cancel a booking. Unpaid cancelled bookings keep their due amount visible
     * as cancelled; paid ones keep revenue until refunded by policy.
     */
    public function cancel(Booking $booking, string $reason = '', ?User $byUser = null): Booking
    {
        if (in_array($booking->status, ['completed', 'cancelled'], true)) {
            throw ValidationException::withMessages(['status' => 'This booking can no longer be cancelled.']);
        }

        // Refund logic placeholder: advance payments would be refunded per business policy.
        $booking->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancellation_reason' => $reason ?: null,
        ]);

        $this->logger->log(
            ActivityLogger::BOOKING_CANCELLED,
            "{$booking->code} cancelled".($reason ? ": {$reason}" : '.'),
            $booking,
            ['status' => 'cancelled']
        );

        return $booking;
    }

    /**
     * Add a payment to a booking and update customer aggregates.
     */
    public function addPayment(Booking $booking, array $data): Payment
    {
        $payment = Payment::create([
            'business_id' => $booking->business_id,
            'booking_id' => $booking->id,
            'customer_id' => $booking->customer_id,
            'user_id' => $data['user_id'] ?? null,
            'code' => $this->nextPaymentCode(),
            'amount' => $data['amount'],
            'method' => in_array($data['method'] ?? 'cash', ['cash', 'online'], true)
                ? $data['method']
                : 'cash',
            'kind' => $data['kind'] ?? 'full',
            'notes' => $data['notes'] ?? null,
        ]);

        if ($booking->customer) {
            $booking->customer->increment('total_spent', $payment->amount);
        }

        return $payment;
    }

    /**
     * Mark a booking paid/unpaid. payment_status is the single source of truth
     * for revenue; the payments ledger is kept in sync so totals can never
     * double-count:
     * - unpaid → paid: records ONE settle payment for the outstanding balance.
     * - paid → unpaid: removes exactly the settle payment(s) added by this flow.
     *   Advance payments (money actually taken earlier) are preserved.
     */
    public function setPaymentStatus(Booking $booking, string $paymentStatus, string $method = 'cash', ?User $byUser = null): Booking
    {
        if (! in_array($paymentStatus, Booking::PAYMENT_STATUSES, true)) {
            throw ValidationException::withMessages(['payment_status' => 'Unknown payment status.']);
        }

        $method = in_array($method, ['cash', 'online'], true) ? $method : 'cash';

        DB::transaction(function () use ($booking, $paymentStatus, $method, $byUser) {
            if ($paymentStatus === 'paid') {
                $due = $booking->dueAmount();

                if ($due > 0) {
                    $this->addPayment($booking, [
                        'amount' => $due,
                        'method' => $method,
                        'kind' => 'full',
                        'user_id' => $byUser?->id,
                        'notes' => 'Settled via payment status toggle',
                    ]);
                }

                $this->markPaidQuietly($booking, $method, now());
            } else {
                // Remove only the auto-generated settle rows (identified by note),
                // never advance/manual payments taken at the counter.
                $settle = $booking->payments()
                    ->where('kind', 'full')
                    ->where('notes', 'Settled via payment status toggle')
                    ->get();

                foreach ($settle as $payment) {
                    $payment->delete();
                    if ($booking->customer) {
                        $booking->customer->decrement('total_spent', $payment->amount);
                    }
                }

                $booking->forceFill(['payment_status' => 'unpaid', 'paid_at' => null, 'payment_method' => null])->save();
            }
        });

        $this->logger->log(
            ActivityLogger::BOOKING_STATUS,
            "{$booking->code} marked {$paymentStatus}".($paymentStatus === 'paid' ? " ({$method})" : '').'.',
            $booking,
            ['payment_status' => $paymentStatus, 'method' => $method]
        );

        return $booking;
    }

    /** Set the paid flag without emitting an extra audit entry. */
    protected function markPaidQuietly(Booking $booking, string $method, $at): void
    {
        $booking->forceFill([
            'payment_status' => 'paid',
            'payment_method' => $method,
            'paid_at' => $at ?? now(),
        ])->save();
    }

    /** Derive the flag from the ledger (used when payments change outside this flow). */
    protected function syncPaymentFlagWithLedger(Booking $booking): void
    {
        $paid = (float) $booking->payments()->sum('amount');

        if ($paid >= (float) $booking->final_cost && (float) $booking->final_cost > 0) {
            $this->markPaidQuietly($booking, (string) ($booking->payment_method ?? 'cash'), $booking->payments()->max('created_at'));
        } else {
            $booking->forceFill(['payment_status' => 'unpaid', 'paid_at' => null, 'payment_method' => null])->save();
        }
    }

    /**
     * Change a booking's status (admin/staff action) with an audit entry.
     */
    public function changeStatus(Booking $booking, string $status, ?string $note = null): Booking
    {
        if (! in_array($status, Booking::STATUSES, true)) {
            throw ValidationException::withMessages(['status' => 'Unknown booking status.']);
        }

        $from = $booking->status;
        $booking->update(['status' => $status]);

        $this->logger->log(
            ActivityLogger::BOOKING_STATUS,
            "{$booking->code} status changed from {$from} to {$status}".($note ? " ({$note})" : '.'),
            $booking,
            ['from' => $from, 'to' => $status]
        );

        return $booking;
    }

    /** Walk-in: create + immediately check in. May start a few minutes in the past. */
    public function walkIn(array $data): Booking
    {
        $data['source'] = 'counter';
        $data['status'] = 'confirmed';
        $data['allow_past'] = true;
        $booking = $this->create($data);
        $this->checkIn($booking);

        return $booking;
    }

    /**
     * Find or create the customer record for a booking. Handles three cases:
     * an existing customer id, a registered user account, or a walk-in by phone.
     */
    protected function resolveCustomer(int $businessId, ?array $customer): ?Customer
    {
        if (empty($customer) || (empty($customer['phone']) && empty($customer['id']) && empty($customer['user_id']))) {
            return null;
        }

        if (! empty($customer['id'])) {
            return Customer::where('business_id', $businessId)->findOrFail($customer['id']);
        }

        $userId = $customer['user_id'] ?? null;

        // Registered customer account: reuse the linked profile.
        if ($userId && $existing = Customer::where('business_id', $businessId)->where('user_id', $userId)->first()) {
            $phone = $customer['phone'] ?? null;

            // Only adopt a new phone number when it does not belong to another
            // customer of the same business (business_id + phone is unique).
            if ($phone && $phone !== $existing->phone) {
                $taken = Customer::where('business_id', $businessId)
                    ->where('phone', $phone)
                    ->whereKeyNot($existing->id)
                    ->exists();

                if (! $taken) {
                    $existing->phone = $phone;
                }
            }

            if (! empty($customer['name'])) {
                $existing->name = $customer['name'];
            }
            if (! empty($customer['email'])) {
                $existing->email = $customer['email'];
            }

            $existing->save();

            return $existing;
        }

        // Known phone number (walk-in or previously booked): attach the account.
        if (! empty($customer['phone'])) {
            return Customer::updateOrCreate(
                ['business_id' => $businessId, 'phone' => $customer['phone']],
                [
                    'name' => $customer['name'] ?? 'Walk-in Customer',
                    'email' => $customer['email'] ?? null,
                    'user_id' => $userId,
                ]
            );
        }

        return Customer::create([
            'business_id' => $businessId,
            'user_id' => $userId,
            'name' => $customer['name'] ?? 'Online Customer',
            'phone' => $customer['email'] ?? ('user-'.$userId),
            'email' => $customer['email'] ?? null,
        ]);
    }

    /**
     * Save dynamic field answers: ['<booking_field_id>' => value, ...].
     */
    protected function storeFieldValues(Booking $booking, array $values): void
    {
        foreach ($values as $fieldId => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            BookingFieldValue::updateOrCreate(
                ['booking_id' => $booking->id, 'booking_field_id' => (int) $fieldId],
                ['value' => is_array($value) ? implode(', ', $value) : (string) $value]
            );
        }
    }

    /** "180" → "3 hours", "90" → "90 minutes". */
    protected function humanMinutes(int $minutes): string
    {
        if ($minutes % 60 === 0) {
            $hours = (int) ($minutes / 60);

            return $hours.' '.($hours === 1 ? 'hour' : 'hours');
        }

        return $minutes.' minutes';
    }

    protected function nextCode(): string
    {
        $date = now()->format('Ymd');
        $seq = (int) (Booking::whereDate('created_at', now())->count() + 1);

        return sprintf('BK-%s-%05d', $date, $seq);
    }

    protected function nextPaymentCode(): string
    {
        $date = now()->format('Ymd');
        $seq = (int) (Payment::whereDate('created_at', now())->count() + 1);

        return sprintf('PAY-%s-%05d', $date, $seq);
    }
}
