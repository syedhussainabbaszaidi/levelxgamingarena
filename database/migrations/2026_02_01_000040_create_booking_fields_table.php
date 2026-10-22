<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('booking_fields', function (Blueprint $table) {
            $table->id();
            // null resource_id = applies to every gaming activity
            $table->foreignId('resource_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key', 50);                       // player_name
            $table->string('label');                         // "Player name"
            $table->string('type', 20)->default('text');     // text|textarea|number|select|checkbox|email|tel|date|time
            $table->string('placeholder')->nullable();
            $table->json('options')->nullable();             // select choices
            $table->string('default_value')->nullable();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_visible')->default(true);    // shown in customer booking form
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['resource_id', 'key']);
            $table->index(['resource_id', 'is_active', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_fields');
    }
};
