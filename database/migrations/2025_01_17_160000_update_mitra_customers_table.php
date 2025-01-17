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
        Schema::table('mitra_customers', function (Blueprint $table) {
            $table->integer('limit_credit')->nullable()->change();
            $table->integer('top')->nullable()->change();
            $table->string('branch_code', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_customers', function (Blueprint $table) {
            $table->integer('limit_credit')->change();
            $table->integer('top')->change();
            $table->string('branch_code', 50)->change();
        });
    }
};
