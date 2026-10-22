<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resource_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            // day: 0=Sun ... 6=Sat
            $table->unsignedTinyInteger('day_of_week')->nullable(); // null = every day
            $table->time('start_time');                             // window start
            $table->time('end_time');                               // window end
            $table->boolean('overnight')->default(false);           // end_time is next day
            $table->decimal('hourly_rate', 10, 2);
            $table->unsignedTinyInteger('min_minutes')->default(30);
            $table->unsignedTinyInteger('slot_minutes')->default(30); // granularity
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('priority')->default(0); // higher wins
            $table->timestamps();

            $table->index(['business_id', 'is_active', 'priority']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
