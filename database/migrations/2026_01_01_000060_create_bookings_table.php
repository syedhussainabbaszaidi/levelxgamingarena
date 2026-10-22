<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resource_id')->constrained()->restrictOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // created by (staff/admin/online user)

            $table->string('code', 20)->unique();          // BK-2026-000123
            $table->enum('source', ['counter', 'online', 'admin'])->default('counter');
            $table->enum('status', [
                'pending',      // online, awaiting approval
                'confirmed',    // approved / upcoming
                'checked_in',   // session live
                'completed',
                'cancelled',
                'no_show',
            ])->default('pending')->index();

            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('duration_minutes');

            $table->decimal('estimated_cost', 12, 2)->default(0);
            $table->decimal('final_cost', 12, 2)->default(0);
            $table->unsignedInteger('extended_minutes')->default(0);

            $table->dateTime('checked_in_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->text('notes')->nullable();
            $table->timestamps();

            // Overlap prevention index
            $table->index(['resource_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
