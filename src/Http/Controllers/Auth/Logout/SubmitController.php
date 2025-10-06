<?php

namespace Jiny\Auth\Http\Controllers\Auth\Logout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Jiny\Auth\Models\UserLogs;
use Jiny\Auth\Services\JwtService;

class SubmitController extends Controller
{
    protected $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    public function __invoke(Request $request)
    {
        $userId = Auth::id();

        // JWT 토큰 무효화 (로그 기록 전에 처리)
        $token = $this->jwtService->getTokenFromRequest($request);
        if ($token) {
            try {
                $parsedToken = $this->jwtService->validateToken($token);
                $tokenId = $parsedToken->claims()->get('jti');

                // 특정 토큰 폐기
                $revokeResult = $this->jwtService->revokeToken($tokenId);
                \Log::info('JWT token revoked', ['token_id' => $tokenId, 'result' => $revokeResult]);
            } catch (\Exception $e) {
                \Log::warning('JWT token revoke failed', ['error' => $e->getMessage()]);
            }
        }

        // 로그아웃 로그 기록
        if (Auth::check()) {
            // 사용자의 모든 JWT 토큰 폐기
            $revokeAllResult = $this->jwtService->revokeAllUserTokens(Auth::id());
            \Log::info('All user JWT tokens revoked', ['user_id' => Auth::id(), 'count' => $revokeAllResult]);

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
            if ($request->expectsJson()) {
                if (method_exists($request->user(), 'currentAccessToken')) {
                    $request->user()->currentAccessToken()->delete();
                }
            }
        }

        Auth::logout();

        // 세션 처리 (세션이 있을 때만)
        try {
            if ($request->hasSession() && $request->session()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
        } catch (\Exception $e) {
            \Log::warning('Session invalidation failed', ['error' => $e->getMessage()]);
        }

        // JWT 토큰 쿠키 제거 (명시적으로 만료시킴)
        $accessTokenCookie = cookie('access_token', '', -2628000, '/', null, false, true);
        $refreshTokenCookie = cookie('refresh_token', '', -2628000, '/', null, false, true);
        $tokenCookie = cookie('token', '', -2628000, '/', null, false, true);

        \Log::info('User logged out', ['user_id' => $userId]);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => '성공적으로 로그아웃되었습니다.',
            ])->withCookie($accessTokenCookie)
              ->withCookie($refreshTokenCookie)
              ->withCookie($tokenCookie);
        }

        return redirect('/')
            ->with('success', '성공적으로 로그아웃되었습니다.')
            ->withCookie($accessTokenCookie)
            ->withCookie($refreshTokenCookie)
            ->withCookie($tokenCookie);
    }
}