<?php

namespace Jiny\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckAccountStatus
{
    /**
     * 계정 상태 확인 미들웨어
     * 인증된 사용자의 계정 상태를 확인합니다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('login');
        }

        // 0. 탈퇴 승인 확인 (최우선)
        $unregistRequest = \Jiny\Auth\Models\UserUnregist::where('user_id', $user->id)
            ->where('status', 'approved')
            ->first();

        if ($unregistRequest) {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '탈퇴 승인된 계정입니다.',
                    'status' => 'deleted'
                ], 403);
            }

            return redirect()->route('account.deleted')
                ->with('error', '탈퇴 승인된 계정입니다. 더 이상 로그인할 수 없습니다.');
        }

        // 1. 계정 차단 확인
        if ($user->status === 'blocked') {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '계정이 차단되었습니다.',
                    'status' => 'blocked'
                ], 403);
            }

            return redirect()->route('account.blocked')
                ->with('error', '계정이 차단되었습니다. 관리자에게 문의하세요.');
        }

        // 3. 계정 비활성화 확인
        if ($user->status === 'inactive') {
            Auth::logout();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => '계정이 비활성화되었습니다.',
                    'status' => 'inactive'
                ], 403);
            }

            return redirect()->route('login')
                ->with('error', '계정이 비활성화되었습니다.');
        }

        // 4. 승인 대기 확인
        $approvalSettings = $this->getApprovalSettings();
        if ($approvalSettings['require_approval'] && $user->status === 'pending') {
            // 승인 대기 중인 경우 특정 페이지만 허용
            $allowedRoutes = [
                'account.pending',
                'logout',
                'verification.notice',
                'verification.verify',
                'verification.resend'
            ];

            if (!$request->routeIs($allowedRoutes)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '계정 승인 대기 중입니다.',
                        'status' => 'pending'
                    ], 403);
                }

                return redirect()->route('account.pending')
                    ->with('info', '계정 승인 대기 중입니다.');
            }
        }

        // 5. 이메일 인증 확인
        $authSettings = $this->loadAuthSettings();
        if (($authSettings['register']['require_email_verification'] ?? true) && !$user->hasVerifiedEmail()) {
            // 이메일 인증이 필요한 경우 특정 페이지만 허용
            $allowedRoutes = [
                'verification.notice',
                'verification.verify',
                'verification.resend',
                'logout'
            ];

            if (!$request->routeIs($allowedRoutes)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '이메일 인증이 필요합니다.',
                        'status' => 'email_not_verified'
                    ], 403);
                }

                return redirect()->route('verification.notice')
                    ->with('warning', '이메일 인증이 필요합니다.');
            }
        }

        // 6. 휴면 계정 확인
        if ($this->isDormant($user)) {
            // 휴면 해제 페이지로 리다이렉트
            $allowedRoutes = [
                'account.reactivate',
                'account.reactivate.submit',
                'logout'
            ];

            if (!$request->routeIs($allowedRoutes)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '휴면 계정입니다. 재활성화가 필요합니다.',
                        'status' => 'dormant'
                    ], 403);
                }

                return redirect()->route('account.reactivate')
                    ->with('info', '휴면 계정입니다. 재활성화가 필요합니다.');
            }
        }

        // 7. 비밀번호 변경 필요 확인
        if ($this->needsPasswordChange($user)) {
            // 비밀번호 변경 페이지로 리다이렉트
            $allowedRoutes = [
                'password.change',
                'password.update',
                'logout'
            ];

            if (!$request->routeIs($allowedRoutes)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '비밀번호 변경이 필요합니다.',
                        'status' => 'password_expired'
                    ], 403);
                }

                return redirect()->route('password.change')
                    ->with('warning', '보안을 위해 비밀번호를 변경해주세요.');
            }
        }

        // 8. 세션 유효성 확인
        $this->checkSessionValidity($user, $request);

        // 9. 활동 로그 업데이트
        $this->updateLastActivity($user);

        return $next($request);
    }

    /**
     * 휴면 계정 여부 확인
     *
     * @param $user
     * @return bool
     */
    protected function isDormant($user)
    {
        // 휴면 테이블 확인
        $sleeper = \DB::table('user_sleeper')
            ->where('user_id', $user->id)
            ->first();

        if ($sleeper) {
            return true;
        }

        // 마지막 로그인 확인
        $authSettings = $this->loadAuthSettings();
        $dormantDays = $authSettings['security']['dormant_days'] ?? 365;

        if ($user->last_login_at && $user->last_login_at->lt(now()->subDays($dormantDays))) {
            // 휴면 처리
            \DB::table('user_sleeper')->insert([
                'user_id' => $user->id,
                'last_login' => $user->last_login_at,
                'dormant_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        }

        return false;
    }

    /**
     * 비밀번호 변경 필요 여부 확인
     *
     * @param $user
     * @return bool
     */
    protected function needsPasswordChange($user)
    {
        $authSettings = $this->loadAuthSettings();
        $passwordExpireDays = $authSettings['security']['password_expire_days'] ?? 90;

        if ($passwordExpireDays <= 0) {
            return false; // 비밀번호 만료 정책 사용 안 함
        }

        // 마지막 비밀번호 변경 확인
        $lastChange = \DB::table('user_password')
            ->where('user_id', $user->id)
            ->orderBy('changed_at', 'desc')
            ->first();

        if (!$lastChange) {
            // 비밀번호 변경 이력이 없으면 계정 생성일 기준
            return $user->created_at->lt(now()->subDays($passwordExpireDays));
        }

        return \Carbon\Carbon::parse($lastChange->changed_at)->lt(now()->subDays($passwordExpireDays));
    }

    /**
     * 세션 유효성 확인
     *
     * @param $user
     * @param Request $request
     */
    protected function checkSessionValidity($user, Request $request)
    {
        $sessionId = session()->getId();

        $authSession = \DB::table('auth_sessions')
            ->where('user_id', $user->id)
            ->where('session_id', $sessionId)
            ->first();

        if (!$authSession) {
            // 세션이 없으면 생성
            \DB::table('auth_sessions')->insert([
                'session_id' => $sessionId,
                'user_id' => $user->id,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'last_activity' => now()->timestamp,
                'expires_at' => now()->addMinutes(config('session.lifetime', 120)),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            // 세션 만료 확인
            if ($authSession->expires_at && \Carbon\Carbon::parse($authSession->expires_at)->lt(now())) {
                Auth::logout();

                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => '세션이 만료되었습니다.',
                        'status' => 'session_expired'
                    ], 401);
                }

                return redirect()->route('login')
                    ->with('warning', '세션이 만료되었습니다. 다시 로그인해주세요.');
            }

            // 세션 업데이트
            \DB::table('auth_sessions')
                ->where('id', $authSession->id)
                ->update([
                    'last_activity' => now()->timestamp,
                    'updated_at' => now(),
                ]);
        }
    }

    /**
     * 마지막 활동 시간 업데이트
     *
     * @param $user
     */
    protected function updateLastActivity($user)
    {
        // 5분마다 업데이트 (DB 부하 감소)
        $cacheKey = "user_last_activity_{$user->id}";

        if (!\Cache::has($cacheKey)) {
            $user->update(['last_activity_at' => now()]);
            \Cache::put($cacheKey, true, 300); // 5분
        }
    }

    /**
     * JSON 설정 파일에서 approval 설정 읽기
     *
     * @return array
     */
    private function getApprovalSettings()
    {
        $settings = $this->loadAuthSettings();
        return $settings['approval'] ?? ['require_approval' => false];
    }

    /**
     * JSON 설정 파일에서 모든 인증 설정 읽기
     *
     * @return array
     */
    private function loadAuthSettings()
    {
        $configPath = base_path('vendor/jiny/auth/config/setting.json');

        if (file_exists($configPath)) {
            try {
                $jsonContent = file_get_contents($configPath);
                $settings = json_decode($jsonContent, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    return $settings;
                }
            } catch (\Exception $e) {
                // JSON 파싱 실패 시 기본값 사용
            }
        }

        return [];
    }
}