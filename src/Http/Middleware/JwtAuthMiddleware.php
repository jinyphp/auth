<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Auth\Services\JwtService;
use Symfony\Component\HttpFoundation\Response;

/**
 * JWT 토큰 인증 미들웨어
 */
class JwtAuthMiddleware
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. 요청에서 토큰 추출
        $token = $this->jwtService->getTokenFromRequest($request);

        if (!$token) {
            \Log::info('JWT Auth Failed: No token found', [
                'url' => $request->url(),
                'cookies' => array_keys($request->cookies->all()),
                'cookie_values' => $request->cookies->all(),
                'has_access_token_cookie' => $request->hasCookie('access_token'),
                'cookie_access_token_value' => $request->cookie('access_token'),
                'authorization_header' => $request->header('Authorization'),
                'bearer_token' => $this->jwtService->extractTokenFromBearer($request->header('Authorization')),
            ]);
            return $this->unauthorized($request, '토큰이 제공되지 않았습니다.');
        }

        // 2. 토큰 검증
        try {
            $user = $this->jwtService->getUserFromToken($token);

            if (!$user) {
                \Log::info('JWT Auth Failed: Invalid token', [
                    'url' => $request->url(),
                    'token_preview' => substr($token, 0, 50) . '...',
                ]);
                return $this->unauthorized($request, '유효하지 않은 토큰입니다.');
            }

            // 3. 사용자 정보를 요청에 저장
            $request->merge(['auth_user' => $user]);
            auth()->setUser($user);

            \Log::info('JWT Auth Success', [
                'url' => $request->url(),
                'user_id' => $user->id,
                'user_email' => $user->email,
            ]);

        } catch (\Exception $e) {
            \Log::error('JWT Auth Exception', [
                'url' => $request->url(),
                'error' => $e->getMessage(),
                'token_preview' => substr($token, 0, 50) . '...',
            ]);
            return $this->unauthorized($request, $e->getMessage());
        }

        return $next($request);
    }

    /**
     * 인증 실패 응답
     */
    protected function unauthorized(Request $request, $message)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => $message,
            ], 401);
        }

        return redirect()->route('login')
            ->with('error', '로그인이 필요합니다.');
    }
}
