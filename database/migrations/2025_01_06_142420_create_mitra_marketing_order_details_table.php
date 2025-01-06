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
        Schema::create('mitra_marketing_order_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('mitra_marketing_order_id')->nullable()->index();
            $table->bigInteger('item_id')->nullable()->index();
            $table->decimal('qty',20,5)->nullable();
            $table->decimal('price',20,5)->nullable();
            $table->decimal('percent_tax',20,5)->nullable();
            $table->decimal('final_price',20,5)->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->text('note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_marketing_order_details');
    }
};
