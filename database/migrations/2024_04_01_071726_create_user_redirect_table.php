<?php
/**
 * 사용자 로그인 리다이렉트
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRedirectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_redirect', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 회원id
            $table->unsignedBigInteger('user_id')->default(0);
            $table->string('email')->nullable();

            $table->string('enable')->nullable();
            $table->string('redirect')->nullable(); //


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
        Schema::dropIfExists('user_redirect');
    }
}
