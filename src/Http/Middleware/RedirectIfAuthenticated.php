<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * JWT 기반 인증 체크 미들웨어
 *
 * 로그인 페이지 등 guest 페이지에 인증된 사용자가 접근할 때
 * JWT 토큰 유무를 확인하여 리다이렉트 처리
 */
class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$guards): Response
    {
        // JWT 모드에서는 JWT 토큰 유무로만 판단
        if (config('admin.auth.method') === 'jwt') {
            // JWT 토큰이 있으면 홈으로 리다이렉트
            if ($request->hasCookie('access_token')) {
                return redirect('/home');
            }

            // JWT 토큰이 없으면 세션 인증 무시하고 계속 진행
            return $next($request);
        }

        // Session 모드: Laravel 기본 동작
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (auth()->guard($guard)->check()) {
                return redirect('/home');
            }
        }

        return $next($request);
    }
}
