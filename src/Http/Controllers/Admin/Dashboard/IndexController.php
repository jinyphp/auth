<?php

namespace Jiny\Auth\Http\Controllers\Admin\Dashboard;

use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Config;

/**
 * 관리자 인증 대시보드 컨트롤러
 *
 * Route::get('/admin/auth') → IndexController::__invoke()
 * 
 * 샤딩 시스템 지원:
 * - user_0xx 테이블에서 데이터 집계
 * - 샤드별 분포 통계
 */
class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // 샤딩 설정 확인
        $shardingEnabled = config('jiny-auth.sharding.enable', false);
        $shardCount = config('jiny-auth.sharding.shard_count', 2);

        // 기본 통계 (샤딩 지원)
        $stats = $this->getBasicStats($shardingEnabled, $shardCount);

        // 최근 가입 회원
        $recent_users = User::latest()->take(10)->get();

        // 월별 가입 추이 (최근 6개월)
        $monthly_signups = $this->getMonthlySignups($shardingEnabled, $shardCount);

        // 회원 유형별 통계
        $user_type_stats = User::selectRaw('utype, count(*) as count')
            ->whereNotNull('utype')
            ->groupBy('utype')
            ->get();

        // 회원 등급별 통계
        $user_grade_stats = User::selectRaw('grade, count(*) as count')
            ->whereNotNull('grade')
            ->groupBy('grade')
            ->get();

        // 계정 상태별 통계
        $account_status_stats = User::selectRaw('account_status, count(*) as count')
            ->whereNotNull('account_status')
            ->groupBy('account_status')
            ->get();

        // 샤드별 분포 (샤딩 활성화 시)
        $shard_distribution = [];
        if ($shardingEnabled) {
            $shard_distribution = $this->getShardDistribution($shardCount);
        }

        return view('jiny-auth::admin.dashboard', compact(
            'stats',
            'recent_users',
            'monthly_signups',
            'user_type_stats',
            'user_grade_stats',
            'account_status_stats',
            'shard_distribution',
            'shardingEnabled'
        ));
    }

    /**
     * 기본 통계 데이터 조회
     */
    private function getBasicStats($shardingEnabled, $shardCount)
    {
        if ($shardingEnabled) {
            // 샤딩 활성화: 모든 샤드에서 집계
            $totalUsers = 0;
            $activeUsers = 0;
            $newUsersToday = 0;
            $newUsersWeek = 0;

            for ($i = 0; $i < $shardCount; $i++) {
                $tableName = sprintf('user_%03d', $i);
                
                $totalUsers += DB::table($tableName)->count();
                $activeUsers += DB::table($tableName)->whereNotNull('email_verified_at')->count();
                $newUsersToday += DB::table($tableName)->whereDate('created_at', today())->count();
                $newUsersWeek += DB::table($tableName)
                    ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count();
            }

            return [
                'total_users' => $totalUsers,
                'active_users' => $activeUsers,
                'new_users_today' => $newUsersToday,
                'new_users_week' => $newUsersWeek,
            ];
        } else {
            // 샤딩 비활성화: 단일 테이블
            return [
                'total_users' => User::count(),
                'active_users' => User::whereNotNull('email_verified_at')->count(),
                'new_users_today' => User::whereDate('created_at', today())->count(),
                'new_users_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            ];
        }
    }

    /**
     * 월별 가입 추이 데이터 (최근 6개월)
     */
    private function getMonthlySignups($shardingEnabled, $shardCount)
    {
        $months = [];
        $data = [];

        // 최근 6개월 레이블 생성
        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $months[] = $date->format('Y-m');
            $data[$date->format('Y-m')] = 0;
        }

        // 데이터베이스 드라이버 확인
        $driver = DB::connection()->getDriverName();
        $dateFormat = $driver === 'sqlite' 
            ? 'strftime("%Y-%m", created_at)'
            : 'DATE_FORMAT(created_at, "%Y-%m")';

        if ($shardingEnabled) {
            // 샤딩 활성화: 모든 샤드에서 집계
            for ($i = 0; $i < $shardCount; $i++) {
                $tableName = sprintf('user_%03d', $i);
                
                $results = DB::table($tableName)
                    ->selectRaw("{$dateFormat} as month, COUNT(*) as count")
                    ->where('created_at', '>=', now()->subMonths(6))
                    ->groupBy('month')
                    ->get();

                foreach ($results as $result) {
                    if (isset($data[$result->month])) {
                        $data[$result->month] += $result->count;
                    }
                }
            }
        } else {
            // 샤딩 비활성화
            $results = User::selectRaw("{$dateFormat} as month, COUNT(*) as count")
                ->where('created_at', '>=', now()->subMonths(6))
                ->groupBy('month')
                ->get();

            foreach ($results as $result) {
                if (isset($data[$result->month])) {
                    $data[$result->month] = $result->count;
                }
            }
        }

        return [
            'labels' => $months,
            'data' => array_values($data),
        ];
    }

    /**
     * 샤드별 사용자 분포
     */
    private function getShardDistribution($shardCount)
    {
        $distribution = [];

        for ($i = 0; $i < $shardCount; $i++) {
            $tableName = sprintf('user_%03d', $i);
            $count = DB::table($tableName)->count();
            
            $distribution[] = [
                'shard' => $tableName,
                'count' => $count,
            ];
        }

        return $distribution;
    }
}
