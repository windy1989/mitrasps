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
            $table->string('document_no',155)->nullable()->after('valid_date')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mitra_marketing_orders', function (Blueprint $table) {
            $table->dropColumn('document_no');
        });
    }
};
