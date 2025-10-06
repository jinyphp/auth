<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use App\Http\Controllers\Controller;

/**
 * 관리자 - OAuth 프로바이더 삭제 처리 컨트롤러
 *
 * 진입 경로:
 * Route::delete('/admin/auth/oauth-providers/{id}') → DeleteController::__invoke()
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
        $configPath = __DIR__ . '/OAuthProviders.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $deleteConfig = $jsonConfig['delete'] ?? [];

        $this->actions = [
            'routes' => [
                'success' => $deleteConfig['redirect']['success'] ?? 'admin.auth.oauth.providers.index',
            ],
            'messages' => [
                'success' => $deleteConfig['messages']['success'] ?? '소셜 로그인 프로바이더가 삭제되었습니다.',
                'error' => $deleteConfig['messages']['error'] ?? '소셜 로그인 프로바이더 삭제에 실패했습니다.',
            ],
        ];
    }

    /**
     * OAuth 프로바이더 삭제 처리
     */
    public function __invoke($id)
    {
        $provider = \DB::table('user_oauth_providers')->where('id', $id)->first();

        if (!$provider) {
            return redirect()->route('admin.auth.oauth.providers.index')
                ->with('error', 'OAuth 프로바이더를 찾을 수 없습니다.');
        }

        \DB::table('user_oauth_providers')->where('id', $id)->delete();

        return redirect()
            ->route($this->actions['routes']['success'])
            ->with('success', $this->actions['messages']['success']);
    }
}
