<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 회원 승인 테이블
 */
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

            $table->string('enable')->nullable();

            // 회원 정보
            $table->string('email')->nullable();
            $table->string('name')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);

            // 승인 여부
            $table->string('auth')->nullable();
            $table->string('auth_date')->nullable(); // 승인일자
            $table->text('description')->nullable(); // 승인 내용

            // 회원인증을 처리한 AdminId
            $table->unsignedBigInteger('admin_id')->default(0);
            $table->unsignedBigInteger('manager_id')->default(0);

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
