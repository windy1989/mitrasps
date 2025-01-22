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
        Schema::table('mitra_customer_billings', function (Blueprint $table) {
            $table->string('notes')->nullable()->change();
            $table->string('npwp')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_customer_billings', function (Blueprint $table) {
            $table->string('notes')->change();
            $table->string('npwp')->change();
        });
    }
};
