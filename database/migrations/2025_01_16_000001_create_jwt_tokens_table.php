<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * JWT 토큰 관리 테이블
     */
    public function up(): void
    {
        Schema::create('jwt_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // 사용자 ID
            $table->string('token_id')->unique(); // JWT JTI (고유 토큰 ID)
            $table->enum('token_type', ['access', 'refresh'])->default('access'); // 토큰 유형
            $table->text('token_hash')->nullable(); // 토큰 해시값
            $table->json('claims')->nullable(); // JWT 클레임
            $table->json('scopes')->nullable(); // 토큰 스코프
            $table->string('ip_address', 45)->nullable(); // 발급 IP
            $table->text('user_agent')->nullable(); // User Agent
            $table->boolean('revoked')->default(false); // 폐기 여부
            $table->timestamp('issued_at'); // 발급 시간
            $table->timestamp('expires_at'); // 만료 시간
            $table->timestamp('last_used_at')->nullable(); // 마지막 사용 시간
            $table->timestamp('revoked_at')->nullable(); // 폐기 시간
            $table->timestamps();

            // 인덱스
            $table->index('user_id');
            $table->index('token_id');
            $table->index('token_type');
            $table->index('revoked');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jwt_tokens');
    }
};