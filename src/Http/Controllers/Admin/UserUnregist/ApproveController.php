<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;
use Jiny\Auth\Models\AuthUser;

/**
 * 관리자: 탈퇴 요청 승인
 */
class ApproveController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $admin = auth()->user() ?? $request->auth_user;

        if (!$admin) {
            return redirect()->route('login');
        }

        $unregistRequest = UserUnregist::findOrFail($id);

        if ($unregistRequest->status !== 'pending') {
            return back()->withErrors(['error' => '이미 처리된 요청입니다.']);
        }

        // 승인 처리
        $unregistRequest->approve($admin->id);

        // TODO: 실제 계정 비활성화 또는 삭제 로직
        // 예: 사용자 상태를 'inactive'로 변경하거나 삭제
        // $user = AuthUser::find($unregistRequest->user_id);
        // if ($user) {
        //     $user->update(['status' => 'deleted']);
        // }

        return redirect()
            ->route('admin.user-unregist.index')
            ->with('success', '탈퇴 요청이 승인되었습니다.');
    }
}
