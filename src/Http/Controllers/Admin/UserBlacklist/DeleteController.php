<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserBlacklist;

use Illuminate\Routing\Controller;

/**
 * 관리자 - 블랙리스트 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/user/blacklist/{id}') → DeleteController::__invoke()
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
        $configPath = __DIR__ . '/UserBlacklist.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.blacklist.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '블랙리스트가 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '블랙리스트 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 블랙리스트 삭제 처리
     */
    public function __invoke($id)
    {
        $blacklist = \DB::table('user_blacklist')->where('id', $id)->first();

        if (!$blacklist) {
            return redirect()->route('admin.auth.user.blacklist.index')
                ->with('error', '블랙리스트를 찾을 수 없습니다.');
        }

        \DB::table('user_blacklist')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
