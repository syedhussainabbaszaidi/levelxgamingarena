<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');                       // "Zone A Gaming Lounge", "Snooker Club"
            $table->string('slug')->unique();             // zone-a-gaming-lounge
            $table->foreignId('parent_id')->nullable()->constrained('businesses')->nullOnDelete();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->time('opens_at')->default('10:00');
            $table->time('closes_at')->default('02:00');  // may pass midnight
            $table->boolean('overnight')->default(false); // closes_at on the next day
            $table->string('timezone', 50)->default('Asia/Karachi');
            $table->string('currency', 8)->default('PKR');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('businesses');
    }
};
