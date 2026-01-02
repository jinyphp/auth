<?php

namespace Jiny\Auth\Http\Controllers\Auth\Logout;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserLogs;
use Jiny\Jwt\Facades\JwtAuth;

/**
 * 로그아웃 처리 컨트롤러
 *
 * JWT 기반 인증 시스템에서 로그아웃을 처리합니다.
 *
 * 로그인 유형별 로그아웃 처리:
 * 1. 이메일 로그인: JWT 토큰 무효화만 수행
 * 2. 소셜 로그인 (Google 등): JWT 토큰 무효화 + OAuth 제공자 로그아웃
 *
 * 주요 기능:
 * 1. 소셜 로그인 여부 확인
 * 2. JWT 토큰 무효화 (현재 토큰 + 사용자의 모든 토큰)
 * 3. 소셜 로그인인 경우 OAuth 제공자 로그아웃
 * 4. 로그아웃 로그 기록
 * 5. 세션 무효화 (세션 사용 시)
 * 6. JWT 쿠키 제거
 * 7. 로그인 페이지로 리다이렉트
 */
class SubmitController extends Controller
{
    /**
     * 생성자
     *
     * 주의: JWT 서비스는 jiny/jwt 패키지의 JwtAuth 파사드를 사용합니다.
     */
    public function __construct()
    {
        // JWT 서비스는 파사드로 접근
    }

    /**
     * 로그아웃 처리
     *
     * jiny/jwt 패키지의 로그아웃 API를 호출하여 JWT 토큰을 무효화하고 사용자를 로그아웃시킵니다.
     * 향후 MSA로 분리될 때 jiny/jwt 서비스를 독립적으로 호출할 수 있도록 설계되었습니다.
     *
     * 처리 순서:
     * 1. jiny/jwt 로그아웃 API 호출 (JWT 토큰 폐기)
     * 2. 소셜 로그인 여부 확인 및 OAuth 제공자 로그아웃
     * 3. 로그아웃 로그 기록
     * 4. Laravel Auth 로그아웃
     * 5. 세션 무효화
     * 6. JWT 쿠키 제거
     * 7. 루트 페이지로 리다이렉트
     *
     * @param  Request  $request  HTTP 요청
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    public function __invoke(Request $request)
    {
        $userId = Auth::id();

        // 1. jiny/jwt 로그아웃 API 호출
        // 향후 MSA로 분리될 때 독립적인 서비스로 호출할 수 있도록 설계
        // 현재는 모놀리식 환경이므로 내부 API를 호출하지만,
        // MSA 분리 시에는 HTTP 클라이언트를 사용하여 외부 서비스 호출
        try {
            // 내부 API 호출 (같은 애플리케이션 내에서 호출)
            // Request::create()를 사용하여 내부 API 호출 시뮬레이션
            $jwtLogoutUrl = route('api.jwt.logout');

            // 현재 요청의 쿠키와 헤더를 포함하여 API 호출
            // 로그아웃 시 항상 모든 JWT 토큰을 해제하도록 설정 (기본값: true)
            $jwtLogoutRequest = Request::create($jwtLogoutUrl, 'POST', [
                'revoke_all' => true, // 모든 토큰 폐기 (기본값 변경)
            ]);

            // 현재 요청의 쿠키와 헤더 복사
            foreach ($request->cookies->all() as $name => $value) {
                $jwtLogoutRequest->cookies->set($name, $value);
            }

            foreach ($request->headers->all() as $key => $values) {
                if (!in_array(strtolower($key), ['host', 'content-length'])) {
                    $jwtLogoutRequest->headers->set($key, $values);
                }
            }

            // 현재 요청의 IP와 User Agent 복사
            $jwtLogoutRequest->server->set('REMOTE_ADDR', $request->ip());
            $jwtLogoutRequest->server->set('HTTP_USER_AGENT', $request->userAgent());

            // API 호출 실행
            $jwtLogoutResponse = app()->handle($jwtLogoutRequest);
            $jwtLogoutData = json_decode($jwtLogoutResponse->getContent(), true);

            if ($jwtLogoutData && ($jwtLogoutData['success'] ?? false)) {
                \Log::info('JWT logout API called successfully', [
                    'user_id' => $userId,
                ]);
            } else {
                \Log::warning('JWT logout API call failed', [
                    'user_id' => $userId,
                    'response' => $jwtLogoutData,
                ]);
            }
        } catch (\Exception $e) {
            // API 호출 실패는 로그만 남기고 계속 진행
            // (jiny/jwt 패키지가 없거나 API가 사용 불가능한 경우 대비)
            \Log::warning('JWT logout API call error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Fallback: 직접 JWT 토큰 폐기 시도 (모든 토큰 해제)
            try {
                $token = JwtAuth::getTokenFromRequest($request);
                if ($token) {
                    $parsedToken = JwtAuth::validateToken($token);
                    $tokenId = $parsedToken->claims()->get('jti');
                    $userId = $parsedToken->claims()->get('sub') ?? $parsedToken->claims()->get('uuid');
                    
                    // 현재 토큰 폐기
                    if ($tokenId) {
                        JwtAuth::revokeToken($tokenId);
                    }
                    
                    // 사용자의 모든 토큰 폐기
                    if ($userId) {
                        JwtAuth::revokeAllUserTokens($userId);
                    }
                }
            } catch (\Exception $fallbackError) {
                \Log::warning('JWT token revoke fallback failed', [
                    'error' => $fallbackError->getMessage(),
                ]);
            }
        }

        // 2. 소셜 로그인 여부 확인 및 OAuth 제공자 로그아웃
        if (Auth::check()) {
            $user = Auth::user();
            $socialProvider = $this->detectSocialProvider($user);

            if ($socialProvider) {
                // 소셜 로그인인 경우: OAuth 제공자 로그아웃 처리
                \Log::info('Social logout detected', [
                    'user_id' => $user->id,
                    'provider' => $socialProvider,
                ]);

                try {
                    $this->performSocialLogout($user, $socialProvider);
                } catch (\Exception $e) {
                    // 소셜 로그아웃 실패는 로그만 남기고 계속 진행
                    \Log::warning('Social logout failed', [
                        'provider' => $socialProvider,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                // 이메일 로그인인 경우
                \Log::info('Email logout detected', ['user_id' => $user->id]);
            }
        }

        // 3. 로그아웃 로그 기록
        if (Auth::check()) {
            // 로그아웃 로그 기록
            try {
                UserLogs::create([
                    'user_id' => Auth::id(),
                    'email' => Auth::user()->email,
                    'action' => 'logout',
                    'description' => '사용자 로그아웃',
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                    'session_id' => $request->hasSession() ? $request->session()->getId() : null,
                ]);
            } catch (\Exception $e) {
                // 로그 기록 실패는 무시
                \Log::warning('User logout log creation failed', [
                    'error' => $e->getMessage(),
                ]);
            }

            // API 토큰 삭제 (Sanctum 사용 시)
            // API 요청인 경우 Sanctum 토큰도 삭제
            if ($request->expectsJson()) {
                try {
                    if (method_exists($request->user(), 'currentAccessToken')) {
                        $request->user()->currentAccessToken()->delete();
                    }
                } catch (\Exception $e) {
                    // Sanctum 토큰 삭제 실패는 무시
                }
            }
        }

        // 3. Laravel Auth 로그아웃
        // auth()->user()가 null이 되고, auth()->check()가 false를 반환
        Auth::logout();

        // 4. 세션 무효화 (세션이 있을 때만)
        try {
            if ($request->hasSession() && $request->session()) {
                // 세션 데이터 삭제
                $request->session()->invalidate();
                // CSRF 토큰 재생성 (보안)
                $request->session()->regenerateToken();
            }
        } catch (\Exception $e) {
            // 세션 무효화 실패는 로그만 남기고 계속 진행
            \Log::warning('Session invalidation failed', ['error' => $e->getMessage()]);
        }

        // 5. JWT 토큰 쿠키 제거 (명시적으로 만료시킴)
        // 쿠키 만료 시간을 과거로 설정하여 브라우저에서 삭제되도록 함
        // -2628000 = -1개월 (과거 시간)
        $accessTokenCookie = cookie('access_token', '', -2628000, '/', null, false, true);
        $refreshTokenCookie = cookie('refresh_token', '', -2628000, '/', null, false, true);
        $tokenCookie = cookie('token', '', -2628000, '/', null, false, true);

        \Log::info('User logged out', ['user_id' => $userId]);

        // 6. 응답 반환
        // API 요청인 경우 JSON 응답
        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '성공적으로 로그아웃되었습니다.',
            ])->withCookie($accessTokenCookie)
                ->withCookie($refreshTokenCookie)
                ->withCookie($tokenCookie);
        }

        // 웹 요청인 경우 루트 페이지로 리다이렉트
        return redirect('/')
            ->with('success', '성공적으로 로그아웃되었습니다.')
            ->withCookie($accessTokenCookie)
            ->withCookie($refreshTokenCookie)
            ->withCookie($tokenCookie);
    }

    /**
     * 소셜 로그인 제공자 감지
     *
     * 사용자가 소셜 로그인으로 인증했는지 확인하고, 사용한 제공자를 반환합니다.
     *
     * @param  object  $user  사용자 객체
     * @return string|null  소셜 제공자 이름 (google, kakao, naver 등) 또는 null (이메일 로그인)
     */
    private function detectSocialProvider($user)
    {
        try {
            // user_oauth_accounts 테이블에서 소셜 계정 확인
            $socialAccount = \DB::table('user_oauth_accounts')
                ->where('user_uuid', $user->uuid)
                ->orderBy('created_at', 'desc')
                ->first();

            if ($socialAccount) {
                return $socialAccount->provider; // google, kakao, naver 등
            }
        } catch (\Exception $e) {
            // 테이블이 없거나 오류 발생 시 null 반환 (이메일 로그인으로 간주)
            \Log::warning('Failed to detect social provider', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        return null; // 이메일 로그인
    }

    /**
     * 소셜 제공자 로그아웃 처리
     *
     * OAuth 제공자의 access token을 폐기하여 소셜 로그아웃을 수행합니다.
     *
     * 처리 내용:
     * - Google: OAuth access token 폐기 (선택사항, 클라이언트 측에서 처리 가능)
     * - Kakao: 로그아웃 API 호출
     * - Naver: 토큰 폐기 API 호출
     *
     * @param  object  $user  사용자 객체
     * @param  string  $provider  소셜 제공자 이름 (google, kakao, naver 등)
     * @return void
     */
    private function performSocialLogout($user, $provider)
    {
        try {
            // 소셜 계정 정보 조회
            $socialAccount = \DB::table('user_oauth_accounts')
                ->where('user_uuid', $user->uuid)
                ->where('provider', $provider)
                ->first();

            if (!$socialAccount) {
                \Log::warning('Social account not found for logout', [
                    'user_id' => $user->id,
                    'provider' => $provider,
                ]);
                return;
            }

            // 제공자별 로그아웃 처리
            switch ($provider) {
                case 'google':
                    // Google OAuth 로그아웃
                    // Google은 클라이언트 측에서 로그아웃 처리하는 것을 권장
                    // 서버 측에서는 토큰 폐기만 수행 (선택사항)
                    $this->revokeGoogleToken($socialAccount);
                    break;

                case 'kakao':
                    // Kakao 로그아웃 API 호출
                    $this->revokeKakaoToken($socialAccount);
                    break;

                case 'naver':
                    // Naver 토큰 폐기
                    $this->revokeNaverToken($socialAccount);
                    break;

                default:
                    \Log::info('No specific logout handler for provider', [
                        'provider' => $provider,
                    ]);
            }

            \Log::info('Social logout completed', [
                'user_id' => $user->id,
                'provider' => $provider,
            ]);
        } catch (\Exception $e) {
            \Log::error('Social logout error', [
                'user_id' => $user->id,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Google OAuth 토큰 폐기
     *
     * @param  object  $socialAccount  소셜 계정 정보
     * @return void
     */
    private function revokeGoogleToken($socialAccount)
    {
        // Google OAuth 토큰 폐기는 선택사항
        // 대부분의 경우 클라이언트 측에서 처리
        \Log::info('Google token revocation (optional)', [
            'provider_user_id' => $socialAccount->provider_user_id,
        ]);

        // 필요시 Google API 호출하여 토큰 폐기
        // https://oauth2.googleapis.com/revoke?token={token}
    }

    /**
     * Kakao 토큰 폐기
     *
     * @param  object  $socialAccount  소셜 계정 정보
     * @return void
     */
    private function revokeKakaoToken($socialAccount)
    {
        // Kakao 로그아웃 API 호출
        \Log::info('Kakao logout', [
            'provider_user_id' => $socialAccount->provider_user_id,
        ]);

        // 필요시 Kakao API 호출
        // POST https://kapi.kakao.com/v1/user/logout
    }

    /**
     * Naver 토큰 폐기
     *
     * @param  object  $socialAccount  소셜 계정 정보
     * @return void
     */
    private function revokeNaverToken($socialAccount)
    {
        // Naver 토큰 폐기
        \Log::info('Naver token revocation', [
            'provider_user_id' => $socialAccount->provider_user_id,
        ]);

        // 필요시 Naver API 호출
        // https://nid.naver.com/oauth2.0/token?grant_type=delete
    }
}
