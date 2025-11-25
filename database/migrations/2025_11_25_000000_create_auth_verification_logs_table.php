<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 이메일 인증 관련 액션 로그 테이블 생성
     *
     * - resend, force_verify, force_unverify 등 인증 상태와 관련된 동작을 기록합니다.
     * - mail 패키지의 메일 로그와 별도로 인증 상태의 이력 확인을 위해 유지합니다.
     */
    public function up(): void
    {
        Schema::create('auth_verification_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('email')->index();
            $table->unsignedBigInteger('shard_id')->nullable()->index();
            $table->string('action', 32)->index(); // resend | force_verify | force_unverify
            $table->string('status', 16)->default('pending')->index(); // pending | sent | success | failed
            $table->string('subject')->nullable();
            $table->text('message')->nullable();
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_verification_logs');
    }
};


