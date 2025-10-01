<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 비밀번호 재설정 토큰 관리 테이블
     */
    public function up(): void
    {
        Schema::create('auth_password_resets', function (Blueprint $table) {
            $table->id();
            $table->string('email')->index(); // 이메일
            $table->string('token'); // 재설정 토큰
            $table->string('ip_address', 45)->nullable(); // 요청 IP
            $table->text('user_agent')->nullable(); // User Agent
            $table->boolean('used')->default(false); // 사용 여부
            $table->timestamp('expires_at'); // 만료 시간
            $table->timestamp('created_at')->nullable();
            $table->timestamp('used_at')->nullable(); // 사용 시간

            // 인덱스
            $table->index('token');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_password_resets');
    }
};