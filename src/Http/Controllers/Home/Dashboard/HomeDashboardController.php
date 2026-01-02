<?php

namespace Jiny\Auth\Http\Controllers\Home\Dashboard;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Http\Controllers\HomeController;

/**
 * 개인 홈 대시보드 컨트롤러 (JWT 인증)
 */
class HomeDashboardController extends HomeController
{
    public function __invoke(Request $request)
    {
        // dump('home dash');

        // Step1. JWT 인증 처리
        // jwt.auth 미들웨어에서 이미 인증된 사용자를 가져옴
        // 미들웨어에서 설정한 사용자 우선 사용 (auth_user 또는 user() 리졸버)
        $user = $request->get('auth_user')
            ?? $request->user()
            ?? (method_exists($request, 'user') ? $request->user() : null)
            ?? (\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user() : null);

        // 미들웨어에서 사용자를 찾지 못한 경우에만 직접 인증 시도
        if (! $user) {
            $user = $this->auth($request);
        }

        if (! $user) {
            \Log::warning('HomeDashboard: User not authenticated', [
                'has_auth_user' => $request->has('auth_user'),
                'has_user_resolver' => $request->hasUserResolver(),
                'auth_check' => \Illuminate\Support\Facades\Auth::check(),
            ]);

            return redirect()->route('login')
                ->with('error', 'JWT 인증이 필요합니다. 로그인해 주세요.')
                ->with('info', '홈 서비스는 로그인 후 이용하실 수 있습니다.');
        }

        \Log::info('HomeDashboard: User authenticated', [
            'user_id' => $user->id,
            'user_uuid' => $user->uuid,
            'user_email' => $user->email,
            'user_name' => $user->name,
        ]);

        $userUuid = $user->uuid ?? '';

        // Step2. 최근 로그인 기록 조회 (API로 이동됨)
        // AuthLogHistoryController 사용


        // Step3. 이머니 정보 조회
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

        // Step4. 포인트 정보 조회
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

        // Step5. 은행 계좌 정보
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

        // Step6. 접속 정보 수집
        $connectionInfo = [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'last_login_at' => $user->last_login_at,
            'last_activity_at' => $user->last_activity_at,
            'login_count' => $user->login_count ?? 0,
            'created_at' => $user->created_at,
        ];

        // Step7. JWT 인증 정보
        $jwtInfo = [
            'has_access_token' => isset($_COOKIE['access_token']),
            'has_refresh_token' => isset($_COOKIE['refresh_token']),
            'auth_method' => 'JWT',
        ];

        // Step8. 소셜 로그인 제공자 확인
        $socialProvider = null;
        if ($userUuid) {
            try {
                $socialAccount = DB::table('user_oauth_accounts')
                    ->where('user_uuid', $userUuid)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($socialAccount) {
                    $socialProvider = $socialAccount->provider; // google, kakao, naver 등
                }
            } catch (\Exception $e) {
                // 테이블이 없는 경우 무시
            }
        }

        // Step9. 로그인 타입 결정
        $loginType = $socialProvider ? 'social' : 'email';

        // Step10. 2FA(이중 인증) 상태 정보
        $twoFactorInfo = [
            'enabled' => (bool) ($user->two_factor_enabled ?? false),
            'method' => $user->two_factor_method ?? 'totp',
            'confirmed_at' => $user->two_factor_confirmed_at,
        ];

        return view('jiny-auth::home.dashboard.index', [
            'user' => $user,
            'user' => $user,
            // 'recentLogins' => $recentLogins, // API로 변경

            'connectionInfo' => $connectionInfo,
            'jwtInfo' => $jwtInfo,
            'socialProvider' => $socialProvider,
            'loginType' => $loginType,
            'emoney' => $emoney,
            'emoneyLogs' => $emoneyLogs,
            'point' => $point,
            'pointLogs' => $pointLogs,
            'bankAccounts' => $bankAccounts,
            'twoFactorInfo' => $twoFactorInfo,
        ]);
    }
}
