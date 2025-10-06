<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 계정 잠금 관리 테이블
     *
     * 비밀번호 오류 횟수에 따른 단계별 접속 제한 관리
     */
    public function up(): void
    {
        Schema::create('account_lockouts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // 사용자 ID
            $table->uuid('user_uuid')->nullable(); // 사용자 UUID (샤딩 지원)
            $table->string('email')->index(); // 이메일
            $table->string('ip_address', 45)->nullable(); // IP 주소

            // 잠금 정보
            $table->integer('lockout_level')->default(1); // 잠금 단계 (1,2,3)
            $table->integer('failed_attempts')->default(0); // 실패 횟수
            $table->integer('lockout_duration')->default(15); // 잠금 시간 (분)

            // 잠금 상태
            $table->enum('status', ['locked', 'unlocked', 'permanent'])->default('locked');
            $table->timestamp('locked_at')->nullable(); // 잠금 시작 시간
            $table->timestamp('unlocks_at')->nullable(); // 자동 해제 시간

            // 해제 정보
            $table->boolean('requires_admin_unlock')->default(false); // 관리자 해제 필요
            $table->unsignedBigInteger('unlocked_by')->nullable(); // 해제 처리자
            $table->timestamp('unlocked_at')->nullable(); // 해제 시간
            $table->text('unlock_reason')->nullable(); // 해제 사유

            // 추가 정보
            $table->text('notes')->nullable(); // 관리자 메모
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('user_uuid');
            // email 인덱스는 20번 라인에서 이미 생성됨
            $table->index('ip_address');
            $table->index('status');
            $table->index('unlocks_at');
            $table->index('requires_admin_unlock');
            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_lockouts');
    }
};