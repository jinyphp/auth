<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountDeletion;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 관리자 - 탈퇴 승인 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/account-deletions/{id}/approve') → ApproveController::__invoke()
 */
class ApproveController extends Controller
{
    protected $deletionService;
    protected $actions;

    public function __construct(AccountDeletionService $deletionService)
    {
        $this->deletionService = $deletionService;
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/AccountDeletion.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $approveConfig = $jsonConfig['approve'] ?? [];

        $this->actions = [
            'validation' => $approveConfig['validation'] ?? [],
            'routes' => [
                'success' => $approveConfig['redirect']['success'] ?? 'admin.deletions.index',
                'error' => $approveConfig['redirect']['error'] ?? 'back',
            ],
            'messages' => [
                'success' => $approveConfig['messages']['success'] ?? '탈퇴가 승인되고 처리되었습니다.',
                'error' => $approveConfig['messages']['error'] ?? '탈퇴 승인 처리에 실패했습니다.',
            ],
        ];
    }

    /**
     * 탈퇴 승인 처리
     */
    public function __invoke(Request $request, $id)
    {
        $request->validate($this->actions['validation']);

        try {
            $adminId = auth()->id();

            $this->deletionService->approveDeletion($id, $adminId, $request->note);

            return redirect()->route($this->actions['routes']['success'])
                ->with('success', $this->actions['messages']['success']);

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }
}