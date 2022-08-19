<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExpireToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('auth')->nullable();
            $table->string('expire')->nullable();
            $table->string('sleeper')->nullable(); // 휴면계정 관리
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
            $table->string('auth')->nullable();
            $table->string('expire')->nullable();
            $table->string('sleeper')->nullable();
        });
    }
}
