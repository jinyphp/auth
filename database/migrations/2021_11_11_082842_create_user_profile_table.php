<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserProfileTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('userProfile', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('country')->nullable();
            $table->string('language')->nullable();
            $table->string('timezone')->nullable();

            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();

            $table->string('phone')->nullable();
            $table->string('mobile')->nullable();
            $table->string('fax')->nullable();

            $table->string('post')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('userProfile', function (Blueprint $table) {
            Schema::dropIfExists('userProfile');
        });
    }
}
