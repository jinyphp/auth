<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 사용자 활동 로그 테이블
     */
    public function up(): void
    {
        Schema::create('auth_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // 사용자 ID
            $table->string('action'); // 활동 유형
            $table->string('description')->nullable(); // 활동 설명
            $table->string('model_type')->nullable(); // 모델 타입
            $table->unsignedBigInteger('model_id')->nullable(); // 모델 ID
            $table->json('old_values')->nullable(); // 이전 값
            $table->json('new_values')->nullable(); // 새로운 값
            $table->string('ip_address', 45)->nullable(); // IP 주소
            $table->text('user_agent')->nullable(); // User Agent
            $table->string('session_id')->nullable(); // 세션 ID
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamp('performed_at'); // 수행 시간
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('action');
            $table->index(['model_type', 'model_id']);
            $table->index('performed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_activity_logs');
    }
};