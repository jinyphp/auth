<?php

namespace Jiny\Auth\Http\Controllers\Home\UserDelete;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
        
        if (!$config || empty($config['enable'])) {
            abort(403, '회원 탈퇴 기능이 비활성화되어 있습니다.');
        }

        // 이미 탈퇴 신청한 내역이 있는지 확인 (모든 상태 포함)
        $existingRequest = UserUnregist::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->latest()
            ->first();

        // 회원 탈퇴 신청 이력 조회 (최근 10개)
        $unregistHistory = $this->getUnregistHistory($user->id, $user->uuid ?? null);

        return view('jiny-auth::home.user-delete.delete-profile', [
            'user' => $user,
            'existingRequest' => $existingRequest,
            'config' => $config,
            'unregistHistory' => $unregistHistory,
        ]);
    }

    /**
     * 사용자 회원 탈퇴 신청 이력 조회
     * 
     * @param int $userId 사용자 ID
     * @param string|null $userUuid 사용자 UUID
     * @return \Illuminate\Support\Collection
     */
    protected function getUnregistHistory(int $userId, ?string $userUuid = null)
    {
        try {
            $query = UserUnregist::where('user_id', $userId);

            // UUID가 있는 경우 UUID로도 조회
            if ($userUuid) {
                $query->orWhere('user_uuid', $userUuid);
            }

            return $query->orderBy('created_at', 'desc')
                ->limit(10)
                ->get()
                ->map(function ($unregist) {
                    // 상태별 라벨 매핑
                    $statusLabels = [
                        'pending' => '대기 중',
                        'approved' => '승인됨',
                        'rejected' => '거부됨',
                        'deleted' => '탈퇴 완료',
                    ];

                    $statusLabel = $statusLabels[$unregist->status] ?? $unregist->status;

                    return [
                        'id' => $unregist->id,
                        'status' => $unregist->status,
                        'status_label' => $statusLabel,
                        'reason' => $unregist->reason,
                        'created_at' => $unregist->created_at,
                        'approved_at' => $unregist->approved_at,
                        'rejected_at' => $unregist->rejected_at ?? null,
                        'manager_id' => $unregist->manager_id,
                    ];
                });

        } catch (\Exception $e) {
            \Log::debug('Failed to fetch unregist history: ' . $e->getMessage());
            return collect();
        }
    }
}
