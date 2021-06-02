<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('type');
            $table->string('authors')->nullable();
            $table->string('start');
            $table->string('end');
            $table->longText('description');
            $table->bigInteger('room')->unsigned();
            $table->bigInteger('edition')->unsigned();
            $table->boolean('active');
            $table->timestamps();
        });

        Schema::table('events', function(Blueprint $table) {
            $table->foreign('room')->references('id')->on('rooms')->onDelete('cascade')->onUpdate('cascade');
        });

        Schema::table('events', function(Blueprint $table) {
            $table->foreign('edition')->references('id')->on('editions')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('events');
    }
}
