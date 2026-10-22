<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Payment status upgrade:
 * - bookings.payment_status (unpaid|paid) is the single source of truth for
 *   revenue accounting (Today's Revenue / Total Payment).
 * - paid_at + payment_method record when/how the booking was settled.
 * - Legacy no_show/checked_in rows map to cancelled/confirmed so the app only
 *   reads/writes the four admin-facing statuses (the DB enum keeps the legacy
 *   values for safe rollbacks).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->enum('payment_status', ['unpaid', 'paid'])->default('unpaid')->index()->after('status');
            $table->dateTime('paid_at')->nullable()->after('payment_status');
            $table->string('payment_method', 20)->nullable()->after('paid_at');
        });

        // Backfill: bookings that already have recorded payments count as paid.
        DB::statement(
            'UPDATE bookings SET payment_status = CASE WHEN EXISTS (
                SELECT 1 FROM payments WHERE payments.booking_id = bookings.id
            ) THEN \'paid\' ELSE \'unpaid\' END'
        );
        DB::statement(
            'UPDATE bookings SET paid_at = (
                SELECT MAX(payments.created_at) FROM payments WHERE payments.booking_id = bookings.id
            ) WHERE payment_status = \'paid\''
        );

        // Legacy walk-in flow (counter check-in) folds into 'confirmed'.
        DB::table('bookings')->where('status', 'checked_in')->update(['status' => 'confirmed']);
        // No-show has no admin action anymore; treat as cancelled.
        DB::table('bookings')->where('status', 'no_show')->update(['status' => 'cancelled']);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn(['payment_status', 'paid_at', 'payment_method']);
        });
    }
};
