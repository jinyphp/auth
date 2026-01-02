<?php

namespace Jiny\Auth\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Class AuthLogHistoryController
 */
class AuthLogHistoryController extends Controller
{
    /**
     * 로그인 기록 조회 (Single Action Controller)
     *
     * @param  Request  $request  API 요청 객체 (인증된 사용자 정보 포함)
     * @return \Illuminate\Http\JsonResponse
     *
     * 응답 형식 (JSON):
     * {
     *   "success": true,
     *   "history": [
     *     {
     *       "email": "user@example.com",
     *       "ip_address": "127.0.0.1",
     *       "user_agent": "Mozilla/5.0...",
     *       "successful": 1,
     *       "attempted_at": "2024-03-20 10:00:00"
     *     },
     *     ...
     *   ]
     * }
     *
     * 에러 응답:
     * - 401 Unauthorized: 인증되지 않은 사용자
     */
    public function __invoke(Request $request)
    {
        // 1. 현재 인증된 사용자 정보 가져오기
        $user = $request->user();

        // 인증되지 않은 경우 401 에러 반환
        if (! $user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2. 로그인 기록 조회
        $recentLogins = [];
        try {
            // auth_login_attempts 테이블에서 조회
            // - email: 사용자 이메일 기준
            // - successful: 성공한 로그인만 (true)
            // - orderBy: 최신순 (attempted_at desc)
            // - limit: 최근 5건
            $recentLogins = DB::table('auth_login_attempts')
                ->where('email', $user->email)
                ->where('successful', true)
                ->orderBy('attempted_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            // 테이블이 존재하지 않거나 DB 오류 발생 시 빈 배열 반환
            // (서비스 중단을 방지하기 위한 예외 처리)
        }

        // 3. JSON 응답 반환
        return response()->json([
            'success' => true,
            'history' => $recentLogins,
        ]);
    }
}
