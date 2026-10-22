<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');                    // "Snooker Table 1", "PS5 Booth 3"
            $table->string('code')->nullable();        // short code e.g. SNK-1
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('capacity')->default(1);
            $table->decimal('hourly_rate', 10, 2)->default(0);   // fallback rate
            $table->enum('status', ['active', 'inactive', 'maintenance'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['business_id', 'category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resources');
    }
};
