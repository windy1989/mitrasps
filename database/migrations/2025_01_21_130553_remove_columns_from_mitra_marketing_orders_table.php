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
            $table->dropColumn('type','dp_type','delivery_schedule');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_marketing_orders', function (Blueprint $table) {
            $table->char('type',1)->nullable();
            $table->char('dp_type',1)->nullable();
            $table->char('delivery_schedule',1)->nullable();
        });
    }
};
