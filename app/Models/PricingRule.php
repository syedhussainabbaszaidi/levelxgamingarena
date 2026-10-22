<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingRule extends Model
{
    protected $fillable = [
        'business_id', 'category_id', 'resource_id', 'name',
        'day_of_week', 'start_time', 'end_time', 'overnight',
        'hourly_rate', 'min_minutes', 'slot_minutes', 'is_active', 'priority',
    ];

    protected $casts = [
        'overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    /** Rules that could apply to a resource (generic, category-level or resource-level). */
    public function scopeForResource(Builder $q, int $resourceId): Builder
    {
        $resource = Resource::findOrFail($resourceId);

        return $q->where(function (Builder $qq) use ($resource) {
            $qq->where(function (Builder $q2) use ($resource) {
                $q2->whereNull('category_id')->whereNull('resource_id');
            })->orWhere(function (Builder $q2) use ($resource) {
                $q2->where('category_id', $resource->category_id)->whereNull('resource_id');
            })->orWhere('resource_id', $resource->id);
        })->where('is_active', true)->orderByDesc('priority');
    }
}
