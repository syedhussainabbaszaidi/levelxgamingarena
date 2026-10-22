<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->unsignedSmallInteger('slot_minutes')->default(60)->after('currency');
            $table->unsignedSmallInteger('min_duration_minutes')->default(30)->after('slot_minutes');
            $table->unsignedSmallInteger('max_advance_days')->default(30)->after('min_duration_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('businesses', function (Blueprint $table) {
            $table->dropColumn(['slot_minutes', 'min_duration_minutes', 'max_advance_days']);
        });
    }
};
