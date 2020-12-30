<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReviewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->integer('field_1');
            $table->integer('field_2');
            $table->integer('field_3');
            $table->longText('review')->nullable();
            $table->bigInteger('post')->unsigned();
            $table->bigInteger('supervisor')->unsigned();
            $table->timestamps();
        });

        Schema::table('reviews', function(Blueprint $table) {
            $table->foreign('supervisor')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('reviews', function(Blueprint $table) {
            $table->foreign('post')->references('id')->on('posts')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reviews');
    }
}
