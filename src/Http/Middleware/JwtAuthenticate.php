<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Auth\Services\JwtService;

class JwtAuthenticate
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * JWT 토큰 인증 미들웨어
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 토큰 추출
            $token = $this->jwtService->getTokenFromRequest($request);

            if (!$token) {
                return $this->unauthorizedResponse('토큰이 제공되지 않았습니다.');
            }

            // 토큰 검증
            $decoded = $this->jwtService->validateToken($token);

            // 사용자 정보를 요청에 추가
            $user = $this->jwtService->getUserFromToken($token);

            if (!$user) {
                return $this->unauthorizedResponse('사용자를 찾을 수 없습니다.');
            }

            // 계정 상태 확인
            if ($user->status === 'blocked') {
                return $this->forbiddenResponse('계정이 차단되었습니다.');
            }

            if ($user->status === 'inactive') {
                return $this->forbiddenResponse('계정이 비활성화되었습니다.');
            }

            // 이메일 인증 확인 (필요한 경우)
            if (config('admin.auth.register.require_email_verification') && !$user->hasVerifiedEmail()) {
                // 이메일 인증 관련 엔드포인트는 허용
                $allowedRoutes = ['api.email.verify', 'api.email.resend', 'api.logout'];
                if (!$request->routeIs($allowedRoutes)) {
                    return $this->forbiddenResponse('이메일 인증이 필요합니다.');
                }
            }

            // 요청에 사용자 정보 추가
            $request->merge(['auth_user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            // 토큰 사용 시간 업데이트
            \DB::table('jwt_tokens')
                ->where('token_id', $decoded->jti)
                ->update(['last_used_at' => now()]);

            return $next($request);

        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * 인증 실패 응답
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse($message = '인증되지 않았습니다.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }

    /**
     * 권한 없음 응답
     *
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbiddenResponse($message = '접근 권한이 없습니다.')
    {
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}