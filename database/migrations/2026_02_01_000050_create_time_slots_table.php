<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_slots', function (Blueprint $table) {
            $table->id();
            // null resource_id = shared slot preset available to every activity
            $table->foreignId('resource_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('label');                          // "Evening 6–8 PM"
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedTinyInteger('day_of_week')->nullable(); // null = every day (0=Sun..6=Sat)
            $table->unsignedSmallInteger('slot_minutes')->default(60);
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['resource_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_slots');
    }
};
