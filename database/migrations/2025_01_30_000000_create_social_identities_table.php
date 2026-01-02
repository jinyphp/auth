<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 소셜 로그인 식별자 테이블 생성 Migration
 *
 * 이 migration은 소셜 로그인(OAuth) 정보를 사용자와 분리하여 저장하는 테이블을 생성합니다.
 * 기존에는 users 테이블에 provider, provider_id가 있었으나, 
 * 한 사용자가 여러 소셜 계정을 연동하거나 다중 소셜 로그인을 지원하기 위해 1:N 관계로 분리합니다.
 *
 * 주요 기능:
 * - 소셜 로그인 정보(provider, provider_id) 저장
 * - 사용자 UUID와 연결 (샤딩 지원)
 * - OAuth 토큰 및 메타데이터 저장
 *
 * 테이블 구조:
 * - user_uuid: 사용자 UUID (샤딩 환경 지원, 필수)
 * - provider: 소셜 제공자 (google, facebook, github 등)
 * - provider_id: 소셜 제공자의 고유 ID (sub, id 등)
 * - token: Access Token (선택)
 * - refresh_token: Refresh Token (선택)
 * - expires_in: 토큰 만료 시간 (선택)
 * - token_secret: OAuth1.0 등을 위한 시크릿 (선택)
 * - meta: 기타 사용자 정보 (JSON)
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ShardingService에서 동적으로 테이블을 생성하므로, 
        // 여기서는 기본 테이블(social_identities)만 생성하거나
        // 또는 이 migration을 템플릿으로 사용하여 ShardingService가 참조하게 할 수 있습니다.
        // 현재는 단일 테이블 모드 및 기본 템플릿 역할을 합니다.
        
        if (Schema::hasTable('social_identities')) {
            return;
        }

        Schema::create('social_identities', function (Blueprint $table) {
            $table->id();

            // 사용자 연결
            $table->string('user_uuid', 36)->index();
            
            // 소셜 식별자
            $table->string('provider'); // google, github, kakao...
            $table->string('provider_id'); // 1234567890
            
            // 유니크 제약: 한 provider 내에서 provider_id는 유일해야 함 (전체 시스템 기준)
            // 하지만 샤딩 환경에서는 글로벌 유니크를 보장하기 위해 인덱스 테이블을 별도로 운용하므로
            // 여기서는 복합 인덱스만 걸어둡니다.
            $table->unique(['provider', 'provider_id']);

            // OAuth 토큰 정보 (필요시 저장)
            $table->text('token')->nullable();
            $table->text('refresh_token')->nullable();
            $table->integer('expires_in')->nullable();
            $table->string('token_secret')->nullable();

            // 사용자 메타 데이터 (이름, 이메일, 아바타 등 캐싱용)
            $table->json('meta')->nullable();

            // 마지막 로그인
            $table->timestamp('last_login_at')->nullable();
            
            $table->timestamps();

            // 인덱스
            // 특정 사용자의 모든 소셜 계정 조회
            // user_uuid 인덱스는 위에서 생성됨
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_identities');
    }
};
