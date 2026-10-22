<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Booking;
use App\Models\Resource;
use App\Models\TimeSlot;
use Carbon\Carbon;
use Carbon\CarbonInterface;

/**
 * Availability engine.
 *
 * Calculates bookable slots for a resource on a date and validates
 * that a proposed time range does not overlap existing active bookings
 * and stays inside business hours.
 */
class AvailabilityService
{
    /**
     * Bookable slot grid for a resource + date.
     *
     * @return array{ slots: array, business_open: bool, message: ?string }
     */
    public function slotsForResource(Resource $resource, Carbon $date, int $slotMinutes = 30): array
    {
        $business = $resource->business;
        $open = $this->businessOpenAt($business, $date);
        $close = $this->businessCloseAt($business, $date);

        $slots = [];
        if (! $resource->isBookable()) {
            return [
                'slots' => [],
                'business_open' => false,
                'message' => $resource->status === 'maintenance'
                    ? 'Resource is under maintenance.'
                    : 'Resource is not available.',
            ];
        }

        $busyRanges = $this->busyRanges($resource, $open, $close);

        for ($t = $open->copy(); $t->lt($close); $t->addMinutes($slotMinutes)) {
            $slotEnd = $t->copy()->addMinutes($slotMinutes);
            $slots[] = [
                'time' => $t->format('H:i'),
                'label' => $t->format('g:i A'),
                'available' => $slotEnd->gt(Carbon::now()) && ! $this->anyOverlap($busyRanges, $t, $slotEnd),
            ];
        }

        return [
            'slots' => $slots,
            'business_open' => true,
            'message' => null,
        ];
    }

    /**
     * Slots for a resource honouring the admin-configured time slots.
     * Falls back to the generated grid when no preset applies to the date.
     *
     * @return array{ slots: array, business_open: bool, message: ?string }
     */
    public function configuredSlots(Resource $resource, Carbon $date, int $defaultSlotMinutes = 60): array
    {
        $override = $this->slotWindowsFor($resource, $date);

        if ($override->isEmpty()) {
            return $this->slotsForResource($resource, $date, $defaultSlotMinutes);
        }

        if (! $resource->isBookable()) {
            return [
                'slots' => [],
                'business_open' => false,
                'message' => $resource->status === 'maintenance'
                    ? 'Resource is under maintenance.'
                    : 'Resource is not available.',
            ];
        }

        $open = $this->businessOpenAt($resource->business, $date);
        $close = $this->businessCloseAt($resource->business, $date);
        $busy = $this->busyRanges($resource, $open, $close);

        $slots = [];
        foreach ($override as $window) {
            $windowStart = $date->copy()->setTimeFromTimeString($window->start_time);
            $windowEnd = $date->copy()->setTimeFromTimeString($window->end_time);
            if ($windowEnd->lte($windowStart)) {
                $windowEnd->addDay(); // window crosses midnight
            }

            // Clamp to business hours.
            $start = $windowStart->lt($open) ? $open->copy() : $windowStart->copy();
            $end = $windowEnd->gt($close) ? $close->copy() : $windowEnd->copy();
            $step = max(15, (int) $window->slot_minutes);

            for ($t = $start->copy(); $t->lt($end); $t->addMinutes($step)) {
                $slotEnd = $t->copy()->addMinutes($step);
                if ($slotEnd->gt($end)) {
                    break;
                }

                $slots[] = [
                    'time' => $t->format('H:i'),
                    'label' => $t->format('g:i A'),
                    'window' => $window->label,
                    'minutes' => $step,
                    // A slot is bookable only if it is in the future and free.
                    'available' => $slotEnd->gt(Carbon::now()) && ! $this->anyOverlap($busy, $t, $slotEnd),
                ];
            }
        }

        return ['slots' => $slots, 'business_open' => true, 'message' => null];
    }

    /** Whether a resource still has at least one bookable slot on a given date. */
    public function hasAvailability(Resource $resource, Carbon $date, int $defaultSlotMinutes = 60): bool
    {
        if (! $resource->isBookable()) {
            return false;
        }

        $result = $this->configuredSlots($resource, $date, $defaultSlotMinutes);

        if (! $result['business_open']) {
            return false;
        }

        foreach ($result['slots'] as $slot) {
            if ($slot['available']) {
                return true;
            }
        }

        return false;
    }

    /** Active configured windows that apply to a resource on a given date. */
    protected function slotWindowsFor(Resource $resource, Carbon $date)
    {
        return TimeSlot::query()
            ->active()
            ->forResource($resource->id)
            ->where(fn ($q) => $q->whereNull('day_of_week')->orWhere('day_of_week', (int) $date->format('w')))
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Validate a proposed booking window on a resource.
     *
     * @param  bool  $allowPast  allow start times slightly in the past (walk-ins)
     * @return array{ ok: bool, message: ?string }
     */
    public function validate(Resource $resource, Carbon $startsAt, Carbon $endsAt, ?int $ignoreBookingId = null, bool $allowPast = false): array
    {
        if ($endsAt->lte($startsAt)) {
            return ['ok' => false, 'message' => 'End time must be after start time.'];
        }

        if (! $allowPast && $startsAt->lt(Carbon::now()->subMinutes(5))) {
            return ['ok' => false, 'message' => 'Start time cannot be in the past.'];
        }

        if (! $resource->isBookable()) {
            return ['ok' => false, 'message' => 'Resource is not currently bookable ('.$resource->status.').'];
        }

        // Walk-in mode skips business-hours checks: the counter is open now,
        // so the session may start in the previous business day's window.
        // A 24/7 venue has no closing time to validate against either.
        if (! $allowPast && ! $resource->business->is24Hours()) {
            $business = $resource->business;
            $open = $this->businessOpenAt($business, $startsAt->copy()->startOfDay());
            $close = $this->businessCloseAt($business, $startsAt->copy()->startOfDay());

            if ($startsAt->lt($open) || $startsAt->gte($close)) {
                return ['ok' => false, 'message' => 'Start time is outside business hours ('.$open->format('g:i A').' – '.$close->format('g:i A').').'];
            }

            if ($endsAt->gt($close)) {
                return ['ok' => false, 'message' => 'Booking would end after closing time ('.$close->format('g:i A').').'];
            }
        }

        $overlap = Booking::overlapping($resource->id, $startsAt, $endsAt, $ignoreBookingId)->exists();

        if ($overlap) {
            return ['ok' => false, 'message' => 'This resource already has a booking overlapping the selected time.'];
        }

        return ['ok' => true, 'message' => null];
    }

    /** Busy ranges (active bookings) of a resource within a window. */
    public function busyRanges(Resource $resource, Carbon $from, Carbon $to): array
    {
        return Booking::where('resource_id', $resource->id)
            ->active()
            ->where('ends_at', '>', $from)
            ->where('starts_at', '<', $to)
            ->get(['starts_at', 'ends_at'])
            ->map(fn (Booking $b) => ['start' => $b->starts_at, 'end' => $b->ends_at])
            ->all();
    }

    protected function anyOverlap(array $ranges, Carbon $startsAt, Carbon $endsAt): bool
    {
        foreach ($ranges as $r) {
            if ($startsAt->lt($r['end']) && $endsAt->gt($r['start'])) {
                return true;
            }
        }

        return false;
    }

    /** Opening datetime on/for the given date (handles overnight closing). */
    public function businessOpenAt(Business $business, CarbonInterface $date): Carbon
    {
        return Carbon::parse($date->format('Y-m-d').' '.$business->opens_at->format('H:i:s'))->startOfMinute();
    }

    /** Closing datetime for the given date; adds a day when business runs past midnight. */
    public function businessCloseAt(Business $business, CarbonInterface $date): Carbon
    {
        $close = Carbon::parse($date->format('Y-m-d').' '.$business->closes_at->format('H:i:s'))->startOfMinute();

        if ($business->overnight) {
            $close->addDay();
        }

        return $close;
    }
}
