<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * A gaming activity / service (snooker table, PS5 booth, PC station, VIP room).
 * Called "resource" internally; presented as a gaming activity in the UI.
 */
class Resource extends Model
{
    protected $table = 'resources';

    /** How the activity is priced. */
    public const PRICING_HOURLY = 'hourly';

    public const PRICING_PER_MINUTE = 'per_minute';

    public const PRICING_FIXED = 'fixed';

    public const PRICING_TYPES = [
        self::PRICING_HOURLY => 'Per hour',
        self::PRICING_PER_MINUTE => 'Per minute',
        self::PRICING_FIXED => 'Fixed package',
    ];

    protected $fillable = [
        'business_id', 'category_id', 'name', 'code', 'image_path', 'description',
        'capacity', 'duration_minutes', 'hourly_rate', 'pricing_type', 'sale_price',
        'promo_label', 'fixed_price', 'package_minutes', 'min_minutes', 'max_minutes',
        'addon_label', 'addon_price', 'status',
        'is_online_bookable', 'featured', 'sort_order', 'meta',
    ];

    protected $casts = [
        'is_online_bookable' => 'boolean',
        'featured' => 'boolean',
        'meta' => 'array',
        'hourly_rate' => 'float',
        'sale_price' => 'float',
        'fixed_price' => 'float',
        'addon_price' => 'float',
        'package_minutes' => 'integer',
        'min_minutes' => 'integer',
        'max_minutes' => 'integer',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function bookingFields(): HasMany
    {
        return $this->hasMany(BookingField::class);
    }

    public function timeSlots(): HasMany
    {
        return $this->hasMany(TimeSlot::class);
    }

    /** Only activities that are active and offered online. */
    public function scopeOnlineBookable(Builder $q): Builder
    {
        return $q->where('status', 'active')->where('is_online_bookable', true);
    }

    public function scopeOrdered(Builder $q): Builder
    {
        return $q->orderBy('sort_order')->orderBy('name');
    }

    public function isBookable(): bool
    {
        return $this->status === 'active';
    }

    /* ------------------------------------------------------------------ */
    /* Pricing */
    /* ------------------------------------------------------------------ */

    public function isHourly(): bool
    {
        return $this->pricing_type === null || $this->pricing_type === self::PRICING_HOURLY;
    }

    public function isPerMinute(): bool
    {
        return $this->pricing_type === self::PRICING_PER_MINUTE;
    }

    public function isFixed(): bool
    {
        return $this->pricing_type === self::PRICING_FIXED;
    }

    /** Hourly rate actually charged (promotional price wins when set). */
    public function effectiveRate(): float
    {
        if ($this->hasDiscount()) {
            return (float) $this->sale_price;
        }

        return (float) $this->hourly_rate;
    }

    /** True when a promotional price is lower than the regular price. */
    public function hasDiscount(): bool
    {
        if ($this->sale_price === null) {
            return false;
        }

        $regular = $this->isFixed() ? (float) $this->fixed_price : (float) $this->hourly_rate;

        return $regular > 0 && (float) $this->sale_price < $regular;
    }

    public function discountPercent(): ?int
    {
        if (! $this->hasDiscount()) {
            return null;
        }

        $regular = $this->isFixed() ? (float) $this->fixed_price : (float) $this->hourly_rate;

        return (int) round((1 - ((float) $this->sale_price / $regular)) * 100);
    }

    /** Convert an hourly-equivalent amount into the unit this activity displays. */
    protected function fromHourly(float $hourly): float
    {
        return $this->isPerMinute() ? $hourly / 60 : $hourly;
    }

    /** Regular price in the activity's display unit (hour, minute or package). */
    public function regularUnitPrice(): float
    {
        if ($this->isFixed()) {
            return (float) $this->fixed_price;
        }

        return round($this->fromHourly((float) $this->hourly_rate), 2);
    }

    /** Price the customer currently pays, in the activity's display unit. */
    public function price(): float
    {
        if ($this->isFixed()) {
            return round((float) ($this->hasDiscount() ? $this->sale_price : $this->fixed_price), 2);
        }

        return round($this->fromHourly($this->effectiveRate()), 2);
    }

    /** "hour", "min" or null for a fixed package. */
    public function priceUnit(): ?string
    {
        if ($this->isFixed()) {
            return null;
        }

        return $this->isPerMinute() ? 'min' : 'hour';
    }

    /** "Rs 200 / hour", "Rs 12 / min" or "Rs 900". */
    public function priceLabel(bool $withCurrency = true): string
    {
        $currency = $withCurrency ? ($this->business?->currency ?? 'PKR').' ' : '';
        $amount = number_format($this->price(), 0);
        $unit = $this->priceUnit();

        return $unit ? "{$currency}{$amount} / {$unit}" : "{$currency}{$amount}";
    }

    /** e.g. "5-hour package" / "one-time charge". */
    public function packageLabel(): ?string
    {
        if (! $this->isFixed()) {
            return null;
        }

        if (! $this->package_minutes) {
            return 'one-time charge';
        }

        $minutes = (int) $this->package_minutes;

        return $minutes % 60 === 0
            ? ($minutes / 60).'-hour package'
            : $minutes.'-minute package';
    }

    /** Minimum length of a booking for this activity (0 = business default). */
    public function minimumMinutes(): int
    {
        return max(0, (int) ($this->min_minutes ?? 0));
    }

    public function maximumMinutes(): ?int
    {
        return $this->max_minutes ? (int) $this->max_minutes : null;
    }

    /** e.g. "+300 PKR / hour additional controller". */
    public function addonDescription(): ?string
    {
        if (! $this->addon_label || ! $this->addon_price) {
            return null;
        }

        $currency = $this->business?->currency ?? 'PKR';

        return '+'.number_format((float) $this->addon_price, 0).' '.$currency.' '.$this->addon_label;
    }

    public function isOnlineBookable(): bool
    {
        return $this->isBookable() && (bool) $this->is_online_bookable;
    }

    /**
     * Stored image when one was uploaded/selected, otherwise category-themed
     * artwork so every activity always has a relevant cover with no extra
     * setup. Falls back gracefully when a stored file has gone missing.
     */
    public function imageUrl(): ?string
    {
        if ($this->hasCustomImage()) {
            return Storage::disk('public')->url($this->image_path);
        }

        return $this->fallbackImageUrl();
    }

    /**
     * The default cover for this activity (used when there is no usable stored
     * file and by the <img onerror> fallback in the views). Real photographs are
     * preferred; the themed line-art is only a last resort for categories that
     * have no photo bundled.
     */
    public function fallbackImageUrl(): string
    {
        $slug = $this->category?->slug;

        $photos = [
            'snooker' => 'snooker.jpg',
            'ps5' => 'ps5.jpg',
            'pc-gaming' => 'pc-gaming.jpg',
            'table-tennis' => 'table-tennis.jpg',
            'account-packages' => 'account-packages.jpg',
        ];

        $photo = $photos[$slug] ?? null;

        if ($photo && is_file(public_path('img/activities/covers/'.$photo))) {
            return asset('img/activities/covers/'.$photo);
        }

        $known = ['snooker', 'pool', 'ps5', 'pc-gaming', 'gaming-zone', 'vip-rooms', 'table-tennis', 'account-packages'];
        $file = in_array($slug, $known, true) ? $slug : 'default';

        // Never emit a URL to artwork that is not actually shipped with the app.
        if (! is_file(public_path('img/activities/'.$file.'.svg'))) {
            $file = 'default';
        }

        return asset('img/activities/'.$file.'.svg');
    }

    /** True when the activity has its own stored image that exists on disk. */
    public function hasCustomImage(): bool
    {
        return (bool) $this->image_path
            && Storage::disk('public')->exists($this->image_path);
    }

    /** Effective default duration for a new booking (falls back to 60 minutes). */
    public function defaultDuration(): int
    {
        return max(15, (int) ($this->duration_minutes ?: 60));
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            'active' => 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
            'maintenance' => 'bg-amber-500/15 text-amber-300 border-amber-500/30',
            default => 'bg-zinc-500/15 text-zinc-300 border-zinc-600/40',
        };
    }
}
