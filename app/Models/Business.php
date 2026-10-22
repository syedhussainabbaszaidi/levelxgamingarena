<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends Model
{
    protected $fillable = [
        'name', 'slug', 'parent_id', 'address', 'phone',
        'opens_at', 'closes_at', 'overnight', 'timezone', 'currency',
        'slot_minutes', 'min_duration_minutes', 'max_advance_days', 'is_active',
    ];

    protected $casts = [
        'opens_at' => 'datetime:H:i',
        'closes_at' => 'datetime:H:i',
        'overnight' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function resources(): HasMany
    {
        return $this->hasMany(Resource::class);
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

    public function pricingRules(): HasMany
    {
        return $this->hasMany(PricingRule::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public static function primary(): ?self
    {
        return static::where('parent_id', null)->orderBy('id')->first();
    }

    /**
     * The venue runs around the clock. Opening and closing at the same time is
     * how a 24/7 schedule is stored, so the booking engine keeps generating a
     * full-day slot grid without a separate flag.
     */
    public function is24Hours(): bool
    {
        return $this->opens_at !== null
            && $this->closes_at !== null
            && $this->opens_at->format('H:i') === $this->closes_at->format('H:i');
    }

    /** Human-readable opening hours used across the public pages. */
    public function hoursLabel(): string
    {
        if ($this->is24Hours()) {
            return 'Open 24/7';
        }

        return $this->opens_at->format('g:i A').' – '.$this->closes_at->format('g:i A')
            .($this->overnight ? ' (next day)' : '');
    }

    /** WhatsApp number in wa.me format (international, digits only). */
    public function whatsAppNumber(): string
    {
        $number = $this->phone ?: config('site.whatsapp_number', '923422631057');

        return preg_replace('/\D+/', '', (string) $number);
    }

    /** Deep link that opens WhatsApp with a prefilled enquiry message. */
    public function whatsAppUrl(?string $message = null): string
    {
        $message = $message ?: config('site.whatsapp_message');
        $number = $this->whatsAppNumber();

        return 'https://wa.me/'.($number !== '' ? $number : '')
            .'?text='.rawurlencode($message);
    }
}
