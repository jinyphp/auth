<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * IP 화이트리스트 관리 테이블
     */
    public function up(): void
    {
        Schema::create('auth_ip_whitelist', function (Blueprint $table) {
            $table->id();
            $table->string('ip_address', 45); // IP 주소 또는 CIDR
            $table->string('description')->nullable(); // 설명
            $table->enum('type', ['user', 'admin', 'api', 'all'])->default('all'); // 적용 유형
            $table->boolean('is_active')->default(true); // 활성화 여부
            $table->timestamp('valid_from')->nullable(); // 유효 시작일
            $table->timestamp('valid_until')->nullable(); // 유효 종료일
            $table->unsignedBigInteger('created_by')->nullable(); // 생성자
            $table->json('metadata')->nullable(); // 추가 메타데이터
            $table->timestamps();

            // 인덱스
            $table->index('ip_address');
            $table->index('type');
            $table->index('is_active');
            $table->index(['valid_from', 'valid_until']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_ip_whitelist');
    }
};