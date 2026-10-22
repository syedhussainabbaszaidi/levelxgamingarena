<?php

namespace App\Services;

use App\Models\PricingRule;
use App\Models\Resource;
use Carbon\Carbon;

/**
 * Calculates prices for time ranges on a resource based on PricingRule windows.
 *
 * Strategy:
 * - The [starts_at, ends_at) range is split into segments at every pricing-window boundary.
 * - Each segment is billed per minute at the rate of the highest-priority matching rule.
 * - If total minutes < min_minutes of the matched rule, the rule's min_minutes is billed instead.
 */
class PricingService
{
    /**
     * Calculate the price for a time range on a resource.
     */
    public function calculate(Resource $resource, Carbon $startsAt, Carbon $endsAt): array
    {
        $totalMinutes = (int) ceil($startsAt->diffInMinutes($endsAt, true));
        if ($totalMinutes <= 0) {
            return ['total' => 0.0, 'segments' => [], 'minutes' => 0];
        }

        // Fixed-price packages are charged once, whatever the window.
        if ($resource->isFixed()) {
            return [
                'total' => $resource->price(),
                'segments' => [[
                    'start' => $startsAt->format('H:i'),
                    'end' => $endsAt->format('H:i'),
                    'minutes' => $totalMinutes,
                    'rate' => null,
                    'rule' => $resource->packageLabel(),
                    'cost' => $resource->price(),
                ]],
                'minutes' => $totalMinutes,
                'fixed' => true,
            ];
        }

        $rules = $this->rulesFor($resource);
        $breakpoints = $this->breakpoints($startsAt, $endsAt, $rules);

        $segments = [];
        $total = 0.0;

        for ($i = 0; $i < count($breakpoints) - 1; $i++) {
            $segStart = $breakpoints[$i];
            $segEnd = $breakpoints[$i + 1];
            $minutes = (int) ceil($segStart->diffInMinutes($segEnd, true));
            if ($minutes <= 0) {
                continue;
            }

            $rule = $this->matchingRule($rules, $segStart);
            $rate = $rule ? (float) $rule->hourly_rate : $resource->effectiveRate();
            $perMinute = $rate / 60;
            $cost = $minutes * $perMinute;

            $segments[] = [
                'start' => $segStart->format('H:i'),
                'end' => $segEnd->format('H:i'),
                'minutes' => $minutes,
                'rate' => round($rate, 2),
                'rule' => $rule?->name,
                'cost' => round($cost, 2),
            ];

            $total += $cost;
        }

        // Enforce min_minutes: if the whole booking is shorter than the minimum
        // of the rule that covers its start, charge the minimum.
        $startRule = $this->matchingRule($rules, $startsAt);
        if ($startRule && $totalMinutes < $startRule->min_minutes) {
            $minTotal = $startRule->min_minutes * ($startRule->hourly_rate / 60);
            $total = max($total, $minTotal);
        }

        return [
            'total' => round($total, 2),
            'segments' => $segments,
            'minutes' => $totalMinutes,
        ];
    }

    /**
     * Shortest bookable duration for a start time: the activity's own minimum
     * plus any minimum imposed by the rate window that applies (e.g. the
     * Monday PC special requires 3 hours).
     */
    public function minimumMinutesFor(Resource $resource, Carbon $startsAt): int
    {
        // The venue-wide minimum is the floor; an activity (or an active rate
        // window such as the 3-hour Monday PC special) may require longer.
        $min = max(
            (int) ($resource->business->min_duration_minutes ?? 30),
            $resource->minimumMinutes()
        );
        $rule = $this->matchingRule($this->rulesFor($resource), $startsAt);

        if ($rule && $rule->min_minutes > 0) {
            $min = max($min, (int) $rule->min_minutes);
        }

        return max(15, $min);
    }

    /**
     * Price for extending a running booking.
     */
    public function extensionPrice(Resource $resource, Carbon $currentEnd, int $extraMinutes): float
    {
        $from = $currentEnd->copy();
        $to = $from->copy()->addMinutes($extraMinutes);

        return $this->calculate($resource, $from, $to)['total'];
    }

    /** All active rules applicable to a resource (business-wide, category, or specific). */
    protected function rulesFor(Resource $resource)
    {
        return PricingRule::query()
            ->forResource($resource->id)
            ->get();
    }

    /**
     * Build the sorted list of segment boundaries across the range:
     * range start/end plus every rule window edge that falls inside.
     */
    protected function breakpoints(Carbon $startsAt, Carbon $endsAt, $rules): array
    {
        $points = [$startsAt->copy()->startOfMinute(), $endsAt->copy()->startOfMinute()];

        foreach ($rules as $rule) {
            foreach ($this->windowEdgesInRange($rule, $startsAt, $endsAt) as $edge) {
                if ($edge->gt($points[0]) && $edge->lt($points[1])) {
                    $points[] = $edge->copy()->startOfMinute();
                }
            }
        }

        usort($points, fn (Carbon $a, Carbon $b) => $a->timestamp <=> $b->timestamp);

        // de-duplicate identical timestamps
        $unique = [];
        foreach ($points as $p) {
            if (empty($unique) || $p->ne(end($unique))) {
                $unique[] = $p;
            }
        }

        return $unique;
    }

    /**
     * All occurrences (today ± 1 day) of a rule's daily window edges
     * that fall within the given range.
     */
    protected function windowEdgesInRange(PricingRule $rule, Carbon $startsAt, Carbon $endsAt): array
    {
        $edges = [];
        $daysChecked = [];

        // check the start day, previous day (for overnight windows), and next day
        foreach ([0, -1, 1] as $offset) {
            $day = $startsAt->copy()->addDays($offset)->startOfDay();
            $key = $day->format('Y-m-d');
            if (in_array($key, $daysChecked, true)) {
                continue;
            }
            $daysChecked[] = $key;

            if ($rule->day_of_week !== null && (int) $day->format('w') !== (int) $rule->day_of_week) {
                continue;
            }

            $winStart = $day->copy()->setTimeFromTimeString($rule->start_time);
            $winEnd = $day->copy()->setTimeFromTimeString($rule->end_time);
            if ($rule->overnight) {
                $winEnd->addDay();
            }

            $edges[] = $winStart;
            $edges[] = $winEnd;
        }

        return $edges;
    }

    /** Highest-priority active rule whose window contains the moment (else null → resource rate). */
    protected function matchingRule($rules, Carbon $moment): ?PricingRule
    {
        foreach ($rules as $rule) {
            foreach ([0, -1] as $offset) {
                $day = $moment->copy()->addDays($offset)->startOfDay();

                if ($rule->day_of_week !== null && (int) $day->format('w') !== (int) $rule->day_of_week) {
                    continue;
                }

                $winStart = $day->copy()->setTimeFromTimeString($rule->start_time);
                $winEnd = $day->copy()->setTimeFromTimeString($rule->end_time);
                if ($rule->overnight) {
                    $winEnd->addDay();
                }

                if ($moment->gte($winStart) && $moment->lt($winEnd)) {
                    return $rule; // rules are pre-ordered by priority desc
                }
            }
        }

        return null;
    }
}
