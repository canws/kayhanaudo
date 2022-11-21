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
        Schema::create('coupons', function (Blueprint $table) {
            $table->id();
            $table->string('coupon_code');
            $table->text('description');
            $table->string('coupon_amount');
            $table->string('discount_type');
            $table->integer('free_shipping');
            $table->date('expiry_date');
            $table->string('minimum_spend');
            $table->string('maximum_spend');
            $table->integer('individual_use');
            $table->integer('exclude_sale_items');
            $table->longText('includeProductIds');
            $table->longText('excludeProductIds');
            $table->string('usage_limit');
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
        Schema::dropIfExists('coupons');
    }
};
