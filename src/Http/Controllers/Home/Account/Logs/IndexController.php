<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Logs;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 사용자 활동 로그 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        $perPage = 20;

        // 로그인 시도 기록
        $loginAttempts = $this->getLoginAttempts($user->id, $user->email, $perPage);

        // 활동 로그
        $activityLogs = $this->getActivityLogs($user->id, $user->uuid, $perPage);

        // 통계 정보
        $stats = $this->getStats($user->id, $user->email, $user->uuid);

        return view('jiny-auth::home.account.logs.index', [
            'user' => $user,
            'loginAttempts' => $loginAttempts,
            'activityLogs' => $activityLogs,
            'stats' => $stats,
        ]);
    }

    /**
     * 로그인 시도 기록 가져오기
     */
    protected function getLoginAttempts(int $userId, string $email, int $perPage)
    {
        try {
            if (\Schema::hasTable('auth_login_attempts')) {
                return DB::table('auth_login_attempts')
                    ->where(function ($query) use ($userId, $email) {
                        $query->where('user_id', $userId)
                              ->orWhere('email', $email);
                    })
                    ->orderBy('attempted_at', 'desc')
                    ->limit($perPage)
                    ->get();
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to fetch login attempts: ' . $e->getMessage());
        }

        return collect();
    }

    /**
     * 활동 로그 가져오기
     */
    protected function getActivityLogs(int $userId, ?string $userUuid, int $perPage)
    {
        try {
            if (\Schema::hasTable('auth_activity_logs')) {
                $query = DB::table('auth_activity_logs')
                    ->where('user_id', $userId);

                if ($userUuid) {
                    $query->orWhere('user_uuid', $userUuid);
                }

                return $query->orderBy('performed_at', 'desc')
                    ->limit($perPage)
                    ->get();
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to fetch activity logs: ' . $e->getMessage());
        }

        return collect();
    }

    /**
     * 통계 정보 가져오기
     */
    protected function getStats(int $userId, string $email, ?string $userUuid)
    {
        $stats = [
            'total_logins' => 0,
            'successful_logins' => 0,
            'failed_logins' => 0,
            'total_activities' => 0,
            'last_login' => null,
            'unique_ips' => 0,
        ];

        try {
            // 로그인 통계
            if (\Schema::hasTable('auth_login_attempts')) {
                $loginStats = DB::table('auth_login_attempts')
                    ->where(function ($query) use ($userId, $email) {
                        $query->where('user_id', $userId)
                              ->orWhere('email', $email);
                    })
                    ->selectRaw('
                        COUNT(*) as total,
                        SUM(CASE WHEN successful = 1 THEN 1 ELSE 0 END) as successful,
                        SUM(CASE WHEN successful = 0 THEN 1 ELSE 0 END) as failed,
                        MAX(CASE WHEN successful = 1 THEN attempted_at END) as last_login,
                        COUNT(DISTINCT ip_address) as unique_ips
                    ')
                    ->first();

                if ($loginStats) {
                    $stats['total_logins'] = $loginStats->total ?? 0;
                    $stats['successful_logins'] = $loginStats->successful ?? 0;
                    $stats['failed_logins'] = $loginStats->failed ?? 0;
                    $stats['last_login'] = $loginStats->last_login;
                    $stats['unique_ips'] = $loginStats->unique_ips ?? 0;
                }
            }

            // 활동 로그 통계
            if (\Schema::hasTable('auth_activity_logs')) {
                $activityCount = DB::table('auth_activity_logs')
                    ->where('user_id', $userId);

                if ($userUuid) {
                    $activityCount->orWhere('user_uuid', $userUuid);
                }

                $stats['total_activities'] = $activityCount->count();
            }
        } catch (\Exception $e) {
            \Log::debug('Failed to fetch stats: ' . $e->getMessage());
        }

        return $stats;
    }
}
