<?php

namespace Jiny\Auth\Http\Controllers\Admin\OAuthProviders;

use App\Http\Controllers\Controller;

/**
 * 관리자 - OAuth 프로바이더 생성 폼 컨트롤러
 *
 * 진입 경로:
 * Route::get('/admin/auth/oauth/providers/create') → CreateController::__invoke()
 */
class CreateController extends Controller
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

        $createConfig = $jsonConfig['create'] ?? [];

        $this->config = [
            'view' => $createConfig['view'] ?? 'jiny-auth::admin.oauth-providers.create',
            'title' => $createConfig['title'] ?? 'OAuth 프로바이더 추가',
            'subtitle' => $createConfig['subtitle'] ?? '새로운 소셜 로그인 프로바이더 추가',
        ];
    }

    /**
     * OAuth 프로바이더 생성 폼 표시
     */
    public function __invoke()
    {
        return view($this->config['view']);
    }
}