<?php

namespace Jiny\Auth\Http\Controllers\Admin\AccountLockout;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Jiny\Auth\Services\AccountLockoutService;

/**
 * 관리자 - 계정 잠금 해제 컨트롤러
 *
 * 진입 경로:
 * Route::post('/admin/lockouts/{id}/unlock') → UnlockController::__invoke()
 */
class UnlockController extends Controller
{
    protected $lockoutService;
    protected $actions;

    public function __construct(AccountLockoutService $lockoutService)
    {
        $this->lockoutService = $lockoutService;
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/AccountLockout.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $unlockConfig = $jsonConfig['unlock'] ?? [];

        $this->actions = [
            'validation' => $unlockConfig['validation'] ?? [],
            'routes' => [
                'success' => $unlockConfig['redirect']['success'] ?? 'admin.lockouts.index',
                'error' => $unlockConfig['redirect']['error'] ?? 'back',
            ],
            'messages' => [
                'success' => $unlockConfig['messages']['success'] ?? '계정 잠금이 해제되었습니다.',
                'error' => $unlockConfig['messages']['error'] ?? '잠금 해제에 실패했습니다.',
                'validation' => $unlockConfig['messages']['validation'] ?? [],
            ],
        ];
    }

    /**
     * 잠금 해제 처리
     */
    public function __invoke(Request $request, $id)
    {
        $request->validate(
            $this->actions['validation'],
            $this->actions['messages']['validation']
        );

        $adminId = auth()->id();

        $result = $this->lockoutService->unlockAccount($id, $adminId, $request->reason);

        if ($result) {
            return redirect()->route($this->actions['routes']['success'])
                ->with('success', $this->actions['messages']['success']);
        }

        return redirect()->back()
            ->with('error', $this->actions['messages']['error']);
    }
}