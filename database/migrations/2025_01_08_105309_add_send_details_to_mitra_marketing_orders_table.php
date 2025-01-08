<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('mitra_marketing_orders', function (Blueprint $table) {
            $table->string('delivery_address',500)->nullable()->after('delivery_schedule');
            $table->bigInteger('delivery_province_code')->nullable()->index()->after('delivery_address');
            $table->bigInteger('delivery_city_code')->nullable()->index()->after('delivery_province_code');
            $table->bigInteger('delivery_district_code')->nullable()->index()->after('delivery_city_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_marketing_orders', function (Blueprint $table) {
            $table->dropColumn('delivery_address','delivery_province_code','delivery_city_code','delivery_district_code');
        });
    }
};
