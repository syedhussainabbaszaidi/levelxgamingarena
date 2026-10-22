<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    public const METHODS = ['cash', 'card', 'online', 'wallet'];
    public const KINDS = ['advance', 'full', 'partial', 'extension', 'addon'];

    protected $fillable = [
        'business_id', 'booking_id', 'customer_id', 'user_id',
        'code', 'amount', 'method', 'kind', 'notes',
    ];

    protected $casts = ['amount' => 'decimal:2'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(Business::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
