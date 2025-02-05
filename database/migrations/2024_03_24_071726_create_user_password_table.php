<?php
/**
 * 패스워드 유효기간 만기 체크
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserPasswordTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_password', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 회원정보
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('name')->nullable();
            $table->string('email')->nullable();

            // 패스워드 정보
            $table->string('password')->nullable();
            $table->string('enable')->nullable();
            $table->string('expire')->nullable(); // 만기일자

            $table->text('description')->nullable();

            // 관리자가 직접 연기를 하는 경우, 관리자 아이디저장
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
        Schema::dropIfExists('user_password');
    }
}
