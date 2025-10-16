<?php
/**
 * 사용자 승인 로그 테이블
 * 회원가입 승인/거부 이력을 기록합니다.
 */
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->timestamps();

            // 표준화된 샤딩 관계 필드
            $table->bigInteger('user_id')->unsigned();
            $table->string('user_uuid', 36)->nullable();
            $table->integer('shard_id')->nullable();
            $table->string('email')->nullable();
            $table->string('name')->nullable();

            // 승인 관련 필드
            $table->enum('action', ['auto_approved', 'approved', 'rejected', 'pending'])
                  ->comment('승인 액션 (auto_approved: 자동승인, approved: 관리자승인, rejected: 거부, pending: 대기)');
            $table->text('comment')->nullable()->comment('승인/거부 사유');

            // 관리자 정보
            $table->bigInteger('admin_user_id')->unsigned()->nullable()->comment('승인한 관리자 ID (자동승인시 null)');
            $table->string('admin_user_name')->nullable()->comment('승인한 관리자명');

            // 요청 정보
            $table->string('ip_address', 45)->nullable()->comment('요청 IP 주소');
            $table->text('user_agent')->nullable()->comment('User Agent');

            // 처리 시간
            $table->timestamp('processed_at')->nullable()->comment('승인/거부 처리 시간');

            // 인덱스
            $table->index('user_id');
            $table->index('user_uuid');
            $table->index('action');
            $table->index('admin_user_id');
            $table->index('processed_at');
            $table->index(['user_id', 'action']);
            $table->index(['user_uuid', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_approval_logs');
    }
};