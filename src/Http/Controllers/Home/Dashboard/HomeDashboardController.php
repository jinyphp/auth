<?php

namespace Jiny\Auth\Http\Controllers\Home\Dashboard;

use Jiny\Auth\Http\Controllers\HomeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 개인 홈 대시보드 컨트롤러 (JWT 인증)
 */
class HomeDashboardController extends HomeController
{
    public function __invoke(Request $request)
    {
        // Step1. JWT 인증 처리
        $user = $this->auth($request);
        if (!$user) {
            \Log::warning('HomeDashboard: User not authenticated');
            return redirect()->route('login')
                ->with('error', 'JWT 인증이 필요합니다. 로그인해 주세요.')
                ->with('info', '홈 서비스는 로그인 후 이용하실 수 있습니다.');
        }

        \Log::info('HomeDashboard: User authenticated', [
            'user_id' => $user->id,
            'user_uuid' => $user->uuid,
            'user_email' => $user->email,
            'user_name' => $user->name
        ]);

        $userUuid = $user->uuid ?? '';

        // 최근 로그인 기록 조회
        $recentLogins = [];
        try {
            $recentLogins = DB::table('auth_login_attempts')
                ->where('email', $user->email)
                ->where('successful', true)
                ->orderBy('attempted_at', 'desc')
                ->limit(5)
                ->get();
        } catch (\Exception $e) {
            // 테이블이 없으면 무시
        }

        // 이머니 정보 조회
        $emoney = null;
        $emoneyLogs = collect();
        if ($userUuid) {
            try {
                $emoney = DB::table('user_emoney')->where('user_uuid', $userUuid)->first();
                $emoneyLogs = DB::table('user_emoney_log')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        // 포인트 정보 조회
        $point = null;
        $pointLogs = collect();
        if ($userUuid) {
            try {
                $point = DB::table('user_point')->where('user_uuid', $userUuid)->first();
                $pointLogs = DB::table('user_point_log')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        // 은행 계좌 정보
        $bankAccounts = collect();
        if ($userUuid) {
            try {
                $bankAccounts = DB::table('user_emoney_bank')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->get();
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        // 접속 정보 수집
        $connectionInfo = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_login_at' => $user->last_login_at,
            'last_activity_at' => $user->last_activity_at,
            'login_count' => $user->login_count ?? 0,
            'created_at' => $user->created_at,
        ];

        return view('jiny-auth::home.dashboard.index', [
            'user' => $user,
            'recentLogins' => $recentLogins,
            'connectionInfo' => $connectionInfo,
            'emoney' => $emoney,
            'emoneyLogs' => $emoneyLogs,
            'point' => $point,
            'pointLogs' => $pointLogs,
            'bankAccounts' => $bankAccounts,
        ]);
    }
}
