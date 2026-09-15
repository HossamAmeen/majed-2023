<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!Schema::hasTable('products')){
            Schema::create('products', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name');
                $table->double('purchasing_price')->nullable();
                $table->double('selling_price')->nullable();
                $table->double('total_price')->nullable();
                $table->integer('quantity')->nullable();
                $table->unsignedBigInteger('code')->nullable();
                $table->double('discount')->nullable();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('category_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade')->onUpdate("cascade");
                $table->foreign('category_id')->references('id')->on('categories')->onDelete('cascade')->onUpdate("cascade");

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
        Schema::dropIfExists('products');
    }
}
