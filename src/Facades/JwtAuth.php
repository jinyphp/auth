<?php

namespace Jiny\Auth\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * JWT 인증 및 샤딩된 사용자 접근을 위한 파사드
 *
 * JWT 토큰 관리와 샤딩된 사용자 인증을 통합하여 제공하는 파사드
 *
 * @method static object|null user(\Illuminate\Http\Request|null $request = null) 현재 인증된 사용자 정보 반환 (세션 + JWT 통합)
 * @method static object|null getUserByUuid(string $uuid) UUID로 사용자 정보 조회
 * @method static array getUsersByUuids(array $uuids) 여러 사용자 UUID로 사용자 정보 조회
 * @method static bool check(\Illuminate\Http\Request|null $request = null) 현재 사용자가 인증되었는지 확인
 * @method static string|null id(\Illuminate\Http\Request|null $request = null) 현재 사용자의 UUID 반환
 * @method static object|null getAuthenticatedUser(\Illuminate\Http\Request $request) 인증된 사용자와 request를 통합하여 반환
 * @method static int getShardNumber(string $uuid) 사용자의 샤드 번호 반환
 * @method static string getShardTableName(string $uuid) 사용자의 샤드 테이블명 반환
 * @method static string generateAccessToken(object $user) Access Token 생성
 * @method static string generateRefreshToken(object $user) Refresh Token 생성
 * @method static array generateTokenPair(object $user) 토큰 쌍 생성 (Access + Refresh)
 * @method static \Lcobucci\JWT\Token validateToken(string $tokenString) 토큰 검증
 * @method static string|null getTokenFromRequest(\Illuminate\Http\Request|null $request = null) 요청에서 토큰 추출
 * @method static object|null getUserFromToken(string $tokenString) 토큰에서 사용자 정보 추출
 * @method static bool revokeToken(string $tokenId) 토큰 폐기
 * @method static bool revokeAllUserTokens(string|int $userId) 사용자의 모든 토큰 폐기
 * @method static string|null extractTokenFromBearer(string $bearerToken) Bearer 토큰에서 토큰 추출
 *
 * @see \Jiny\Auth\Services\JwtAuthService
 */
class JwtAuth extends Facade
{
    /**
     * 파사드의 등록된 이름을 반환합니다.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'jiny.auth.jwt';
    }
}