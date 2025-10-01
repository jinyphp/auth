<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 로그인 시도 추적 테이블
     */
    public function up(): void
    {
        Schema::create('auth_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('email')->nullable(); // 시도한 이메일
            $table->string('username')->nullable(); // 시도한 사용자명
            $table->string('ip_address', 45); // IP 주소
            $table->text('user_agent')->nullable(); // User Agent
            $table->boolean('successful')->default(false); // 성공 여부
            $table->string('failure_reason')->nullable(); // 실패 사유
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamp('attempted_at'); // 시도 시간
            $table->timestamps();

            // 인덱스
            $table->index('email');
            $table->index('ip_address');
            $table->index('successful');
            $table->index('attempted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_login_attempts');
    }
};