<?php

namespace Jiny\Auth\Http\Controllers\Home\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * 개인 홈 대시보드 컨트롤러
 */
class HomeDashboardController extends Controller
{
    public function __invoke(Request $request)
    {


        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

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

        return view('jiny-auth::home.dashboard', [
            'user' => $user,
            'recentLogins' => $recentLogins,
        ]);
    }
}
