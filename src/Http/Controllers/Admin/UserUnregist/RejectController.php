<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;

/**
 * 관리자: 회원 탈퇴 거부 처리
 */
class RejectController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $unregist = UserUnregist::findOrFail($id);

        if ($unregist->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => '대기 중인 신청만 거부할 수 있습니다.'
            ], 400);
        }

        $unregist->update([
            'status' => 'rejected',
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'reject_reason' => $request->input('reject_reason') // 거부 사유 (선택)
        ]);

        return response()->json([
            'success' => true,
            'message' => '회원 탈퇴 신청이 거부되었습니다.'
        ]);
    }
}
