<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingFieldValue extends Model
{
    protected $fillable = ['booking_id', 'booking_field_id', 'value'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(BookingField::class, 'booking_field_id');
    }
}
