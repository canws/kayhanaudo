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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('sku');
            $table->string('product_title');
            $table->string('slug');
            $table->text('categories');
            $table->longText('product_content');
            $table->integer('featured_image');
            $table->longText('gallery_images');
            $table->string('product_type');
            $table->longText('short_discription');
            $table->string('regular_price');
            $table->string('wholesaler_price');
            $table->string('stock_status');
            $table->integer('shipping_class');
            $table->string('dimensions_length');
            $table->string('dimensions_width');
            $table->string('dimensions_height');
            $table->string('weight');
            $table->text('video_link');
            $table->longText('product_specification');
            $table->integer('make');
            $table->integer('model');
            $table->integer('model_year');
            $table->string('status');
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
        Schema::dropIfExists('products');
    }
};
