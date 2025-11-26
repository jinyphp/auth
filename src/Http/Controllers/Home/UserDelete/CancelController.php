<?php

namespace Jiny\Auth\Http\Controllers\Home\UserDelete;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 탈퇴 신청 취소
 */
class CancelController extends Controller
{
    public function __invoke(Request $request)
    {
        $user = auth()->user() ?? $request->auth_user;

        if (!$user) {
            return redirect()->route('login');
        }

        // 대기 중인 탈퇴 신청 찾기
        $unregistRequest = UserUnregist::where('user_id', $user->id)
            ->where('status', 'pending')
            ->latest()
            ->first();

        if (!$unregistRequest) {
            return back()->withErrors(['error' => '취소할 탈퇴 신청이 없습니다.']);
        }

        // 탈퇴 신청 삭제
        $unregistRequest->delete();

        // AJAX 요청인 경우 JSON 응답
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '탈퇴 신청이 취소되었습니다.',
                'redirect' => route('account.deletion.show')
            ]);
        }

        return redirect()
            ->route('account.deletion.show')
            ->with('success', '탈퇴 신청이 취소되었습니다.');
    }
}

