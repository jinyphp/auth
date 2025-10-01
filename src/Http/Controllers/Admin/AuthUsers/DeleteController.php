<?php

namespace Jiny\Auth\Http\Controllers\Admin\AuthUsers;

use App\Http\Controllers\Controller;
use Jiny\Auth\Models\AuthUser;

/**
 * 관리자 - 사용자 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth-users/{id}') → DeleteController::__invoke()
 */
class DeleteController extends Controller
{
    protected $actions;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadActions();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadActions()
    {
        $configPath = __DIR__ . '/AuthUser.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.users.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '사용자가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '사용자 삭제에 실패했습니다.',
            ],
            'storage' => [
                'avatar_path' => $jsonConfig['storage']['avatar']['path'] ?? 'public/avatars',
            ],
        ];
    }

    /**
     * 사용자 삭제 처리
     */
    public function __invoke($id)
    {
        $user = AuthUser::findOrFail($id);

        // 아바타 이미지 삭제
        if ($user->avatar) {
            \Storage::delete($this->actions['storage']['avatar_path'] . '/' . basename($user->avatar));
        }

        $user->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}