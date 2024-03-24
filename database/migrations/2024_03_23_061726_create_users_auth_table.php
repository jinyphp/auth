<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersAuthTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_auth', function (Blueprint $table) {
            $table->id();

            // 회원인증 요청일자 : created_at
            $table->timestamps();

            // 사용자 번호
            $table->unsignedBigInteger('user_id')->default(0);

            $table->string('enable')->nullable();
            $table->string('auth')->nullable(); // 승인여부
            $table->string('auth_date')->nullable();
            $table->text('description')->nullable();

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
        Schema::dropIfExists('users_auth');
    }
}
