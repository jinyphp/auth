<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountDeletion;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\AccountDeletionService;

/**
 * 관리자 - 탈퇴 거부 처리 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/account-deletions/{id}/reject') → RejectController::__invoke()
 */
class RejectController extends Controller
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

        $rejectConfig = $jsonConfig['reject'] ?? [];

        $this->actions = [
            'validation' => $rejectConfig['validation'] ?? [],
            'routes' => [
                'success' => $rejectConfig['redirect']['success'] ?? 'admin.deletions.index',
                'error' => $rejectConfig['redirect']['error'] ?? 'back',
            ],
            'messages' => [
                'success' => $rejectConfig['messages']['success'] ?? '탈퇴 신청이 거부되었습니다.',
                'error' => $rejectConfig['messages']['error'] ?? '탈퇴 거부 처리에 실패했습니다.',
                'validation' => $rejectConfig['messages']['validation'] ?? [],
            ],
        ];
    }

    /**
     * 탈퇴 거부 처리
     */
    public function __invoke(Request $request, $id)
    {
        $request->validate(
            $this->actions['validation'],
            $this->actions['messages']['validation']
        );

        try {
            $adminId = auth()->id();

            $this->deletionService->rejectDeletion($id, $adminId, $request->note);

            return redirect()->route($this->actions['routes']['success'])
                ->with('success', $this->actions['messages']['success']);

        } catch (\Exception $e) {
            return back()
                ->with('error', $e->getMessage());
        }
    }
}