<?php

namespace Jiny\Auth\Http\Controllers\Admin\UserSocial;

use App\Http\Controllers\Controller;

/**
 * 관리자 - 소셜 계정 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/user/social/{id}') → DeleteController::__invoke()
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
        $configPath = __DIR__ . '/UserSocial.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.user.social.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '소셜 계정이 성공적으로 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '소셜 계정 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * 소셜 계정 삭제 처리
     */
    public function __invoke($id)
    {
        $social = \DB::table('user_socials')->where('id', $id)->first();

        if (!$social) {
            return redirect()->route('admin.auth.user.social.index')
                ->with('error', '소셜 계정을 찾을 수 없습니다.');
        }

        \DB::table('user_socials')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
