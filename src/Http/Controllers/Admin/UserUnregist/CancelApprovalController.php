<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Jiny\Auth\Models\UserUnregist;
use App\Models\User;

/**
 * 관리자: 탈퇴 승인 취소 처리
 */
class CancelApprovalController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $unregist = UserUnregist::findOrFail($id);

        if ($unregist->status !== 'approved') {
            return response()->json([
                'success' => false,
                'message' => '승인된 신청만 승인 취소할 수 있습니다.'
            ], 400);
        }

        DB::beginTransaction();
        try {
            $unregist->update([
                'status' => 'pending',
                'approved_at' => null,
                'approved_by' => null,
            ]);

            // 사용자 계정을 복구하여 다시 활성 상태로 전환
            $userModel = new User();
            $usesSoftDeletes = in_array('Illuminate\Database\Eloquent\SoftDeletes', class_uses_recursive($userModel));
            $userQuery = $usesSoftDeletes ? User::withTrashed() : User::query();
            $user = $userQuery->find($unregist->user_id);

            if ($user) {
                if ($usesSoftDeletes && method_exists($user, 'restore')) {
                    $user->restore();
                } else {
                    $user->deleted_at = null;
                    $user->save();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '승인 상태가 취소되어 다시 대기중으로 변경되었습니다.'
            ]);
        } catch (\Throwable $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => '승인 취소 중 오류가 발생했습니다: ' . $e->getMessage()
            ], 500);
        }
    }
}

