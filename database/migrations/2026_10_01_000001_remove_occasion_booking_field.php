<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The "Occasion" field was removed from the booking flow. Booking fields
     * drive both the customer form and its validation, so deleting the row
     * removes it from the UI and the backend rules in one step.
     */
    public function up(): void
    {
        DB::table('booking_fields')->where('key', 'occasion')->delete();
    }

    public function down(): void
    {
        // Non-destructive: the field is re-created by BookingConfigSeeder if needed.
    }
};
