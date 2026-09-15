<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfigrationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('configrations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('title')->nullable();
            $table->string('manager')->nullable();
            $table->string('en_title')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->text('description')->nullable();
            $table->text('en_description')->nullable();
            $table->text('home_description')->nullable();
            $table->text('en_home_description')->nullable();
            $table->text('descriptionPoint')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone2')->nullable();
            $table->string('whatsapp')->nullable();
            $table->string('video')->nullable();
            $table->string('video2')->nullable();
            $table->string('youtube')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();

            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
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
        Schema::dropIfExists('configrations');
    }
}
