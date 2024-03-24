<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserSleeperTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_sleeper', function (Blueprint $table) {
            $table->id();

            // 회원인증 요청일자 : created_at
            $table->timestamps();

            // 사용자 번호
            $table->unsignedBigInteger('user_id')->default(0);

            $table->string('enable')->nullable();
            $table->string('sleeper')->nullable(); // 휴면상태
            $table->text('description')->nullable();


            $table->string('expire_date')->nullable();

            // 회원인증을 처리한 AdminId
            $table->unsignedBigInteger('admin_id')->default(0);

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_sleeper');
    }
}
