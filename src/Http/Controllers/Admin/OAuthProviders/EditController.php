<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use App\Http\Controllers\Controller;

/**
 * 관리자 - OAuth 프로바이더 수정 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/oauth-providers/{id}/edit') → EditController::__invoke()
 */
class EditController extends Controller
{
    protected $config;

    public function __construct()
    {
        $this->middleware(['auth', 'admin']);
        $this->loadConfig();
    }

    /**
     * JSON 설정 파일 로드
     */
    protected function loadConfig()
    {
        $configPath = __DIR__ . '/OAuthProviders.json';
        $jsonConfig = json_decode(file_get_contents($configPath), true);

        $editConfig = $jsonConfig['edit'] ?? [];

        $this->config = [
            'view' => $editConfig['view'] ?? 'jiny-auth::admin.oauth-providers.edit',
            'title' => $editConfig['title'] ?? 'OAuth 프로바이더 수정',
            'subtitle' => $editConfig['subtitle'] ?? 'OAuth 프로바이더 정보 수정',
        ];
    }

    /**
     * OAuth 프로바이더 수정 폼 표시
     */
    public function __invoke($id)
    {
        $provider = \DB::table('user_oauth_providers')->where('id', $id)->first();

        if (!$provider) {
            return redirect()->route('admin.auth.oauth.providers.index')
                ->with('error', 'OAuth 프로바이더를 찾을 수 없습니다.');
        }

        return view($this->config['view'], compact('provider'));
    }
}
