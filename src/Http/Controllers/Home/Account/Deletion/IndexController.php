<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Deletion;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 회원 탈퇴 신청 페이지
 */
class IndexController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 설정 확인
        $config = config('admin.auth.account_deletion');
        
        if (!$config['enable']) {
            abort(403, '회원 탈퇴 기능이 비활성화되어 있습니다.');
        }

        // 이미 탈퇴 신청한 내역이 있는지 확인 (모든 상태 포함)
        $existingRequest = UserUnregist::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();

        return view('jiny-auth::home.account.delete-profile', [
            'user' => $user,
            'existingRequest' => $existingRequest,
            'config' => $config,
        ]);
    }
}
