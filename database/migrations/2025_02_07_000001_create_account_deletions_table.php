<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 회원 탈퇴 신청 및 처리 테이블
     */
    public function up(): void
    {
        Schema::create('account_deletions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // 사용자 ID
            $table->uuid('user_uuid'); // 사용자 UUID
            $table->string('email')->index(); // 이메일
            $table->string('name'); // 이름

            // 탈퇴 신청 정보
            $table->text('reason')->nullable(); // 탈퇴 사유
            $table->enum('deletion_type', ['user_request', 'admin', 'auto'])->default('user_request'); // 탈퇴 유형
            $table->timestamp('requested_at'); // 신청 시간
            $table->string('requested_ip', 45)->nullable(); // 신청 IP

            // 승인/처리 정보
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->unsignedBigInteger('approved_by')->nullable(); // 승인자 ID
            $table->timestamp('approved_at')->nullable(); // 승인 시간
            $table->text('admin_note')->nullable(); // 관리자 메모

            // 자동 삭제 정보
            $table->integer('auto_delete_days')->default(30); // 자동 삭제 기간 (일)
            $table->timestamp('auto_delete_at')->nullable(); // 자동 삭제 예정일
            $table->boolean('auto_deleted')->default(false); // 자동 삭제 여부

            // 실제 삭제 정보
            $table->boolean('data_deleted')->default(false); // 데이터 삭제 완료 여부
            $table->timestamp('deleted_at')->nullable(); // 실제 삭제 시간
            $table->json('deleted_tables')->nullable(); // 삭제된 테이블 목록

            // 복구 정보 (선택적)
            $table->boolean('backup_created')->default(false); // 백업 생성 여부
            $table->string('backup_path')->nullable(); // 백업 파일 경로
            $table->timestamp('backup_expires_at')->nullable(); // 백업 만료일

            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('user_uuid');
            // email 인덱스는 18번 라인에서 이미 생성됨
            $table->index('status');
            $table->index('auto_delete_at');
            $table->index('requested_at');
            $table->index(['status', 'auto_delete_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_deletions');
    }
};