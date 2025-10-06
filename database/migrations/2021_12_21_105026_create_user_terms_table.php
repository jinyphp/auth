<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 이용약관
 */
class CreateUserTermsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_terms', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 활성화/비활성화
            $table->boolean('enable')->default(true);
            // 필수/선택 약관
            $table->boolean('required')->default(true);

            $table->string('title');
            $table->string('slug')->nullable();
            $table->string('blade')->nullable();

            $table->text('content')->nullable();
            $table->integer('pos')->default(1);

            $table->text('description')->nullable();

            // 버전 관리
            $table->string('version', 50)->nullable()->comment('약관 버전 (예: 1.0.0)');

            // 유효기간
            $table->timestamp('valid_from')->nullable()->comment('약관 시작일');
            $table->timestamp('valid_to')->nullable()->comment('약관 종료일');

            // 작업자ID
            $table->string('manager')->nullable();
            $table->unsignedBigInteger('user_id')->default(0);

            // 동의한 회원수
            $table->integer('users')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_terms');
    }
}
