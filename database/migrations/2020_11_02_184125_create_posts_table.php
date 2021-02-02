<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('authors');
            $table->bigInteger('template')->unsigned();
            $table->bigInteger('category')->unsigned();
            $table->bigInteger('created')->unsigned();
            $table->bigInteger('edit')->unsigned();
            $table->string('state');
            $table->bigInteger('supervisor')->unsigned()->nullable();
            $table->longText('tags')->nullable();
            $table->longText('pdf')->nullable();
            $table->longText('field_1')->nullable();
            $table->longText('field_2')->nullable();
            $table->longText('field_3')->nullable();
            $table->longText('field_4')->nullable();
            $table->longText('field_5')->nullable();
            $table->longText('field_6')->nullable();
            $table->longText('field_7')->nullable();
            $table->longText('field_8')->nullable();
            $table->longText('field_9')->nullable();
            $table->dateTime('latest_modify');
            $table->timestamps();
        });



        Schema::table('posts', function(Blueprint $table) {
            $table->foreign('created')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('posts', function(Blueprint $table) {
            $table->foreign('edit')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('posts', function(Blueprint $table) {
            $table->foreign('category')->references('id')->on('categories')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('posts', function(Blueprint $table) {
            $table->foreign('template')->references('id')->on('templates')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('posts', function(Blueprint $table) {
            $table->foreign('supervisor')->references('id')->on('users')->onDelete('cascade')->onUpdate('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
}
