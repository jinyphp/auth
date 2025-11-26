<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * JWT 토큰 관리 테이블 생성 Migration
 *
 * 이 migration은 JWT 인증 시스템에서 발급된 모든 토큰을 추적하고 관리하기 위한 테이블을 생성합니다.
 *
 * 주요 기능:
 * - 로그인 시 발급된 Access Token과 Refresh Token의 정보를 저장
 * - 토큰의 발급 시간, 만료 시간, 사용 기록 등을 관리
 * - 토큰 폐기(revoke) 기능 지원
 * - 샤딩 환경에서의 사용자 식별을 위한 UUID 지원
 * - 관리자 페이지에서 토큰 목록 조회 및 모니터링 가능
 *
 * 사용 위치:
 * - JwtAuthService::generateAccessToken() - Access Token 생성 시 자동 저장
 * - JwtAuthService::generateRefreshToken() - Refresh Token 생성 시 자동 저장
 * - Admin\UserToken\IndexController - 관리자 페이지에서 토큰 목록 조회
 *
 * 테이블 구조:
 * - user_id: 사용자 ID (일반 환경)
 * - user_uuid: 사용자 UUID (샤딩 환경 지원)
 * - token_id: JWT의 JTI (JWT ID) 클레임 값, 고유 식별자
 * - token_type: 'access' 또는 'refresh' 토큰 구분
 * - token_hash: 토큰 ID의 SHA256 해시값 (보안을 위해 원본 토큰은 저장하지 않음)
 * - claims: JWT 페이로드의 클레임 정보 (JSON)
 * - scopes: 토큰의 권한 범위 (JSON)
 * - ip_address: 토큰 발급 시 요청한 IP 주소
 * - user_agent: 토큰 발급 시 사용한 브라우저/클라이언트 정보
 * - remember: Remember me 옵션 선택 여부
 * - revoked: 토큰 폐기 여부
 * - issued_at: 토큰 발급 시간
 * - expires_at: 토큰 만료 시간
 * - last_used_at: 토큰 마지막 사용 시간 (토큰 검증 시 업데이트)
 * - revoked_at: 토큰 폐기 시간
 */
return new class extends Migration
{
    /**
     * JWT 토큰 관리 테이블 생성
     *
     * 로그인 시 JwtAuthService에서 자동으로 토큰 정보가 저장되며,
     * 관리자 페이지(/admin/auth/token)에서 토큰 목록을 조회할 수 있습니다.
     *
     * @return void
     */
    public function up(): void
    {
        Schema::create('jwt_tokens', function (Blueprint $table) {
            // 기본 키
            $table->id();

            // 사용자 식별 정보
            // user_id: 일반 환경에서 사용하는 사용자 ID (샤딩 비활성화 시 사용)
            // nullable로 설정한 이유: 샤딩 환경에서는 UUID만 있을 수 있음
            $table->unsignedBigInteger('user_id')->nullable();

            // user_uuid: 샤딩 환경에서 사용하는 사용자 UUID
            // 샤딩이 활성화된 경우 UUID로 사용자를 식별하며, 여러 샤드에 분산된 사용자도 추적 가능
            $table->string('user_uuid', 36)->nullable();

            // 토큰 식별 정보
            // token_id: JWT의 JTI (JWT ID) 클레임 값
            // JWT 표준에서 토큰을 고유하게 식별하기 위한 식별자
            // unique 제약조건으로 중복 방지
            $table->string('token_id')->unique();

            // token_type: 토큰 유형 구분
            // 'access': 일반 API 접근용 토큰 (짧은 만료 시간)
            // 'refresh': Access Token 갱신용 토큰 (긴 만료 시간)
            $table->enum('token_type', ['access', 'refresh'])->default('access');

            // 토큰 보안 정보
            // token_hash: token_id의 SHA256 해시값
            // 보안을 위해 원본 토큰 문자열은 저장하지 않고 해시값만 저장
            // 토큰 검증 시 해시값으로 비교하여 토큰을 식별
            $table->text('token_hash')->nullable();

            // JWT 페이로드 정보
            // claims: JWT의 클레임 정보를 JSON 형태로 저장
            // 예: {"sub": "user-uuid", "email": "user@example.com", "name": "사용자명"}
            $table->json('claims')->nullable();

            // scopes: 토큰의 권한 범위를 JSON 형태로 저장
            // 예: ["read", "write"] - OAuth2 스타일의 권한 관리
            $table->json('scopes')->nullable();

            // 발급 환경 정보
            // ip_address: 토큰 발급 시 요청한 클라이언트의 IP 주소
            // IPv6를 지원하기 위해 최대 45자로 설정 (IPv6 최대 길이)
            $table->string('ip_address', 45)->nullable();

            // user_agent: 토큰 발급 시 사용한 브라우저/클라이언트 정보
            // 예: "Mozilla/5.0 (Windows NT 10.0; Win64; x64) ..."
            // 보안 모니터링 및 이상 접근 탐지에 활용
            $table->text('user_agent')->nullable();

            // remember: Remember me 옵션 선택 여부
            // true인 경우 토큰 만료 시간이 더 길게 설정됨
            // JwtAuthService에서 remember 옵션에 따라 토큰 유효기간을 다르게 설정
            $table->boolean('remember')->default(false);

            // 토큰 상태 관리
            // revoked: 토큰 폐기 여부
            // true인 경우 해당 토큰은 더 이상 사용할 수 없음
            // 로그아웃 시 또는 관리자가 강제 폐기 시 true로 설정
            $table->boolean('revoked')->default(false);

            // 시간 정보
            // issued_at: 토큰 발급 시간 (필수)
            // 로그인 성공 시점의 타임스탬프
            $table->timestamp('issued_at');

            // expires_at: 토큰 만료 시간 (필수)
            // 이 시간 이후에는 토큰이 유효하지 않음
            // Access Token: 기본 1시간, Remember me 시 24시간
            // Refresh Token: 기본 30일, Remember me 시 90일
            $table->timestamp('expires_at');

            // last_used_at: 토큰 마지막 사용 시간 (선택)
            // 토큰 검증 시마다 업데이트되어 최근 활동 추적 가능
            // 오래 사용되지 않은 토큰을 자동으로 정리하는 데 활용
            $table->timestamp('last_used_at')->nullable();

            // revoked_at: 토큰 폐기 시간 (선택)
            // revoked가 true로 변경된 시점의 타임스탬프
            // 보안 감사 및 로그 분석에 활용
            $table->timestamp('revoked_at')->nullable();

            // Laravel 기본 타임스탬프
            // created_at: 레코드 생성 시간
            // updated_at: 레코드 수정 시간
            $table->timestamps();

            // 인덱스 설정
            // 쿼리 성능 최적화를 위한 인덱스
            // user_id: 사용자별 토큰 조회 시 사용
            $table->index('user_id');

            // user_uuid: 샤딩 환경에서 UUID로 토큰 조회 시 사용
            $table->index('user_uuid');

            // token_id: 토큰 검증 시 고유 식별자로 조회 (unique이므로 자동 인덱스 생성되지만 명시)
            $table->index('token_id');

            // token_type: 토큰 타입별 필터링 시 사용 (예: Access Token만 조회)
            $table->index('token_type');

            // revoked: 폐기된 토큰 필터링 시 사용
            $table->index('revoked');

            // expires_at: 만료된 토큰 정리 작업 시 사용
            // 만료 시간이 지난 토큰을 일괄 삭제하는 쿼리 성능 향상
            $table->index('expires_at');
        });
    }

    /**
     * JWT 토큰 관리 테이블 삭제
     *
     * migration 롤백 시 테이블을 삭제합니다.
     * 주의: 이 작업은 모든 토큰 기록을 삭제하므로 신중하게 실행해야 합니다.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::dropIfExists('jwt_tokens');
    }
};
