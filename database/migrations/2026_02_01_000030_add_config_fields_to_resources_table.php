<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->string('image_path')->nullable()->after('code');
            $table->unsignedSmallInteger('duration_minutes')->default(60)->after('capacity');
            $table->boolean('is_online_bookable')->default(true)->after('status');
            $table->boolean('featured')->default(false)->after('is_online_bookable');
            $table->json('meta')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn(['image_path', 'duration_minutes', 'is_online_bookable', 'featured', 'meta']);
        });
    }
};
