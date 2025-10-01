<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 인증 세션 관리 테이블
     */
    public function up(): void
    {
        Schema::create('auth_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id')->unique(); // 세션 ID
            $table->unsignedBigInteger('user_id'); // 사용자 ID
            $table->string('ip_address', 45)->nullable(); // IP 주소
            $table->text('user_agent')->nullable(); // User Agent
            $table->text('payload'); // 세션 데이터
            $table->integer('last_activity'); // 마지막 활동 시간
            $table->timestamp('expires_at')->nullable(); // 만료 시간
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('last_activity');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_sessions');
    }
};