<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * 관리자: 회원 탈퇴 승인 처리
 */
class ApproveController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $unregist = UserUnregist::findOrFail($id);

        if ($unregist->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => '대기 중인 신청만 승인할 수 있습니다.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            // 1. 탈퇴 신청 상태 업데이트
            $unregist->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            // 2. 사용자 계정 Soft Delete 처리
            $user = User::find($unregist->user_id);
            if ($user) {
                // 이미 삭제된 경우 제외
                if (!$user->deleted_at) {
                    $user->deleted_at = now();
                    $user->save();
                }
            }

            // TODO: 추가적인 데이터 정리 로직 (필요 시)
            // 예: 소셜 계정 연결 해제, 세션 만료 등

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '회원 탈퇴가 승인되었습니다.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => '처리 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}
