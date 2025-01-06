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
        Schema::create('mitra_marketing_orders', function (Blueprint $table) {
            $table->id();
            $table->string('code',155)->nullable()->index();
            $table->bigInteger('user_id')->nullable()->index();
            $table->string('account_code')->nullable()->index();
            $table->char('type',1)->nullable();
            $table->date('post_date')->nullable();
            $table->date('valid_date')->nullable();
            $table->char('type_delivery',1)->nullable();
            $table->date('delivery_date')->nullable();
            $table->char('delivery_schedule',1)->nullable();
            $table->char('payment_type',1)->nullable();
            $table->char('dp_type',1)->nullable();
            $table->text('note')->nullable();
            $table->decimal('total',20,5)->nullable();
            $table->decimal('tax',20,5)->nullable();
            $table->decimal('grandtotal',20,5)->nullable();
            $table->char('status',1)->nullable();
            $table->bigInteger('void_id')->nullable();
            $table->string('void_note')->nullable();
            $table->timestamp('void_date')->nullable();
            $table->bigInteger('delete_id')->nullable();
            $table->string('delete_note')->nullable();
            $table->bigInteger('done_id')->nullable();
            $table->timestamp('done_date')->nullable();
            $table->string('done_note')->nullable();
            $table->timestamps();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mitra_marketing_orders');
    }
};
