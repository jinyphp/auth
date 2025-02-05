<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_grade', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            $table->string('enable')->default(1);
            $table->string('name')->nullable();
            $table->text('description')->nullable();

            $table->integer('users')->default(0);
            // 최대 사용자 수
            $table->string('max_users')->nullable();


            // 가입 포인트
            $table->string('welcome_point')->nullable();

            // 추천 포인트
            $table->string('recommend_point')->nullable();

            // 가입비용
            $table->string('register_fee')->nullable();

            // 월 유지비용
            $table->string('monthly_fee')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_grade');
    }
};
