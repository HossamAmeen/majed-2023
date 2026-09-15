<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillsProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    { 
        Schema::create('bills_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_price');
            $table->integer('selling_price');

            $table->integer('discount');
            $table->integer('quantity');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('bill_id');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade')->onUpdate("cascade");
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('cascade')->onUpdate("cascade");
            $table->timestamps();
            $table->softDeletes();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bills_products');
    }
}
