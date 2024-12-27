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
        Schema::create('user_log_daily', function (Blueprint $table) {
            $table->id();

            // 회원인증 요청일자 : created_at
            $table->timestamps();

            $table->string('year')->nullable();
            $table->string('month')->nullable();
            $table->string('day')->nullable();

            $table->unsignedBigInteger('cnt')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_log_daily');
    }

};
