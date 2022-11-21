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
        Schema::create('zoone_methods', function (Blueprint $table) {
            $table->id();
            $table->integer('zoone_id');
            $table->string('shipping_method');
            $table->string('method_title');
            $table->string('shipping_cost');
            $table->text('description');
            $table->string('free_shipping_requires');
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
        Schema::dropIfExists('zoone_methods');
    }
};
