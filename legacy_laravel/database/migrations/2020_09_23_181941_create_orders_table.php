<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('orders')){
        Schema::create('orders', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('product_price');
            $table->double('price');
            $table->integer('quantity');
            $table->date('date');
            $table->string('status')->default('sold-out');
            $table->string('product_name');
            $table->integer('discount');
            
            $table->unsignedBigInteger('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null')->onUpdate("set null");

            $table->bigInteger('bill_id')->unsigned()->nullable();
            $table->foreign('bill_id')->references('id')->on('bills')->onDelete('set null');

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();
        });
        }
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
}
