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
            $table->string('delivery_province_code',25)->nullable()->change();
            $table->string('delivery_city_code',25)->nullable()->change();
            $table->string('delivery_district_code',25)->nullable()->change();
            $table->index(['delivery_province_code','delivery_city_code','delivery_district_code'],'delivery_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_marketing_orders', function (Blueprint $table) {
            //
        });
    }
};
