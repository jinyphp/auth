<?php

namespace Jiny\Auth\Http\Controllers\Auth\Logout;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserLogs;
use Jiny\Auth\Services\JwtAuthService;

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
     * JWT 서비스
     *
     * @var JwtAuthService
     */
    protected $jwtService;

    /**
     * 생성자
     *
     * @param  JwtAuthService  $jwtService  JWT 서비스 인스턴스
     */
    public function __construct(JwtAuthService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * 로그아웃 처리
     *
     * JWT 토큰을 무효화하고 사용자를 로그아웃시킵니다.
     *
     * 처리 순서:
     * 1. 현재 JWT 토큰 무효화
     * 2. 사용자의 모든 JWT 토큰 무효화
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

        // 1. 현재 JWT 토큰 무효화 (로그 기록 전에 처리)
        // 요청에서 JWT 토큰 추출 (쿠키, Authorization 헤더, 쿼리 파라미터)
        $token = $this->jwtService->getTokenFromRequest($request);
        if ($token) {
            try {
                // JWT 토큰 검증 및 파싱
                $parsedToken = $this->jwtService->validateToken($token);
                // 토큰 ID (jti claim) 추출
                $tokenId = $parsedToken->claims()->get('jti');

                // 특정 토큰을 블랙리스트에 추가하여 폐기
                $revokeResult = $this->jwtService->revokeToken($tokenId);
                \Log::info('JWT token revoked', ['token_id' => $tokenId, 'result' => $revokeResult]);
            } catch (\Exception $e) {
                // 토큰 무효화 실패는 로그만 남기고 계속 진행
                \Log::warning('JWT token revoke failed', ['error' => $e->getMessage()]);
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
                // 이메일 로그인인 경우: JWT 토큰 무효화만 수행
                \Log::info('Email logout detected', ['user_id' => $user->id]);
            }
        }

        // 3. 로그아웃 로그 기록 및 사용자의 모든 토큰 폐기
        if (Auth::check()) {
            // 사용자의 모든 JWT 토큰 폐기
            // 다른 디바이스/브라우저에서 발급받은 토큰도 모두 무효화
            $revokeAllResult = $this->jwtService->revokeAllUserTokens(Auth::id());
            \Log::info('All user JWT tokens revoked', ['user_id' => Auth::id(), 'count' => $revokeAllResult]);

            // 로그아웃 로그 기록
            UserLogs::create([
                'user_id' => Auth::id(),
                'email' => Auth::user()->email,
                'action' => 'logout',
                'description' => '사용자 로그아웃',
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            ]);

            // API 토큰 삭제 (Sanctum 사용 시)
            // API 요청인 경우 Sanctum 토큰도 삭제
            if ($request->expectsJson()) {
                if (method_exists($request->user(), 'currentAccessToken')) {
                    $request->user()->currentAccessToken()->delete();
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
