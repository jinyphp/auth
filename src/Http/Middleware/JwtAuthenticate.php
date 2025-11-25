<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Jiny\Auth\Services\JwtAuthService;
use Jiny\Auth\Facades\Shard;
use Illuminate\Support\Facades\Auth;

class JwtAuthenticate
{
    protected $jwtService;

    public function __construct(JwtAuthService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * JWT 토큰 인증 미들웨어
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            // 공개 경로(로그인/회원가입/비번재설정/이메일 인증 등)는 인증을 요구하지 않음
            $publicRoutes = [
                'login', 'login.*',
                'register', 'register.*',
                'password.*',
                'verification.*',
                'email.*',
                'logout',
            ];
            if (
                $request->routeIs($publicRoutes) ||
                $request->is('login') || $request->is('login/*') ||
                $request->is('register') || $request->is('register/*') ||
                $request->is('password/*') ||
                $request->is('email/*') ||
                $request->is('verification/*')
            ) {
                return $next($request);
            }

            // 토큰 추출
            $token = $this->jwtService->getTokenFromRequest($request);
            if (! $token) {
                // 세션 인증이 남아있을 수 있으므로 안전하게 로그아웃 및 세션 무효화
                $this->forceLogout($request);
                return redirect('/login')
                    ->with('message', '회원 접속 토큰이 존재하지 않습니다.')
                    ->withCookie(cookie()->forget('access_token'))
                    ->withCookie(cookie()->forget('refresh_token'));
            }

            // 토큰 검증
            $decoded = $this->jwtService->validateToken($token);

            // 1차: 기본 JWT 서비스로 유저 조회
            $user = $this->jwtService->getUserFromToken($token);

            // 2차: 샤드 파사드 존재 시 샤딩 DB에서 조회 (이메일 등 고유키 기반)
            if (! $user) {
				// 토큰 클레임에서 식별자 추출
				$claims = $decoded->claims();
				$email = null;
				$uuid = null;
				try { $email = $claims->get('email'); } catch (\Throwable $e) {}
				try { $uuid = $claims->get('uuid'); } catch (\Throwable $e) {}
				if (! $uuid) {
					try { $uuid = $claims->get('sub'); } catch (\Throwable $e) {}
				}

				// 이메일 우선 조회
				if (! $user && $email) {
					$user = Shard::getUserByEmail($email);
				}
				// UUID로 조회
				if (! $user && $uuid) {
					$user = Shard::getUserByUuid($uuid);
				}
            }

            if (! $user) {
                return $this->unauthorizedResponse('회원 정보를 찾을 수 없습니다.');
            }

            // 계정 상태 확인
            if ($user->status === 'blocked') {
                return $this->forbiddenResponse('계정이 차단되었습니다.');
            }

            if ($user->status === 'inactive') {
                return $this->forbiddenResponse('계정이 비활성화되었습니다.');
            }

            // 이메일 인증 확인 (필요한 경우)
            if (config('admin.auth.register.require_email_verification') && ! $user->hasVerifiedEmail()) {
                // 이메일 인증 관련 엔드포인트는 허용
                $allowedRoutes = ['api.email.verify', 'api.email.resend', 'api.logout'];
                if (! $request->routeIs($allowedRoutes)) {
                    return $this->forbiddenResponse('이메일 인증이 필요합니다.');
                }
            }

            // 요청에 사용자 정보 추가
            $request->merge(['auth_user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });
            // Blade @auth, 라라벨 기본 가드에서도 같은 사용자로 인식되도록 설정
            // 세션을 생성하지 않고 현재 요청 범위에서만 인증 상태를 부여합니다.
            try {
                if (class_exists(\Illuminate\Support\Facades\Auth::class)) {
                    \Illuminate\Support\Facades\Auth::setUser($user);
                }
            } catch (\Throwable $e) {
                // 가드 설정 실패는 무시 (요청 리졸버는 이미 설정됨)
            }

            // 토큰 사용 시간 업데이트
			try {
				$claims = $decoded->claims();
				$tokenId = $claims->get('jti');
				\DB::table('jwt_tokens')
					->where('token_id', $tokenId)
					->update(['last_used_at' => now()]);
			} catch (\Throwable $e) {
				// 토큰 ID가 없는 경우는 무시
			}

            return $next($request);

        } catch (\Exception $e) {
            // 토큰 예외 발생 시에도 세션을 안전하게 정리하여 루프 방지
            $this->forceLogout($request);
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * 세션/인증 강제 로그아웃 및 세션 무효화
     */
    private function forceLogout(Request $request): void
    {
        try {
            Auth::logout();
        } catch (\Throwable $e) {
            // ignore
        }
        try {
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        } catch (\Throwable $e) {
            // ignore
        }
    }

    /**
     * 인증 실패 응답
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function unauthorizedResponse($message = '인증되지 않았습니다.')
    {
        // 웹 요청: 로그인 페이지로 안내
        if (!request()->expectsJson()) {
            return redirect('/login')
                ->with('error', $message)
                ->withCookie(cookie()->forget('access_token'))
                ->withCookie(cookie()->forget('refresh_token'));
        }

        // API 요청: JSON 응답
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 401);
    }

    /**
     * 권한 없음 응답
     *
     * @param  string  $message
     * @return \Illuminate\Http\JsonResponse
     */
    protected function forbiddenResponse($message = '접근 권한이 없습니다.')
    {
        // 웹 요청: 로그인 페이지로 안내
        if (!request()->expectsJson()) {
            return redirect('/login')
                ->with('error', $message)
                ->withCookie(cookie()->forget('access_token'))
                ->withCookie(cookie()->forget('refresh_token'));
        }

        // API 요청: JSON 응답
        return response()->json([
            'success' => false,
            'message' => $message,
        ], 403);
    }
}
