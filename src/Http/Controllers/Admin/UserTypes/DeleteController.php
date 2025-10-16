<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserTypes;

use Illuminate\Routing\Controller;
use Jiny\Auth\Models\UserType;

/**
 * 관리자 - 사용자 유형 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/user/types/{id}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/UserTypes.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'validation' => $deleteConfig['validation'] ?? [],
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.types.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '사용자 유형이 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '사용자 유형 삭제에 실패했습니다.',
                'has_users' => $deleteConfig['messages']['has_users'] ?? '이 유형을 사용하는 사용자가 있어 삭제할 수 없습니다.',
            ],
            'check_users' => $deleteConfig['validation']['check_users'] ?? true,
        ];
    }

    /**
     * 사용자 유형 삭제 처리
     */
    public function __invoke($id)
    {
        $userType = UserType::findOrFail($id);

        // 사용자가 있는 유형은 삭제 불가
        if ($this->actions['check_users'] && $userType->users > 0) {
            return redirect()
                ->route($this->actions['routes']['success'])
                ->with('error', $this->actions['messages']['has_users']);
        }

        $userType->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}