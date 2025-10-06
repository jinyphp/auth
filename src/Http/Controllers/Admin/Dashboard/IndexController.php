<?php

namespace Jiny\Auth\Http\Controllers\Admin\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

/**
 * 관리자 인증 대시보드 컨트롤러
 *
 * Route::get('/admin/auth') → IndexController::__invoke()
 */
class IndexController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        // 회원 통계 데이터
        $stats = [
            'total_users' => User::count(),
            'active_users' => User::whereNotNull('email_verified_at')->count(),
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        // 최근 가입 회원
        $recent_users = User::latest()->take(10)->get();

        // 회원 유형별 통계 (utype 컬럼 사용)
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

        return view('jiny-auth::admin.dashboard', compact(
            'stats',
            'recent_users',
            'user_type_stats',
            'user_grade_stats',
            'account_status_stats'
        ));
    }
}
