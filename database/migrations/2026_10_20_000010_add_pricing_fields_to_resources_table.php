<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rich activity pricing: hourly, per-minute and fixed package pricing, an
 * optional promotional price, per-activity minimum/maximum durations and an
 * optional paid add-on (e.g. an extra PS5 controller).
 *
 * hourly_rate / sale_price are always stored as the *hourly equivalent* so the
 * existing pricing engine keeps working; per-minute activities display that
 * amount divided by 60.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->string('pricing_type', 20)->default('hourly')->after('hourly_rate');
            $table->decimal('sale_price', 10, 2)->nullable()->after('pricing_type');
            $table->string('promo_label', 60)->nullable()->after('sale_price');
            $table->decimal('fixed_price', 10, 2)->nullable()->after('sale_price');
            $table->unsignedSmallInteger('package_minutes')->nullable()->after('fixed_price');
            $table->unsignedSmallInteger('min_minutes')->nullable()->after('duration_minutes');
            $table->unsignedSmallInteger('max_minutes')->nullable()->after('min_minutes');
            $table->string('addon_label', 80)->nullable()->after('promo_label');
            $table->decimal('addon_price', 10, 2)->nullable()->after('addon_label');
        });
    }

    public function down(): void
    {
        Schema::table('resources', function (Blueprint $table) {
            $table->dropColumn([
                'pricing_type', 'sale_price', 'promo_label', 'fixed_price', 'package_minutes',
                'min_minutes', 'max_minutes', 'addon_label', 'addon_price',
            ]);
        });
    }
};
