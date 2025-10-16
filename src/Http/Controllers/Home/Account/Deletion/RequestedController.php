<?php

namespace Jiny\Auth\Http\Controllers\Home\Account\Deletion;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 탈퇴 신청 완료 페이지
 */
class RequestedController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 탈퇴 신청 내역 가져오기
        $unregistRequest = UserUnregist::where('user_id', $user->id)
            ->latest()
            ->first();

        return view('jiny-auth::home.account.deletion-requested', [
            'user' => $user,
            'unregistRequest' => $unregistRequest,
        ]);
    }
}
