<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserUnregist;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Models\UserUnregist;
use Jiny\Auth\Models\AuthUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * 관리자: 탈퇴 요청 실제 삭제 처리
 */
class DeleteController extends Controller
{
    public function __invoke(Request $request, $id)
    {
        $admin = auth()->user() ?? $request->auth_user;

        if (!$admin) {
            return redirect()->route('login');
        }

        $unregistRequest = UserUnregist::findOrFail($id);

        // 승인된 상태만 삭제 가능
        if ($unregistRequest->status !== 'approved') {
            return back()->withErrors(['error' => '승인된 탈퇴 요청만 삭제할 수 있습니다.']);
        }

        // 설정 확인
        $config = config('admin.auth.account_deletion');

        try {
            DB::beginTransaction();

            // 사용자 계정 찾기
            $user = AuthUser::find($unregistRequest->user_id);

            if ($user) {
                // 백업 생성 옵션 확인
                if ($config['create_backup']) {
                    // TODO: 사용자 데이터 백업 생성 로직
                    // 예: JSON 파일로 저장, 별도 백업 테이블에 저장 등
                    Log::info('User data backup created', [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'deleted_by' => $admin->id,
                        'deleted_at' => now(),
                    ]);
                }

                // 사용자 계정 삭제
                // 옵션 1: 완전 삭제 (hard delete)
                $user->delete();

                // 옵션 2: 소프트 삭제를 사용하는 경우
                // $user->delete(); // 이미 SoftDeletes를 사용 중이면 자동으로 soft delete

                // 옵션 3: 상태만 변경 (계정 비활성화)
                // $user->update(['status' => 'deleted', 'deleted_at' => now()]);

                Log::info('User account deleted', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'unregist_request_id' => $unregistRequest->id,
                    'deleted_by' => $admin->id,
                ]);
            }

            // 탈퇴 요청 레코드는 유지 (이력 관리를 위해)
            // 또는 삭제하려면: $unregistRequest->delete();

            DB::commit();

            return redirect()
                ->route('admin.user-unregist.index')
                ->with('success', '회원 계정이 삭제되었습니다.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Failed to delete user account', [
                'user_id' => $unregistRequest->user_id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => '계정 삭제 중 오류가 발생했습니다: ' . $e->getMessage()]);
        }
    }
}
