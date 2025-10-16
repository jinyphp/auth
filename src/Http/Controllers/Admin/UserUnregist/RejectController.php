<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 관리자: 탈퇴 요청 거부
 */
class RejectController extends Controller
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

        // 거부 처리
        $unregistRequest->reject($admin->id);

        return redirect()
            ->route('admin.user-unregist.index')
            ->with('success', '탈퇴 요청이 거부되었습니다.');
    }
}
