<?php

namespace Jiny\Auth\Http\Controllers\Traits;

use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\User;

/**
 * JWT 인증 관련 공통 메서드를 제공하는 Trait
 */
trait JWTAuthTrait
{
    /**
     * JWT 토큰에서 사용자 정보 추출
     *
     * @param Request $request
     * @return User|null
     */
    protected function getUserFromJWT(Request $request)
    {
        try {
            // JWT 토큰 추출 (Authorization 헤더, 쿠키, 파라미터 순으로 확인)
            $token = $this->extractJWTToken($request);

            if (!$token) {
                return null;
            }

            // JWT 시크릿 키 (환경 변수에서 가져오기)
            $jwtSecret = config('app.jwt_secret', env('JWT_SECRET', 'default_secret'));

            // JWT 토큰 디코딩
            $decoded = JWT::decode($token, new Key($jwtSecret, 'HS256'));

            // 토큰에서 사용자 ID 추출
            $userId = $decoded->sub ?? $decoded->user_id ?? null;

            if ($userId) {
                return User::find($userId);
            }

        } catch (\Exception $e) {
            // JWT 토큰 검증 실패시 로그 기록
            \Log::warning('JWT token validation failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * 요청에서 JWT 토큰 추출
     *
     * @param Request $request
     * @return string|null
     */
    protected function extractJWTToken(Request $request)
    {
        // 1. Authorization 헤더에서 Bearer 토큰 확인
        $authHeader = $request->header('Authorization');
        if ($authHeader && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return $matches[1];
        }

        // 2. 쿠키에서 JWT 토큰 확인
        $jwtCookie = $request->cookie('jwt_token') ?? $request->cookie('access_token');
        if ($jwtCookie) {
            return $jwtCookie;
        }

        // 3. 요청 파라미터에서 토큰 확인
        $tokenParam = $request->input('token') ?? $request->input('jwt');
        if ($tokenParam) {
            return $tokenParam;
        }

        return null;
    }

    /**
     * 사용자 인증 - 다중 인증 방식 지원
     *
     * @param Request $request
     * @return User|null
     */
    protected function getAuthenticatedUser(Request $request)
    {
        // 1. 기본 Laravel Auth 확인
        $user = auth()->user();

        // 2. JWT 토큰에서 사용자 정보 확인
        if (!$user) {
            $user = $this->getUserFromJWT($request);
        }

        // 3. 요청에서 전달된 auth_user 확인
        if (!$user) {
            $user = $request->auth_user;
        }

        return $user;
    }
}