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
            $table->integer('limit_credit')->change();
            $table->integer('top')->change();
            $table->string('branch_code', 50)->change();
            $table->dropColumn('npwp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_customers', function (Blueprint $table) {
            $table->string('limit_credit')->nullable()->change();
            $table->string('top')->nullable()->change();
            $table->string('branch_code')->nullable()->change();
            $table->string('npwp')->nullable()->after('top');
        });
    }
};
