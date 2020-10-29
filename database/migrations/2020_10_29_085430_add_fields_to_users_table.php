<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('surname')->after('name');
            $table->string('curriculumvitae')->after('remember_token')->nullable();
            $table->string('disciplinary')->after('remember_token')->nullable();
            $table->string('affiliation')->after('remember_token')->nullable();
            $table->string('city')->after('remember_token')->nullable();
            $table->string('country')->after('remember_token')->nullable();
            $table->string('gender')->after('remember_token')->nullable();
            $table->string('title')->after('remember_token')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}
