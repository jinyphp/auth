<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * 소셜 로그인 인덱스 테이블 생성
     *
     * 샤딩된 환경에서 Provider + ProviderId로 사용자 UUID를 빠르게 조회하기 위한 인덱스
     */
    public function up(): void
    {
        if (Schema::hasTable('social_login_index')) {
            return;
        }

        Schema::create('social_login_index', function (Blueprint $table) {
            $table->id();
            $table->string('provider'); // google, github, etc.
            $table->string('provider_id'); // sub, id, etc.
            $table->string('uuid', 36); // 사용자 UUID
            $table->timestamps();

            // 인덱스
            // 특정 소셜 계정으로 UUID 조회
            $table->unique(['provider', 'provider_id']);
            $table->index('uuid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_login_index');
    }
};
