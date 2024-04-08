<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserLogCountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_log_count', function (Blueprint $table) {
            $table->id();

            // 회원인증 요청일자 : created_at
            $table->timestamps();

            // 사용자 번호
            $table->unsignedBigInteger('user_id')->default(0);

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
        Schema::dropIfExists('user_log_count');
    }
}
