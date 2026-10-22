<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingField extends Model
{
    public const TYPES = [
        'text' => 'Text',
        'textarea' => 'Long text',
        'number' => 'Number',
        'select' => 'Dropdown',
        'checkbox' => 'Checkbox',
        'email' => 'Email',
        'tel' => 'Phone',
        'date' => 'Date',
        'time' => 'Time',
    ];

    protected $fillable = [
        'resource_id', 'key', 'label', 'type', 'placeholder', 'options',
        'default_value', 'is_required', 'is_visible', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
        'is_visible' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function resource(): BelongsTo
    {
        return $this->belongsTo(Resource::class);
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true);
    }

    /** Fields shown on the customer booking form. */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('is_active', true)->where('is_visible', true);
    }

    /** Fields that apply to an activity (its own + global ones). */
    public function scopeForResource(Builder $q, int $resourceId): Builder
    {
        return $q->where(fn (Builder $w) => $w
            ->where('resource_id', $resourceId)
            ->orWhereNull('resource_id'));
    }

    public function isGlobal(): bool
    {
        return $this->resource_id === null;
    }

    /** Options as a plain array (['a','b'] or ['k'=>'v']) for the form builder. */
    public function choiceOptions(): array
    {
        return is_array($this->options) ? $this->options : [];
    }
}
