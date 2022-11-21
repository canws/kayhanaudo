<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('order_id');
            $table->string('transaction_id');
            $table->string('customer_email');
            $table->string('amount');
            $table->string('currency');
            $table->string('payment_mode');
            $table->string('payment_date');
            $table->string('discount_amount');
            $table->string('shipping_cost');
            $table->string('shipping_method');
            $table->string('status');
            $table->text('billing_address');
            $table->text('shipping_address');
            $table->text('payer_details');
            $table->text('ordernote');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
