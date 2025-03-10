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
            $table->integer('top_internal')->nullable()->after('top');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_customers', function (Blueprint $table) {
            $table->dropColumn('top_internal');
        });
    }
};
