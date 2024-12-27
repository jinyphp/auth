<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('user_log_status', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('user_id')->nullable();

            $table->string('key')->nullable(); // 키이름
            $table->string('value')->nullable(); // 값
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_log_status');
    }
};
