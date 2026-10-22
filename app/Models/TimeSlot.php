<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeSlot extends Model
{
    protected $fillable = [
        'resource_id', 'label', 'start_time', 'end_time',
        'day_of_week', 'slot_minutes', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public const DAYS = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Slots that apply to an activity (its own + shared presets). */
    public function scopeForResource(Builder $q, int $resourceId): Builder
    {
        return $q->where(fn (Builder $w) => $w
            ->where('resource_id', $resourceId)
            ->orWhereNull('resource_id'));
    }

    public function dayLabel(): string
    {
        return $this->day_of_week === null ? 'Every day' : self::DAYS[(int) $this->day_of_week];
    }

    public function timeLabel(): string
    {
        return substr($this->start_time, 0, 5).' – '.substr($this->end_time, 0, 5);
    }
}
